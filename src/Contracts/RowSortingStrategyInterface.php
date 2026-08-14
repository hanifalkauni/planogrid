<?php

namespace Alkauni\Planogrid\Contracts;

use Alkauni\Planogrid\DTO\CustomLabelDetection;

interface RowSortingStrategyInterface
{
    /**
     * Group detections into rows.
     * Input: Array of CustomLabelDetection items (already ordered top-to-bottom by Y position).
     * Output: 2D Array of CustomLabelDetection items grouped by row index.
     *
     * @param array<int, CustomLabelDetection> $items
     * @return array<int, array<int, CustomLabelDetection>>
     */
    public function sortIntoRows(array $items): array;
}
