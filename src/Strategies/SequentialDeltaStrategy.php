<?php

namespace Alkauni\Planogrid\Strategies;

use Alkauni\Planogrid\Contracts\RowSortingStrategyInterface;
use Alkauni\Planogrid\DTO\CustomLabelDetection;

/**
 * Strategy 0: Sequential Delta Strategy (Default Implementation)
 * Compares the vertical delta top position of item N vs item N-1 against minHeight threshold.
 */
class SequentialDeltaStrategy implements RowSortingStrategyInterface
{
    /**
     * @param array<int, CustomLabelDetection> $items
     * @return array<int, array<int, CustomLabelDetection>>
     */
    public function sortIntoRows(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        // Calculate minHeight across all detections
        $heights = array_map(fn(CustomLabelDetection $item) => $item->box->height, $items);
        $minHeight = !empty($heights) ? min($heights) : 0.0;

        $lastTop = 0.0;
        $indexRow = 0;
        $rows = [];

        foreach ($items as $item) {
            $currentTop = $item->box->top;
            $diffRangeValue = abs($lastTop - $currentTop);
            $lastTop = $currentTop;

            if (empty($rows)) {
                $rows[0][] = $item;
            } elseif ($diffRangeValue >= $minHeight) {
                $indexRow++;
                $rows[$indexRow][] = $item;
            } else {
                $rows[$indexRow][] = $item;
            }
        }

        return array_values($rows);
    }
}
