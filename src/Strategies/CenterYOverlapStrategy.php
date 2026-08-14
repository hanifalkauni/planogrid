<?php

namespace Alkauni\Planogrid\Strategies;

use Alkauni\Planogrid\Contracts\RowSortingStrategyInterface;
use Alkauni\Planogrid\DTO\CustomLabelDetection;

/**
 * Strategy 2: Center-Y Overlap Strategy
 * Groups items into rows if an item's Center-Y falls within the vertical span [Top, Bottom] of an existing row.
 */
class CenterYOverlapStrategy implements RowSortingStrategyInterface
{
    public function __construct(
        private readonly float $minOverlapRatio = 0.50
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

        $rowSpans = []; // Structure: ['top' => float, 'bottom' => float, 'items' => array]

        foreach ($items as $item) {
            $itemCenterY = $item->box->centerY();
            $itemTop = $item->box->top;
            $itemBottom = $item->box->bottom();

            $matchedRowIndex = null;

            foreach ($rowSpans as $index => $row) {
                // Check if item's center Y falls within row vertical bounds or has sufficient vertical overlap
                $overlapTop = max($row['top'], $itemTop);
                $overlapBottom = min($row['bottom'], $itemBottom);
                $overlapHeight = max(0.0, $overlapBottom - $overlapTop);
                $overlapRatio = $item->box->height > 0 ? $overlapHeight / $item->box->height : 0;

                if (($itemCenterY >= $row['top'] && $itemCenterY <= $row['bottom']) || $overlapRatio >= $this->minOverlapRatio) {
                    $matchedRowIndex = $index;
                    break;
                }
            }

            if ($matchedRowIndex !== null) {
                $rowSpans[$matchedRowIndex]['items'][] = $item;
                // Dynamically adjust row bounds
                $rowSpans[$matchedRowIndex]['top'] = min($rowSpans[$matchedRowIndex]['top'], $itemTop);
                $rowSpans[$matchedRowIndex]['bottom'] = max($rowSpans[$matchedRowIndex]['bottom'], $itemBottom);
            } else {
                $rowSpans[] = [
                    'top' => $itemTop,
                    'bottom' => $itemBottom,
                    'items' => [$item],
                ];
            }
        }

        // Sort rows by their average top coordinate to ensure top-to-bottom order
        usort($rowSpans, fn($a, $b) => $a['top'] <=> $b['top']);

        return array_map(fn($row) => $row['items'], $rowSpans);
    }
}
