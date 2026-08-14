<?php

namespace Alkauni\Planogrid\Tests\Integration;

use Alkauni\Planogrid\DTO\PlanogramItem;
use Alkauni\Planogrid\DTO\PlanogramRow;
use Alkauni\Planogrid\DTO\PlanogramTemplate;
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\CenterYOverlapStrategy;
use PHPUnit\Framework\TestCase;

class PlanogramProcessorTest extends TestCase
{
    private string $dummyImageBinary;

    protected function setUp(): void
    {
        // Create a 500x500 blank PNG image in memory for testing
        $im = imagecreatetruecolor(500, 500);
        $bgColor = imagecolorallocate($im, 240, 240, 240);
        imagefill($im, 0, 0, $bgColor);
        ob_start();
        imagepng($im);
        $this->dummyImageBinary = ob_get_clean();
        imagedestroy($im);
    }

    public function test_full_process_workflow(): void
    {
        $awsLabels = [
            [
                'Name' => 'Product Alpha 250ml',
                'Confidence' => 98.45,
                'Geometry' => ['BoundingBox' => ['Left' => 0.10, 'Top' => 0.15, 'Width' => 0.20, 'Height' => 0.30]],
            ],
            [
                'Name' => 'Product Beta 500ml',
                'Confidence' => 95.12,
                'Geometry' => ['BoundingBox' => ['Left' => 0.40, 'Top' => 0.16, 'Width' => 0.20, 'Height' => 0.30]],
            ],
        ];

        $processor = new PlanogramProcessor();
        $gridResult = $processor
            ->setRowStrategy(new CenterYOverlapStrategy())
            ->process($awsLabels, 500, 500);

        $resultArray = $gridResult->toArray();

        $this->assertArrayHasKey('result_geometry', $resultArray);
        $this->assertArrayHasKey('result', $resultArray);
        $this->assertEquals('Product Alpha 250ml', $resultArray['result'][0]['Brand 1']);
        $this->assertEquals('Product Beta 500ml', $resultArray['result'][0]['Brand 2']);
    }

    public function test_full_verify_workflow(): void
    {
        $awsLabels = [
            [
                'Name' => 'Product Alpha 250ml',
                'Confidence' => 98.45,
                'Geometry' => ['BoundingBox' => ['Left' => 0.10, 'Top' => 0.15, 'Width' => 0.20, 'Height' => 0.30]],
            ],
        ];

        $template = new PlanogramTemplate([
            new PlanogramRow([
                new PlanogramItem('Product Alpha 250ml'),
            ]),
        ]);

        $processor = new PlanogramProcessor();
        $evaluation = $processor->verify(
            imageBinary: $this->dummyImageBinary,
            customLabels: $awsLabels,
            expectedTemplate: $template,
            imageWidth: 500,
            imageHeight: 500
        );

        $this->assertTrue($evaluation->isCorrect());
        $this->assertEquals(100.0, $evaluation->getComplianceScore());
        $this->assertNotNull($evaluation->getAnnotatedImage());
        $this->assertGreaterThan(0, strlen($evaluation->getAnnotatedImage()));
    }
}
