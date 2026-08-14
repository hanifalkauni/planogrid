<?php

namespace Alkauni\Planogrid\Strategies;

use Alkauni\Planogrid\Contracts\RowSortingStrategyInterface;
use Alkauni\Planogrid\DTO\CustomLabelDetection;

/**
 * Strategy 1: Baseline Anchor Strategy
 * Compares current item's top coordinate against the first item's top anchor in the active row.
 * Prevents chained drift over long shelf rows.
 */
class BaselineAnchorStrategy implements RowSortingStrategyInterface
{
    public function __construct(
        private readonly float $thresholdMultiplier = 0.5
    ) {}

    /**
     * @param array<int, CustomLabelDetection> $items
     * @return array<int, array<int, CustomLabelDetection>>
     */
    public function sortIntoRows(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        // Compute median height for threshold scale
        $heights = array_map(fn(CustomLabelDetection $item) => $item->box->height, $items);
        sort($heights);
        $count = count($heights);
        $medianHeight = $count % 2 === 0
            ? ($heights[$count / 2 - 1] + $heights[$count / 2]) / 2.0
            : $heights[(int) floor($count / 2)];

        $threshold = $medianHeight * $this->thresholdMultiplier;

        $rows = [];
        $currentRowIndex = 0;
        $rowAnchorTop = null;

        foreach ($items as $item) {
            $currentTop = $item->box->top;

            if ($rowAnchorTop === null) {
                $rowAnchorTop = $currentTop;
                $rows[$currentRowIndex][] = $item;
                continue;
            }

            if (abs($currentTop - $rowAnchorTop) >= $threshold) {
                $currentRowIndex++;
                $rowAnchorTop = $currentTop;
                $rows[$currentRowIndex][] = $item;
            } else {
                $rows[$currentRowIndex][] = $item;
            }
        }

        return array_values($rows);
    }
}
