<?php
declare(strict_types=1);

namespace Wisebits\Validator\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class UserUniqueName extends Constraint
{
    public string $message = 'The name "{{ value }}" is already in use.';
}
