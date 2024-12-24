<?php

namespace Tests\Wisebits\Unit\Model;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Wisebits\Constant\Configuration;
use Wisebits\Exception\LogicException;
use Wisebits\Model\User;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
    private int $userId = 1;
    private string $userName = 'username123';
    private string $userEmail = 'user@wisebits.com';
    private string $userNotes = 'Some notes';

    #[Test]
    public function userCanBeCreated(): void
    {
        $user = new User($this->userId, $this->userName, $this->userEmail);

        $this->assertSame($this->userId, $user->getId());
        $this->assertSame($this->userName, $user->getName());
        $this->assertSame($this->userEmail, $user->getEmail());
        $this->assertNull($user->getNotes());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreated());
        $this->assertNull($user->getDeleted());
    }

    #[Test]
    public function userNameCanBeUpdated(): void
    {
        $newName = 'newname';
        $user = new User($this->userId, $this->userName, $this->userEmail);
        $user->setName($newName);

        $this->assertSame($newName, $user->getName());
    }

    #[Test]
    public function userEmailCanBeUpdated(): void
    {
        $newEmail = 'newuser@wisebits.com';
        $user = new User($this->userId, $this->userName, $this->userEmail);
        $user->setEmail($newEmail);

        $this->assertSame($newEmail, $user->getEmail());
    }

    #[Test]
    public function userNotesCanBeSetAndDeleted(): void
    {
        $user = new User($this->userId, $this->userName, $this->userEmail);
        $user->setNotes($this->userNotes);

        $this->assertSame($this->userNotes, $user->getNotes());

        $user->deleteNotes();

        $this->assertNull($user->getNotes());
    }

    #[Test]
    public function userCanBeMarkedAsDeleted(): void
    {
        $user = new User($this->userId, $this->userName, $this->userEmail);
        $deletedTime = new DateTimeImmutable('now', new DateTimeZone(Configuration::DEFAULT_TIMEZONE));
        $user->setDeleted($deletedTime);

        $this->assertTrue($user->isDeleted());
        $this->assertSame($deletedTime, $user->getDeleted());
    }

    #[Test]
    public function cannotDeleteAlreadyDeletedUser(): void
    {
        $this->expectException(LogicException::class);

        $user = new User($this->userId, $this->userName, $this->userEmail);
        $deletedTime = new DateTimeImmutable('now', new DateTimeZone(Configuration::DEFAULT_TIMEZONE));
        $user->setDeleted($deletedTime);

        $user->setDeleted(new DateTimeImmutable('now', new DateTimeZone(Configuration::DEFAULT_TIMEZONE)));
    }

    #[Test]
    public function userNameIsTrimmedInConstructor(): void
    {
        $rawName = '  username123  ';
        $user = new User($this->userId, $rawName, $this->userEmail);
        $this->assertSame('username123', $user->getName());
    }

    #[Test]
    public function userEmailIsTrimmedInConstructor(): void
    {
        $rawEmail = '  user@wisebits.com  ';
        $user = new User($this->userId, $this->userName, $rawEmail);
        $this->assertSame('user@wisebits.com', $user->getEmail());
    }

    #[Test]
    public function userEmailIsLowercasedInConstructor(): void
    {
        $rawEmail = 'USER@WISEBITS.COM';
        $user = new User($this->userId, $this->userName, $rawEmail);
        $this->assertSame('user@wisebits.com', $user->getEmail());
    }

    #[Test]
    public function userNameIsTrimmedInSetter(): void
    {
        $rawName = '  newname  ';
        $user = new User($this->userId, $this->userName, $this->userEmail);
        $user->setName($rawName);
        $this->assertSame('newname', $user->getName());
    }

    #[Test]
    public function userEmailIsTrimmedInSetter(): void
    {
        $rawEmail = '  newuser@wisebits.com  ';
        $user = new User($this->userId, $this->userName, $this->userEmail);
        $user->setEmail($rawEmail);
        $this->assertSame('newuser@wisebits.com', $user->getEmail());
    }

    #[Test]
    public function userEmailIsLowercasedInSetter(): void
    {
        $rawEmail = 'NEWUSER@WISEBITS.COM';
        $user = new User($this->userId, $this->userName, $this->userEmail);
        $user->setEmail($rawEmail);
        $this->assertSame('newuser@wisebits.com', $user->getEmail());
    }

    #[Test]
    public function cannotCreateUserWithDeletedBeforeCreated(): void
    {
        $this->expectException(LogicException::class);

        $createdTime = new DateTimeImmutable('now', new DateTimeZone(Configuration::DEFAULT_TIMEZONE));
        $deletedTime = $createdTime->modify('-1 day');

        new User($this->userId, $this->userName, $this->userEmail, null, $createdTime, $deletedTime);
    }

    #[Test]
    public function cannotCreateUserWithDeletedInFuture(): void
    {
        $this->expectException(LogicException::class);

        $createdTime = new DateTimeImmutable('now', new DateTimeZone(Configuration::DEFAULT_TIMEZONE));
        $deletedTime = $createdTime->modify('+1 day');

        new User($this->userId, $this->userName, $this->userEmail, null, $createdTime, $deletedTime);
    }

    #[Test]
    public function cannotCreateUserWithCreatedInFuture(): void
    {
        $this->expectException(LogicException::class);

        $createdTime = (new DateTimeImmutable('now', new DateTimeZone(Configuration::DEFAULT_TIMEZONE)))->modify('+1 day');

        new User($this->userId, $this->userName, $this->userEmail, null, $createdTime);
    }

    #[Test]
    public function cannotSetDeletedDateBeforeCreatedDate(): void
    {
        $this->expectException(LogicException::class);

        $createdTime = new DateTimeImmutable('now', new DateTimeZone(Configuration::DEFAULT_TIMEZONE));
        $user = new User($this->userId, $this->userName, $this->userEmail, null, $createdTime);

        $pastDeletedTime = $createdTime->modify('-1 day');
        $user->setDeleted($pastDeletedTime);
    }
}
