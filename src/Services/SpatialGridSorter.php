<?php

namespace Alkauni\Planogrid\Services;

use Alkauni\Planogrid\Contracts\GridSorterInterface;
use Alkauni\Planogrid\Contracts\RowSortingStrategyInterface;
use Alkauni\Planogrid\DTO\CustomLabelDetection;
use Alkauni\Planogrid\DTO\PlanogramGridResult;
use Alkauni\Planogrid\Strategies\SequentialDeltaStrategy;

class SpatialGridSorter implements GridSorterInterface
{
    private RowSortingStrategyInterface $strategy;

    public function __construct(?RowSortingStrategyInterface $strategy = null)
    {
        $this->strategy = $strategy ?? new SequentialDeltaStrategy();
    }

    public function setRowStrategy(RowSortingStrategyInterface $strategy): static
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function sort(array $customLabels, float $imageWidth = 1.0, float $imageHeight = 1.0): PlanogramGridResult
    {
        if (empty($customLabels)) {
            return new PlanogramGridResult([]);
        }

        // 1. Parse array into CustomLabelDetection DTOs
        $detections = [];
        foreach ($customLabels as $item) {
            if ($item instanceof CustomLabelDetection) {
                $detections[] = $item;
            } else if (is_array($item)) {
                $detections[] = CustomLabelDetection::fromArray($item, $imageWidth, $imageHeight);
            }
        }

        if (empty($detections)) {
            return new PlanogramGridResult([]);
        }

        // 2. Initial Sort Top-to-Bottom by Top Y coordinate ascending
        usort($detections, fn(CustomLabelDetection $a, CustomLabelDetection $b) => $a->box->top <=> $b->box->top);

        // 3. Delegate row clustering to active strategy
        $rawRows = $this->strategy->sortIntoRows($detections);

        // 4. Sort columns in each row Left-to-Right by Left X coordinate ascending
        $finalMatrix = [];
        foreach ($rawRows as $rowItems) {
            usort($rowItems, fn(CustomLabelDetection $a, CustomLabelDetection $b) => $a->box->left <=> $b->box->left);
            $finalMatrix[] = $rowItems;
        }

        return new PlanogramGridResult($finalMatrix);
    }
}
