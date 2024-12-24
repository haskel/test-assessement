<?php
declare(strict_types=1);

namespace Wisebits\Validator\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EmailAllowedDomainName extends Constraint
{
    public string $message = 'The domain "{{ domain }}" is not allowed.';
}
