<?php
declare(strict_types=1);

namespace Tests\Wisebits\Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Wisebits\Exception\InvalidArgumentException;
use Wisebits\Exception\ValidationException;
use Wisebits\Model\User;
use Wisebits\Repository\UserRepository;
use Wisebits\Service\UserService;

#[CoversClass(UserService::class)]
class UserServiceTest extends TestCase
{
    private UserRepository $userRepository;
    private ValidatorInterface $validator;
    private UserService $userService;

    private const VALID_USER_ID = 1;
    private const INVALID_USER_ID = 0;
    private const USER_NAME = 'username123';
    private const USER_EMAIL = 'user@wisebits.com';
    private const USER_NOTES = 'Some notes';
    private const NEW_USER_NAME = 'newname';
    private const NEW_USER_EMAIL = 'newuser@wisebits.com';
    private const UPDATED_NOTES = 'Updated notes';
    private const VALIDATION_ERROR_MESSAGE = 'Some violation';

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->userService = new UserService($this->userRepository, $this->validator);
    }

    #[Test]
    public function createUserSuccessfully(): void
    {
        $data = ['name' => self::USER_NAME, 'email' => self::USER_EMAIL, 'notes' => self::USER_NOTES];

        $this->validator->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepository->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(User::class));

        $user = $this->userService->create($data);

        $this->assertSame(self::USER_NAME, $user->getName());
        $this->assertSame(self::USER_EMAIL, $user->getEmail());
        $this->assertSame(self::USER_NOTES, $user->getNotes());
    }

    #[Test]
    public function createUserThrowsValidationException(): void
    {
        $data = ['name' => self::USER_NAME, 'email' => self::USER_EMAIL, 'notes' => self::USER_NOTES];

        $this->validator->method('validate')
            ->willReturn(new ConstraintViolationList([new ConstraintViolation(self::VALIDATION_ERROR_MESSAGE, null, [], null, null, null)]));

        $this->expectException(ValidationException::class);

        $this->userService->create($data);
    }

    #[Test]
    public function updateUserSuccessfully(): void
    {
        $data = ['name' => self::NEW_USER_NAME, 'email' => self::NEW_USER_EMAIL, 'notes' => self::UPDATED_NOTES];
        $user = new User(self::VALID_USER_ID, self::USER_NAME, self::USER_EMAIL);

        $this->userRepository->method('requireById')
            ->willReturn($user);

        $this->validator->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->userRepository->expects($this->once())
            ->method('update')
            ->with($user);

        $updatedUser = $this->userService->update(self::VALID_USER_ID, $data);

        $this->assertSame(self::NEW_USER_NAME, $updatedUser->getName());
        $this->assertSame(self::NEW_USER_EMAIL, $updatedUser->getEmail());
        $this->assertSame(self::UPDATED_NOTES, $updatedUser->getNotes());
    }

    #[Test]
    public function updateUserThrowsValidationException(): void
    {
        $data = ['name' => self::NEW_USER_NAME, 'email' => self::NEW_USER_EMAIL, 'notes' => self::UPDATED_NOTES];
        $user = new User(self::VALID_USER_ID, self::USER_NAME, self::USER_EMAIL);

        $this->userRepository->method('requireById')
            ->willReturn($user);

        $this->validator->method('validate')
            ->willReturn(new ConstraintViolationList([new ConstraintViolation(self::VALIDATION_ERROR_MESSAGE, null, [], null, null, null)]));

        $this->expectException(ValidationException::class);

        $this->userService->update(self::VALID_USER_ID, $data);
    }

    #[Test]
    public function updateUserThrowsInvalidArgumentExceptionForInvalidId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->userService->update(self::INVALID_USER_ID, []);
    }

    #[Test]
    public function deleteUserSuccessfully(): void
    {
        $user = new User(self::VALID_USER_ID, self::USER_NAME, self::USER_EMAIL);

        $this->userRepository->method('requireById')
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('softDelete')
            ->with($user);

        $this->userService->delete(self::VALID_USER_ID);
    }

    #[Test]
    public function deleteUserThrowsInvalidArgumentExceptionForInvalidId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->userService->delete(self::INVALID_USER_ID);
    }

    #[Test]
    public function getByIdReturnsUser(): void
    {
        $user = new User(self::VALID_USER_ID, self::USER_NAME, self::USER_EMAIL);

        $this->userRepository->method('getById')
            ->willReturn($user);

        $result = $this->userService->getById(self::VALID_USER_ID);

        $this->assertSame($user, $result);
    }

    #[Test]
    public function getByIdReturnsNullForNonExistentUser(): void
    {
        $this->userRepository->method('getById')
            ->willReturn(null);

        $result = $this->userService->getById(self::VALID_USER_ID);

        $this->assertNull($result);
    }
}
