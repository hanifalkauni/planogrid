<?php

namespace Alkauni\Planogrid\Strategies;

use Alkauni\Planogrid\Contracts\RowSortingStrategyInterface;
use Alkauni\Planogrid\DTO\BoundingBox;
use Alkauni\Planogrid\DTO\CustomLabelDetection;

/**
 * Strategy 4: Vertical IoU Strategy (1D Vertical Intersection over Union)
 * Uses standard computer vision 1D IoU metric to evaluate vertical overlap between product bounding boxes.
 */
class VerticalIoUStrategy implements RowSortingStrategyInterface
{
    public function __construct(
        private readonly float $minIoUThreshold = 0.40
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

        $rows = []; // Structure: ['top' => float, 'bottom' => float, 'items' => array]

        foreach ($items as $item) {
            $itemBox = $item->box;

            $bestRowIndex = null;
            $bestIoU = 0.0;

            foreach ($rows as $index => $row) {
                // Create synthetic bounding box for the row vertical span
                $rowBox = new BoundingBox(
                    left: 0.0,
                    top: $row['top'],
                    width: 1.0,
                    height: max(0.001, $row['bottom'] - $row['top'])
                );

                $iou = $itemBox->verticalIoU($rowBox);

                if ($iou >= $this->minIoUThreshold && $iou > $bestIoU) {
                    $bestIoU = $iou;
                    $bestRowIndex = $index;
                }
            }

            if ($bestRowIndex !== null) {
                $rows[$bestRowIndex]['items'][] = $item;
                $rows[$bestRowIndex]['top'] = min($rows[$bestRowIndex]['top'], $itemBox->top);
                $rows[$bestRowIndex]['bottom'] = max($rows[$bestRowIndex]['bottom'], $itemBox->bottom());
            } else {
                $rows[] = [
                    'top' => $itemBox->top,
                    'bottom' => $itemBox->bottom(),
                    'items' => [$item],
                ];
            }
        }

        // Sort rows top-to-bottom
        usort($rows, fn($a, $b) => $a['top'] <=> $b['top']);

        return array_map(fn($row) => $row['items'], $rows);
    }
}
