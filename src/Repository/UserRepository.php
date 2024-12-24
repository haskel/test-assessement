<?php
declare(strict_types=1);

namespace Wisebits\Repository;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Connection;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Wisebits\Constant\Configuration;
use Wisebits\Event\User\UserCreated;
use Wisebits\Event\User\UserDeleted;
use Wisebits\Event\User\UserUpdated;
use Wisebits\Exception\InvalidArgumentException;
use Wisebits\Exception\NotFoundException;
use Wisebits\Model\User;

class UserRepository
{
    public function __construct(
        private Connection $connection,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getById(int $id): ?User
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('User ID must be greater than 0');
        }

        $data = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE id = :id AND deleted IS NULL',
            ['id' => $id]
        );
        if ($data === false) {
            return null;
        }

        $timeZone = new DateTimeZone(Configuration::DEFAULT_TIMEZONE);

        return new User(
            id: (int) $data['id'],
            name: $data['name'],
            email: $data['email'],
            notes: $data['notes'],
            created: new DateTimeImmutable($data['created'], $timeZone),
            deleted: $data['deleted'] ? new DateTimeImmutable($data['deleted'], $timeZone) : null,
        );
    }

    public function requireById(int $id): User
    {
        $user = $this->getById($id);
        if ($user === null) {
            throw new NotFoundException(sprintf('User not found [id=%d]', $id));
        }

        return $user;
    }

    public function create(User $user): void
    {
        $this->connection->insert(
            'users',
            [
                'name' => $user->getName(),
                'email' => strtolower($user->getEmail()),
                'notes' => $user->getNotes(),
                'created' => $user->getCreated()->format(Configuration::DB_DATETIME_FORMAT),
            ]
        );

        $this->eventDispatcher->dispatch(new UserCreated($user));
    }

    public function update(User $user): void
    {
        if ($user->getId() <= 0) {
            throw new InvalidArgumentException('User ID must be greater than 0');
        }

        $this->connection->beginTransaction();

        try {
            $currentUserRow = $this->connection->fetchAssociative(
                'SELECT id, deleted FROM users WHERE id = :id FOR UPDATE',
                ['id' => $user->getId()]
            );

            // do not update deleted user
            if ($currentUserRow === false || $currentUserRow['deleted'] !== null) {
                throw new NotFoundException(sprintf('User not found [id=%d]', $user->getId()));
            }

            $this->connection->update(
                'users',
                [
                    'name' => $user->getName(),
                    'email' => strtolower($user->getEmail()),
                    'notes' => $user->getNotes(),
                ],
                [
                    'id' => $user->getId()
                ]
            );

            $this->connection->commit();

        } catch (Throwable $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UserUpdated($user));
    }

    public function softDelete(User $user): void
    {
        if ($user->getId() <= 0) {
            throw new InvalidArgumentException('User ID must be greater than 0');
        }

        $deletedDateTime = (new DateTimeImmutable('now', new DateTimeZone(Configuration::DEFAULT_TIMEZONE)));

        $this->connection->beginTransaction();

        try {
            $currentUserRow = $this->connection->fetchAssociative(
                'SELECT id, deleted FROM users WHERE id = :id FOR UPDATE',
                ['id' => $user->getId()]
            );

            // do not delete already deleted user
            if ($currentUserRow === false || $currentUserRow['deleted'] !== null) {
                if ($this->connection->isTransactionActive()) {
                    $this->connection->rollBack();
                }

                return;
            }

            $this->connection->update(
                'users',
                [
                    'deleted' => $deletedDateTime->format(Configuration::DB_DATETIME_FORMAT),
                ],
                [
                    'id' => $user->getId()
                ]
            );

            $this->connection->commit();

        } catch (Throwable $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UserDeleted($user));
    }

    public function hasUserByName(string $value): bool
    {
        if (trim($value) === '') {
            return false;
        }

        $data = $this->connection->fetchAssociative(
            'SELECT name FROM users WHERE name = :name',
            ['name' => strtolower($value)]
        );

        return $data !== false;
    }

    public function hasUserByEmail(string $value): bool
    {
        if (trim($value) === '') {
            return false;
        }

        $data = $this->connection->fetchAssociative(
            'SELECT email FROM users WHERE email = :email',
            ['email' => strtolower($value)]
        );

        return $data !== false;
    }
}
