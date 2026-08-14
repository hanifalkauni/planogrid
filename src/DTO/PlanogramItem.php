<?php

namespace Alkauni\Planogrid\DTO;

class PlanogramItem
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $id = null,
        public readonly ?string $sku = null,
        public readonly bool $isCompetitor = false
    ) {}

    public static function fromString(string $name): self
    {
        return new self(name: $name);
    }
}
