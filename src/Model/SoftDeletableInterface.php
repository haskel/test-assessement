<?php
declare(strict_types=1);

namespace Wisebits\Model;

use DateTimeInterface;

interface SoftDeletableInterface
{
    public function getDeleted(): ?DateTimeInterface;
    public function setDeleted(?DateTimeInterface $deleted): self;
    public function isDeleted(): bool;
}
