<?php

namespace Alkauni\Planogrid\Tests\Unit;

use Alkauni\Planogrid\DTO\BoundingBox;
use PHPUnit\Framework\TestCase;

class BoundingBoxTest extends TestCase
{
    public function test_coordinates_and_centers(): void
    {
        $box = new BoundingBox(left: 100, top: 200, width: 50, height: 100, confidence: 95.0);

        $this->assertEquals(150, $box->right());
        $this->assertEquals(300, $box->bottom());
        $this->assertEquals(125, $box->centerX());
        $this->assertEquals(250, $box->centerY());
    }

    public function test_vertical_iou_calculation(): void
    {
        $box1 = new BoundingBox(left: 0, top: 100, width: 100, height: 100); // 100 to 200
        $box2 = new BoundingBox(left: 0, top: 150, width: 100, height: 100); // 150 to 250

        // Intersection: 150 to 200 = 50
        // Union: 100 to 250 = 150
        // IoU = 50 / 150 = 0.333...

        $iou = $box1->verticalIoU($box2);
        $this->assertEqualsWithDelta(0.3333, $iou, 0.001);
    }
}
