<?php

namespace Alkauni\Planogrid\Tests\Unit;

use Alkauni\Planogrid\Services\SpatialGridSorter;
use Alkauni\Planogrid\Strategies\BaselineAnchorStrategy;
use Alkauni\Planogrid\Strategies\CenterYOverlapStrategy;
use Alkauni\Planogrid\Strategies\SequentialDeltaStrategy;
use Alkauni\Planogrid\Strategies\ShelfProjectionStrategy;
use Alkauni\Planogrid\Strategies\SpatialClusterStrategy;
use Alkauni\Planogrid\Strategies\VerticalIoUStrategy;
use PHPUnit\Framework\TestCase;

class SpatialGridSorterTest extends TestCase
{
    private array $sampleAwsLabels;

    protected function setUp(): void
    {
        // 4 products arranged in 2 rows x 2 columns:
        // Row 1: Product A (top 100, left 100), Product B (top 110, left 300)
        // Row 2: Product C (top 350, left 105), Product D (top 360, left 305)
        $this->sampleAwsLabels = [
            [
                'Name' => 'Product B 500ml',
                'Confidence' => 99.0,
                'Geometry' => ['BoundingBox' => ['Left' => 0.30, 'Top' => 0.11, 'Width' => 0.15, 'Height' => 0.20]],
            ],
            [
                'Name' => 'Product A 250ml',
                'Confidence' => 98.0,
                'Geometry' => ['BoundingBox' => ['Left' => 0.10, 'Top' => 0.10, 'Width' => 0.15, 'Height' => 0.20]],
            ],
            [
                'Name' => 'Product D 1L',
                'Confidence' => 95.0,
                'Geometry' => ['BoundingBox' => ['Left' => 0.31, 'Top' => 0.36, 'Width' => 0.15, 'Height' => 0.20]],
            ],
            [
                'Name' => 'Product C 750ml',
                'Confidence' => 97.0,
                'Geometry' => ['BoundingBox' => ['Left' => 0.11, 'Top' => 0.35, 'Width' => 0.15, 'Height' => 0.20]],
            ],
        ];
    }

    public function test_strategy_0_sequential_delta(): void
    {
        $sorter = new SpatialGridSorter(new SequentialDeltaStrategy());
        $grid = $sorter->sort($this->sampleAwsLabels, 1000, 1000);

        $result = $grid->getResult();

        $this->assertCount(2, $result); // 2 rows
        $this->assertEquals('Product A 250ml', $result[0]['Brand 1']);
        $this->assertEquals('Product B 500ml', $result[0]['Brand 2']);
        $this->assertEquals('Product C 750ml', $result[1]['Brand 1']);
        $this->assertEquals('Product D 1L', $result[1]['Brand 2']);
    }

    public function test_strategy_1_baseline_anchor(): void
    {
        $sorter = new SpatialGridSorter(new BaselineAnchorStrategy());
        $grid = $sorter->sort($this->sampleAwsLabels, 1000, 1000);

        $result = $grid->getResult();

        $this->assertCount(2, $result);
        $this->assertEquals('Product A 250ml', $result[0]['Brand 1']);
        $this->assertEquals('Product B 500ml', $result[0]['Brand 2']);
    }

    public function test_strategy_2_center_y_overlap(): void
    {
        $sorter = new SpatialGridSorter(new CenterYOverlapStrategy());
        $grid = $sorter->sort($this->sampleAwsLabels, 1000, 1000);

        $result = $grid->getResult();

        $this->assertCount(2, $result);
        $this->assertEquals('Product A 250ml', $result[0]['Brand 1']);
        $this->assertEquals('Product B 500ml', $result[0]['Brand 2']);
    }

    public function test_strategy_3_spatial_cluster(): void
    {
        $sorter = new SpatialGridSorter(new SpatialClusterStrategy());
        $grid = $sorter->sort($this->sampleAwsLabels, 1000, 1000);

        $result = $grid->getResult();

        $this->assertCount(2, $result);
        $this->assertEquals('Product A 250ml', $result[0]['Brand 1']);
        $this->assertEquals('Product B 500ml', $result[0]['Brand 2']);
    }

    public function test_strategy_4_vertical_iou(): void
    {
        $sorter = new SpatialGridSorter(new VerticalIoUStrategy());
        $grid = $sorter->sort($this->sampleAwsLabels, 1000, 1000);

        $result = $grid->getResult();

        $this->assertCount(2, $result);
        $this->assertEquals('Product A 250ml', $result[0]['Brand 1']);
        $this->assertEquals('Product B 500ml', $result[0]['Brand 2']);
    }

    public function test_strategy_5_shelf_projection(): void
    {
        $sorter = new SpatialGridSorter(new ShelfProjectionStrategy());
        $grid = $sorter->sort($this->sampleAwsLabels, 1000, 1000);

        $result = $grid->getResult();

        $this->assertCount(2, $result);
        $this->assertEquals('Product A 250ml', $result[0]['Brand 1']);
        $this->assertEquals('Product B 500ml', $result[0]['Brand 2']);
    }
}
