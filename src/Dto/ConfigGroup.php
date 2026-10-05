<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Dto;

class ConfigGroup
{
    public function __construct(
        protected string $name,
        protected int $count,
        protected bool $active,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
