<?php

namespace Alkauni\Planogrid\DTO;

class PlanogramRow
{
    /**
     * @var array<int, PlanogramItem>
     */
    public readonly array $items;

    /**
     * @param array<int, PlanogramItem|string> $items
     */
    public function __construct(array $items = [])
    {
        $parsed = [];
        foreach ($items as $item) {
            if ($item instanceof PlanogramItem) {
                $parsed[] = $item;
            } else if (is_string($item)) {
                $parsed[] = PlanogramItem::fromString($item);
            }
        }
        $this->items = $parsed;
    }

    public function count(): int
    {
        return count($this->items);
    }
}
