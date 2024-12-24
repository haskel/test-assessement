<?php
declare(strict_types=1);

namespace Wisebits\Service;

class ForbiddenEmailDomainsRegistry
{
    /**
     * @param array<string, bool> $domains
     */
    public function __construct(
        private array $domains = []
    ) {
    }

    public function isForbidden(string $domain): bool
    {
        if (empty($this->domains)) {
            return false;
        }

        return isset($this->domains[trim($domain)]);
    }
}
