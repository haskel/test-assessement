<?php
declare(strict_types=1);

namespace Wisebits\Service;

class UsernameRestrictedWordsRegistry
{
    private string $pattern;

    /**
     * @param array<string> $words
     */
    public function __construct(
        private array $words = []
    ) {
        $this->pattern = '/' . implode('|', array_map('preg_quote', $words)) . '/i';
    }

    public function containsRestricted(string $username): bool
    {
        if (empty($this->words)) {
            return false;
        }

        $username = strtolower(trim($username));
        if (preg_match($this->pattern, $username)) {
            return true;
        }

        return false;
    }
}
