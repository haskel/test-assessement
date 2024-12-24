<?php
declare(strict_types=1);

namespace Wisebits\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Wisebits\Repository\UserRepository;

class UserUniqueNameValidator extends ConstraintValidator
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UserUniqueName) {
            throw new UnexpectedTypeException($constraint, UserUniqueName::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->userRepository->hasUserByName($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }

}
