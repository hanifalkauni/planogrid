<?php

namespace Alkauni\Planogrid\Tests\Unit;

use Alkauni\Planogrid\DTO\PlanogramGridResult;
use Alkauni\Planogrid\DTO\PlanogramItem;
use Alkauni\Planogrid\DTO\PlanogramRow;
use Alkauni\Planogrid\DTO\PlanogramTemplate;
use Alkauni\Planogrid\Services\PlanogramMatcherService;
use Alkauni\Planogrid\Services\SpatialGridSorter;
use PHPUnit\Framework\TestCase;

class PlanogramMatcherTest extends TestCase
{
    public function test_template_matching_success(): void
    {
        $awsLabels = [
            [
                'Name' => 'Product Alpha 250ml',
                'Confidence' => 98.0,
                'Geometry' => ['BoundingBox' => ['Left' => 0.10, 'Top' => 0.10, 'Width' => 0.15, 'Height' => 0.20]],
            ],
            [
                'Name' => 'Product Beta 500ml',
                'Confidence' => 99.0,
                'Geometry' => ['BoundingBox' => ['Left' => 0.30, 'Top' => 0.11, 'Width' => 0.15, 'Height' => 0.20]],
            ],
        ];

        $sorter = new SpatialGridSorter();
        $gridResult = $sorter->sort($awsLabels, 1000, 1000);

        $template = new PlanogramTemplate([
            new PlanogramRow([
                new PlanogramItem('Product Alpha 250ml'),
                new PlanogramItem('Product Beta 500ml'),
            ]),
        ]);

        $matcher = new PlanogramMatcherService();
        $evaluation = $matcher->match($gridResult, $template);

        $this->assertTrue($evaluation->isCorrect());
        $this->assertEquals(100.0, $evaluation->getComplianceScore());
        $this->assertEquals(2, $evaluation->getMatchedCount());
        $this->assertEmpty($evaluation->mismatches);
    }

    public function test_template_matching_mismatch(): void
    {
        $awsLabels = [
            [
                'Name' => 'Wrong Product',
                'Confidence' => 98.0,
                'Geometry' => ['BoundingBox' => ['Left' => 0.10, 'Top' => 0.10, 'Width' => 0.15, 'Height' => 0.20]],
            ],
        ];

        $sorter = new SpatialGridSorter();
        $gridResult = $sorter->sort($awsLabels, 1000, 1000);

        $template = new PlanogramTemplate([
            new PlanogramRow([
                new PlanogramItem('Product Alpha 250ml'),
            ]),
        ]);

        $matcher = new PlanogramMatcherService();
        $evaluation = $matcher->match($gridResult, $template);

        $this->assertFalse($evaluation->isCorrect());
        $this->assertEquals(0.0, $evaluation->getComplianceScore());
        $this->assertNotEmpty($evaluation->mismatches);
    }
}
