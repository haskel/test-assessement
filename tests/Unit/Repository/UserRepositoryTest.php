<?php

declare(strict_types=1);

namespace Tests\Wisebits\Unit\Repository;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Wisebits\Event\User\UserCreated;
use Wisebits\Event\User\UserDeleted;
use Wisebits\Event\User\UserUpdated;
use Wisebits\Exception\NotFoundException;
use Wisebits\Model\User;
use Wisebits\Repository\UserRepository;

class UserRepositoryTest extends TestCase
{
    private Connection $connection;
    private EventDispatcherInterface $eventDispatcher;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->userRepository = new UserRepository($this->connection, $this->eventDispatcher);
    }

    #[Test]
    public function getByIdReturnsUser(): void
    {
        $this->connection->method('fetchAssociative')
            ->willReturn([
                'id' => 1,
                'name' => 'username',
                'email' => 'user@wisebits.com',
                'notes' => 'Some notes',
                'created' => '2024-01-01 00:00:00',
                'deleted' => null,
            ]);

        $user = $this->userRepository->getById(1);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(1, $user->getId());
        $this->assertSame('username', $user->getName());
    }

    #[Test]
    public function getByIdReturnsNullForNonExistentUser(): void
    {
        $this->connection->method('fetchAssociative')
            ->willReturn(false);

        $user = $this->userRepository->getById(1);

        $this->assertNull($user);
    }

    #[Test]
    public function requireByIdThrowsNotFoundExceptionForNonExistentUser(): void
    {
        $this->connection->method('fetchAssociative')
            ->willReturn(false);

        $this->expectException(NotFoundException::class);

        $this->userRepository->requireById(1);
    }

    #[Test]
    public function createUserDispatchesEvent(): void
    {
        $user = new User(1, 'username', 'user@wisebits.com', 'Some notes', new DateTimeImmutable());

        $this->connection->expects($this->once())
            ->method('insert')
            ->with('users', $this->arrayHasKey('name'));

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserCreated::class));

        $this->userRepository->create($user);
    }

    #[Test]
    public function updateUserDispatchesEvent(): void
    {
        $user = new User(1, 'username', 'user@wisebits.com', 'Some notes', new DateTimeImmutable());

        $this->connection->method('fetchAssociative')
            ->willReturn(['id' => 1, 'deleted' => null]);

        $this->connection->expects($this->once())
            ->method('update')
            ->with('users', $this->arrayHasKey('name'));

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserUpdated::class));

        $this->userRepository->update($user);
    }

    #[Test]
    public function updateUserThrowsNotFoundExceptionForDeletedUser(): void
    {
        $user = new User(1, 'username', 'user@wisebits.com', 'Some notes', new DateTimeImmutable());

        $this->connection->method('fetchAssociative')
            ->willReturn(['id' => 1, 'deleted' => '2024-01-01 00:00:00']);

        $this->expectException(NotFoundException::class);

        $this->userRepository->update($user);
    }

    #[Test]
    public function softDeleteUserDispatchesEvent(): void
    {
        $user = new User(1, 'username', 'user@wisebits.com', 'Some notes', new DateTimeImmutable());

        $this->connection->method('fetchAssociative')
            ->willReturn(['id' => 1, 'deleted' => null]);

        $this->connection->expects($this->once())
            ->method('update')
            ->with('users', $this->arrayHasKey('deleted'));

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UserDeleted::class));

        $this->userRepository->softDelete($user);
    }

    #[Test]
    public function softDeleteUserDoesNotDispatchEventForAlreadyDeletedUser(): void
    {
        $user = new User(1, 'username', 'user@wisebits.com', 'Some notes', new DateTimeImmutable());

        $this->connection->method('fetchAssociative')
            ->willReturn(['id' => 1, 'deleted' => '2024-01-01 00:00:00']);

        $this->connection->expects($this->never())
            ->method('update');

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->userRepository->softDelete($user);
    }

    #[Test]
    public function hasUserByNameReturnsTrueIfUserExists(): void
    {
        $this->connection->method('fetchAssociative')
            ->willReturn(['name' => 'username']);

        $result = $this->userRepository->hasUserByName('username');

        $this->assertTrue($result);
    }

    #[Test]
    public function hasUserByNameReturnsFalseIfUserDoesNotExist(): void
    {
        $this->connection->method('fetchAssociative')
            ->willReturn(false);

        $result = $this->userRepository->hasUserByName('username');

        $this->assertFalse($result);
    }

    #[Test]
    public function hasUserByEmailReturnsTrueIfUserExists(): void
    {
        $this->connection->method('fetchAssociative')
            ->willReturn(['email' => 'user@wisebits.com']);

        $result = $this->userRepository->hasUserByEmail('user@wisebits.com');

        $this->assertTrue($result);
    }

    #[Test]
    public function hasUserByEmailReturnsFalseIfUserDoesNotExist(): void
    {
        $this->connection->method('fetchAssociative')
            ->willReturn(false);

        $result = $this->userRepository->hasUserByEmail('user@wisebits.com');

        $this->assertFalse($result);
    }
}
