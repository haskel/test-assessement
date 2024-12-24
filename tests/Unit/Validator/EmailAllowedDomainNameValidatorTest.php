<?php
declare(strict_types=1);

namespace Tests\Wisebits\Unit\Validator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Wisebits\Service\ForbiddenEmailDomainsRegistry;
use Wisebits\Validator\Constraint\EmailAllowedDomainName;
use Wisebits\Validator\Constraint\EmailAllowedDomainNameValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

#[CoversClass(EmailAllowedDomainNameValidator::class)]
class EmailAllowedDomainNameValidatorTest extends TestCase
{
    private ForbiddenEmailDomainsRegistry $forbiddenEmailDomainsRegistry;
    private ExecutionContextInterface $context;
    private EmailAllowedDomainNameValidator $validator;

    protected function setUp(): void
    {
        $this->forbiddenEmailDomainsRegistry = $this->createMock(ForbiddenEmailDomainsRegistry::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new EmailAllowedDomainNameValidator($this->forbiddenEmailDomainsRegistry);
        $this->validator->initialize($this->context);
    }

    #[Test]
    public function validateThrowsUnexpectedTypeExceptionForInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('user@wisebits.com', $this->createMock(Constraint::class));
    }

    #[Test]
    public function validateThrowsUnexpectedValueExceptionForNonStringValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $constraint = new EmailAllowedDomainName();
        $this->validator->validate(123, $constraint);
    }

    #[Test]
    #[DataProvider('emailDataProvider')]
    public function validateChecksForbiddenDomains(string $email, bool $isForbidden, bool $shouldAddViolation): void
    {
        $constraint = new EmailAllowedDomainName(['message' => 'The domain is forbidden.']);

        $this->forbiddenEmailDomainsRegistry->method('isForbidden')
            ->willReturn($isForbidden);

        if ($shouldAddViolation) {
            $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
            $violationBuilder->expects($this->once())
                ->method('setParameter')
                ->with('{{ domain }}', $email)
                ->willReturnSelf();
            $violationBuilder->expects($this->once())
                ->method('addViolation');

            $this->context->expects($this->once())
                ->method('buildViolation')
                ->with($constraint->message)
                ->willReturn($violationBuilder);
        } else {
            $this->context->expects($this->never())
                ->method('buildViolation');
        }

        $this->validator->validate($email, $constraint);
    }

    public static function emailDataProvider(): array
    {
        return [
            'allowed domain' => ['user@allowed.com', false, false],
            'forbidden domain' => ['user@forbidden.com', true, true],
            'empty domain' => ['user@', false, false],
        ];
    }
}
