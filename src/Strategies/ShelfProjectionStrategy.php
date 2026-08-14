<?php

namespace Alkauni\Planogrid\Strategies;

use Alkauni\Planogrid\Contracts\RowSortingStrategyInterface;
use Alkauni\Planogrid\DTO\CustomLabelDetection;

/**
 * Strategy 5: Shelf Projection Strategy (1D Vertical Histogram Gap Projection)
 * Projects bounding box vertical coverage onto a 1D density histogram.
 * Low density valleys in the histogram identify physical shelf gaps separating rows.
 */
class ShelfProjectionStrategy implements RowSortingStrategyInterface
{
    public function __construct(
        private readonly int $histogramResolution = 200
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

        // Determine min Top and max Bottom
        $minTop = INF;
        $maxBottom = -INF;

        foreach ($items as $item) {
            $minTop = min($minTop, $item->box->top);
            $maxBottom = max($maxBottom, $item->box->bottom());
        }

        $span = max(1.0, $maxBottom - $minTop);
        $bins = array_fill(0, $this->histogramResolution, 0);

        // Fill 1D vertical density histogram
        foreach ($items as $item) {
            $startBin = (int) floor((($item->box->top - $minTop) / $span) * ($this->histogramResolution - 1));
            $endBin = (int) ceil((($item->box->bottom() - $minTop) / $span) * ($this->histogramResolution - 1));

            $startBin = max(0, min($this->histogramResolution - 1, $startBin));
            $endBin = max(0, min($this->histogramResolution - 1, $endBin));

            for ($b = $startBin; $b <= $endBin; $b++) {
                $bins[$b]++;
            }
        }

        // Identify gap valleys (bins with zero or local minimum density)
        // Find shelf region boundaries where density transition happens
        $shelfRegions = [];
        $inShelf = false;
        $regionStart = 0;

        for ($b = 0; $b < $this->histogramResolution; $b++) {
            if ($bins[$b] > 0 && !$inShelf) {
                $inShelf = true;
                $regionStart = $b;
            } elseif ($bins[$b] == 0 && $inShelf) {
                $inShelf = false;
                $shelfRegions[] = [
                    'top' => $minTop + ($regionStart / $this->histogramResolution) * $span,
                    'bottom' => $minTop + ($b / $this->histogramResolution) * $span,
                ];
            }
        }

        if ($inShelf) {
            $shelfRegions[] = [
                'top' => $minTop + ($regionStart / $this->histogramResolution) * $span,
                'bottom' => $maxBottom,
            ];
        }

        if (empty($shelfRegions)) {
            return [$items];
        }

        // Assign each item to the shelf region with maximum vertical overlap
        $rows = array_fill(0, count($shelfRegions), []);

        foreach ($items as $item) {
            $itemCenterY = $item->box->centerY();
            $bestRegionIndex = 0;
            $minDistance = INF;

            foreach ($shelfRegions as $index => $region) {
                if ($itemCenterY >= $region['top'] && $itemCenterY <= $region['bottom']) {
                    $bestRegionIndex = $index;
                    break;
                }
                $regionCenterY = ($region['top'] + $region['bottom']) / 2.0;
                $dist = abs($itemCenterY - $regionCenterY);
                if ($dist < $minDistance) {
                    $minDistance = $dist;
                    $bestRegionIndex = $index;
                }
            }

            $rows[$bestRegionIndex][] = $item;
        }

        // Filter out empty rows
        $filteredRows = array_values(array_filter($rows, fn($row) => !empty($row)));

        return !empty($filteredRows) ? $filteredRows : [$items];
    }
}
