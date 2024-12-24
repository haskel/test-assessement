<?php
declare(strict_types=1);

namespace Wisebits\Validator\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class UserUniqueEmail extends Constraint
{
    public string $message = 'This email is already in use.';
}
