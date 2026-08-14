<?php

namespace Alkauni\Planogrid\DTO;

class PlanogramTemplate
{
    /**
     * @var array<int, PlanogramRow>
     */
    public readonly array $rows;

    /**
     * @param array<int, PlanogramRow|array<int, PlanogramItem|string>> $rows
     */
    public function __construct(array $rows = [])
    {
        $parsed = [];
        foreach ($rows as $row) {
            if ($row instanceof PlanogramRow) {
                $parsed[] = $row;
            } else if (is_array($row)) {
                $parsed[] = new PlanogramRow($row);
            }
        }
        $this->rows = $parsed;
    }

    public function rowCount(): int
    {
        return count($this->rows);
    }

    public function totalItemCount(): int
    {
        $total = 0;
        foreach ($this->rows as $row) {
            $total += $row->count();
        }
        return $total;
    }
}
