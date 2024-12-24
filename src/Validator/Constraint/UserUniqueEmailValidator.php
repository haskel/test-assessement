<?php
declare(strict_types=1);

namespace Wisebits\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Wisebits\Repository\UserRepository;

class UserUniqueEmailValidator extends ConstraintValidator
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UserUniqueEmail) {
            throw new UnexpectedTypeException($constraint, UserUniqueEmail::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->userRepository->hasUserByEmail($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
