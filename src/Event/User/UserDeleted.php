<?php
declare(strict_types=1);

namespace Wisebits\Event\User;

use Symfony\Contracts\EventDispatcher\Event;
use Wisebits\Model\User;

class UserDeleted extends Event
{
    public function __construct(
        public readonly User $user,
    ) {
    }
}
