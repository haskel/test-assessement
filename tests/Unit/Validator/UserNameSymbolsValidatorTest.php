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
use Wisebits\Validator\Constraint\UserNameSymbols;
use Wisebits\Validator\Constraint\UserNameSymbolsValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

#[CoversClass(UserNameSymbolsValidator::class)]
class UserNameSymbolsValidatorTest extends TestCase
{
    private ExecutionContextInterface $context;
    private UserNameSymbolsValidator $validator;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new UserNameSymbolsValidator();
        $this->validator->initialize($this->context);
    }

    #[Test]
    public function validateThrowsUnexpectedTypeExceptionForInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('username123', $this->createMock(Constraint::class));
    }

    #[Test]
    public function validateThrowsUnexpectedValueExceptionForNonStringValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $constraint = new UserNameSymbols();
        $this->validator->validate(123, $constraint);
    }

    #[Test]
    #[DataProvider('userNameDataProvider')]
    public function validateChecksUserNameSymbols(string $username, bool $shouldAddViolation): void
    {
        $constraint = new UserNameSymbols(['message' => 'The username contains invalid characters.']);

        if ($shouldAddViolation) {
            $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
            $violationBuilder->expects($this->once())
                ->method('setParameter')
                ->with('{{ value }}', $username)
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

        $this->validator->validate($username, $constraint);
    }

    public static function userNameDataProvider(): array
    {
        return [
            'valid username' => ['username123', false],
            'invalid username with special characters' => ['user@name!', true],
            'invalid username with uppercase letters' => ['UserName', true],
            'empty username' => ['', false],
            'whitespace username' => [' ', true],
            'with whitespace username' => [' username', true],
        ];
    }
}
