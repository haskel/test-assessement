<?php
declare(strict_types=1);

namespace Wisebits\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Wisebits\Exception\InvalidArgumentException;
use Wisebits\Exception\ValidationException;
use Wisebits\Model\User;
use Wisebits\Repository\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param array<string, string> $data
     */
    public function create(array $data): User
    {
        $user = new User(
            name: $data['name'],
            email: $data['email'],
            notes: $data['notes'],
        );

        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        $this->userRepository->create($user);

        return $user;
    }

    /**
     * @param array<string, string> $data
     */
    public function update(int $id, array $data): User
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('User ID is invalid');
        }

        $user = $this->userRepository->requireById($id);

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['notes'])) {
            $user->setNotes($data['notes']);
        }

        $errors = $this->validator->validate($user);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        $this->userRepository->update($user);

        return $user;
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid user ID');
        }

        $user = $this->userRepository->requireById($id);
        $this->userRepository->softDelete($user);
    }

    public function getById(int $id): ?User
    {
        if ($id < 1) {
            throw new InvalidArgumentException('Invalid user ID');
        }

        return $this->userRepository->getById($id);
    }
}
