<?php
declare(strict_types=1);

namespace Wisebits\Model;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Wisebits\Constant\Configuration;
use Wisebits\Exception\LogicException;
use Symfony\Component\Validator\Constraints as Assert;
use Wisebits\Validator\Constraint as AppAssert;

class User implements SoftDeletableInterface
{
    private ?int $id;

    #[
        Assert\Length(min: 8, max: 64),
        AppAssert\UserUniqueName,
        AppAssert\UserNameSymbols,
        AppAssert\UserNameRestrictedWords,
    ]
    private ?string $name;

    #[
        Assert\NotBlank,
        Assert\Length(max: 256),
        Assert\Email,
        AppAssert\UserUniqueEmail,
        AppAssert\EmailAllowedDomainName,
    ]
    private ?string $email;

    private ?DateTimeInterface $created;

    private ?DateTimeInterface $deleted;

    private ?string $notes;

    public function __construct(
        ?int $id = null,
        ?string $name = null,
        ?string $email = null,
        ?string $notes = null,
        ?DateTimeInterface $created = null,
        ?DateTimeInterface $deleted = null,
    ) {
        $nowDateTime = new DateTimeImmutable('now', new DateTimeZone(Configuration::DEFAULT_TIMEZONE));

        $this->id = $id;
        $this->name = trim($name ?? ''); // don't forget to strtolower if UserNameSymbols constraint will become case-insensitive
        $this->email = strtolower(trim($email ?? ''));
        $this->notes = $notes;
        $this->created = $created ?? $nowDateTime;
        $this->deleted = $deleted;

        if ($this->deleted !== null && $this->created > $this->deleted) {
            throw new LogicException('User is deleted before created');
        }

        if ($this->deleted !== null && $this->deleted > $nowDateTime) {
            throw new LogicException('User is deleted in the future');
        }

        if ($this->created > $nowDateTime) {
            throw new LogicException('User is created in the future');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name); // don't forget to strtolower if UserNameSymbols constraint will become case-insensitive

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower(trim($email));

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function deleteNotes(): void
    {
        $this->notes = null;
    }

    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }

    public function getDeleted(): ?DateTimeInterface
    {
        return $this->deleted;
    }

    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }

    public function setDeleted(?DateTimeInterface $deleted): self
    {
        if ($deleted !== null && $this->deleted !== null) {
            throw new LogicException('User is already deleted');
        }

        if ($deleted !== null && $this->created > $deleted) {
            throw new LogicException('User is deleted before created');
        }

        $this->deleted = $deleted;

        return $this;
    }
}
