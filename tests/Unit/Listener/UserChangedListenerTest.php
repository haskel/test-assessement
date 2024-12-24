<?php

namespace Tests\Wisebits\Unit\Listener;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Wisebits\Event\User\UserCreated;
use Wisebits\Event\User\UserDeleted;
use Wisebits\Event\User\UserUpdated;
use Wisebits\Listener\UserChangedListener;
use Wisebits\Model\User;

class UserChangedListenerTest extends TestCase
{
    #[Test]
    public function onUserCreatedLogsWithCorrectLevelAndUser(): void
    {
        $user = $this->getUser();
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                $this->anything(), // any message
                $this->callback(function ($context) use ($user) {
                    return isset($context['user']) && $context['user'] === $user;
                })
            );

        $listener = new UserChangedListener($logger);
        $listener->onUserCreated(new UserCreated($user));
    }

    #[Test]
    public function onUserUpdatedLogsWithCorrectLevelAndUser(): void
    {
        $user = $this->getUser();
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                $this->anything(),
                $this->callback(function ($context) use ($user) {
                    return isset($context['user']) && $context['user'] === $user;
                })
            );

        $listener = new UserChangedListener($logger);
        $listener->onUserUpdated(new UserUpdated($user));
    }

    #[Test]
    public function onUserDeletedLogsWithCorrectLevelAndUser(): void
    {
        $user = $this->getUser();
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                $this->anything(),
                $this->callback(function ($context) use ($user) {
                    return isset($context['user']) && $context['user'] === $user;
                })
            );

        $listener = new UserChangedListener($logger);
        $listener->onUserDeleted(new UserDeleted($user));
    }

    private function getUser(): User
    {
        return new User(1, 'name', 'email');
    }
}
