<?php
declare(strict_types=1);

namespace Wisebits\Listener;

use Psr\Log\LoggerInterface;
use Wisebits\Event\User\UserCreated;
use Wisebits\Event\User\UserDeleted;
use Wisebits\Event\User\UserUpdated;

class UserChangedListener
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function onUserCreated(UserCreated $event): void
    {
        $this->logger->info('User created', ['user' => $event->user]);
    }

    public function onUserUpdated(UserUpdated $event): void
    {
        $this->logger->info('User updated', ['user' => $event->user]);
    }

    public function onUserDeleted(UserDeleted $event): void
    {
        $this->logger->info('User deleted', ['user' => $event->user]);
    }
}
