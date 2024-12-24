<?php
declare(strict_types=1);

namespace Wisebits\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Wisebits\Service\UsernameRestrictedWordsRegistry;

class UserNameRestrictedWordsValidator extends ConstraintValidator
{
    public function __construct(
        private UsernameRestrictedWordsRegistry $usernameRestrictedWordsRegistry,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UserNameRestrictedWords) {
            throw new UnexpectedTypeException($constraint, UserNameRestrictedWords::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->usernameRestrictedWordsRegistry->containsRestricted($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
