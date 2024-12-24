<?php
declare(strict_types=1);

namespace Wisebits\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Attribute;

#[Attribute]
class UserNameRestrictedWords extends Constraint
{
    public string $message = 'This username contains restricted words.';
}
