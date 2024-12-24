<?php
declare(strict_types=1);

namespace Wisebits\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Wisebits\Service\ForbiddenEmailDomainsRegistry;

class EmailAllowedDomainNameValidator extends ConstraintValidator
{
    public function __construct(
        private ForbiddenEmailDomainsRegistry $forbiddenEmailDomainsRegistry,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EmailAllowedDomainName) {
            throw new UnexpectedTypeException($constraint, EmailAllowedDomainName::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $domain = explode('@', $value)[1] ?? null;
        if (!$domain || trim($domain) === '') {
            return;
        }

        if ($this->forbiddenEmailDomainsRegistry->isForbidden($domain)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ domain }}', $value)
                ->addViolation();
        }
    }
}
