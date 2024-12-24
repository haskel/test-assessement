<?php
declare(strict_types=1);

namespace Tests\Wisebits\Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Wisebits\Service\ForbiddenEmailDomainsRegistry;

#[CoversClass(ForbiddenEmailDomainsRegistry::class)]
class ForbiddenEmailDomainsRegistryTest extends TestCase
{
    #[Test]
    #[DataProvider('domainDataProvider')]
    public function isForbiddenReturnsExpectedResult(array $domains, string $domainToCheck, bool $expectedResult): void
    {
        $registry = new ForbiddenEmailDomainsRegistry($domains);

        $result = $registry->isForbidden($domainToCheck);

        $this->assertSame($expectedResult, $result);
    }

    public static function domainDataProvider(): array
    {
        return [
            'forbidden domain' => [
                'domains' => ['forbidden.com' => true, 'blocked.com' => true],
                'domainToCheck' => 'forbidden.com',
                'expectedResult' => true,
            ],
            'not forbidden domain' => [
                'domains' => ['forbidden.com' => true, 'blocked.com' => true],
                'domainToCheck' => 'allowed.com',
                'expectedResult' => false,
            ],
            'empty domain' => [
                'domains' => ['forbidden.com' => true, 'blocked.com' => true],
                'domainToCheck' => '',
                'expectedResult' => false,
            ],
            'whitespace domain' => [
                'domains' => ['forbidden.com' => true, 'blocked.com' => true],
                'domainToCheck' => ' ',
                'expectedResult' => false,
            ],
            'with whitespace domain allowed' => [
                'domains' => ['forbidden.com' => true, 'blocked.com' => true],
                'domainToCheck' => ' allowed.com',
                'expectedResult' => false,
            ],
            'with whitespace domain forbidden' => [
                'domains' => ['forbidden.com' => true, 'blocked.com' => true],
                'domainToCheck' => ' forbidden.com',
                'expectedResult' => true,
            ],
            'empty domain list' => [
                'domains' => [],
                'domainToCheck' => 'anydomain.com',
                'expectedResult' => false,
            ],
        ];
    }
}
