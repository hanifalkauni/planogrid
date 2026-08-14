<?php

namespace Alkauni\Planogrid\Contracts;

use Alkauni\Planogrid\DTO\PlanogramGridResult;

interface GridSorterInterface
{
    /**
     * Sort AWS Rekognition detection items into a 2D Planogram Grid Matrix (Rows x Columns).
     *
     * @param array $customLabels Raw detection items array
     * @param float $imageWidth Actual or reference image width
     * @param float $imageHeight Actual or reference image height
     * @return PlanogramGridResult
     */
    public function sort(array $customLabels, float $imageWidth = 1.0, float $imageHeight = 1.0): PlanogramGridResult;

    /**
     * Set the row sorting strategy.
     */
    public function setRowStrategy(RowSortingStrategyInterface $strategy): static;
}
