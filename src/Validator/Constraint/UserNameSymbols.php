<?php
declare(strict_types=1);

namespace Wisebits\Validator\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UserNameSymbols extends Constraint
{
    public string $message = 'The username "{{ value }}" contains forbidden symbols.';
}
