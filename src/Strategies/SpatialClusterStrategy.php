<?php

namespace Alkauni\Planogrid\Strategies;

use Alkauni\Planogrid\Contracts\RowSortingStrategyInterface;
use Alkauni\Planogrid\DTO\CustomLabelDetection;

/**
 * Strategy 3: Spatial Cluster Strategy (1D Density Clustering on Center-Y)
 * Clusters items by Center-Y coordinate using 1D density neighborhood distance.
 * Highly robust against photo tilt / camera angle skew.
 */
class SpatialClusterStrategy implements RowSortingStrategyInterface
{
    public function __construct(
        private readonly float $epsFactor = 0.45
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

        // Calculate median height to establish cluster epsilon distance
        $heights = array_map(fn(CustomLabelDetection $item) => $item->box->height, $items);
        sort($heights);
        $count = count($heights);
        $medianHeight = $count % 2 === 0
            ? ($heights[$count / 2 - 1] + $heights[$count / 2]) / 2.0
            : $heights[(int) floor($count / 2)];

        $eps = $medianHeight * $this->epsFactor;

        // Sort items by Center-Y ascending
        usort($items, fn(CustomLabelDetection $a, CustomLabelDetection $b) => $a->box->centerY() <=> $b->box->centerY());

        $clusters = [];
        $currentCluster = [];
        $lastCenterY = null;

        foreach ($items as $item) {
            $centerY = $item->box->centerY();

            if ($lastCenterY === null) {
                $currentCluster[] = $item;
                $lastCenterY = $centerY;
                continue;
            }

            if (abs($centerY - $lastCenterY) <= $eps) {
                $currentCluster[] = $item;
                // Update running centroid of current cluster
                $clusterCenters = array_map(fn(CustomLabelDetection $i) => $i->box->centerY(), $currentCluster);
                $lastCenterY = array_sum($clusterCenters) / count($clusterCenters);
            } else {
                $clusters[] = $currentCluster;
                $currentCluster = [$item];
                $lastCenterY = $centerY;
            }
        }

        if (!empty($currentCluster)) {
            $clusters[] = $currentCluster;
        }

        return $clusters;
    }
}
