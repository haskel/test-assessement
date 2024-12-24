<?php
declare(strict_types=1);

namespace Wisebits\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends BaseException
{
    public function __construct(
        public readonly ConstraintViolationListInterface $violations,
    ) {
        parent::__construct('Validation failed');
    }
}
