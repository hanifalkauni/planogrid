<?php

namespace Alkauni\Planogrid;

use Alkauni\Planogrid\Contracts\GridSorterInterface;
use Alkauni\Planogrid\Contracts\ImageAnnotatorInterface;
use Alkauni\Planogrid\Contracts\PlanogramMatcherInterface;
use Alkauni\Planogrid\Contracts\RowSortingStrategyInterface;
use Alkauni\Planogrid\DTO\CustomLabelDetection;
use Alkauni\Planogrid\DTO\ImageAnnotationConfig;
use Alkauni\Planogrid\DTO\PlanogramEvaluation;
use Alkauni\Planogrid\DTO\PlanogramGridResult;
use Alkauni\Planogrid\DTO\PlanogramTemplate;
use Alkauni\Planogrid\Services\ImageAnnotatorService;
use Alkauni\Planogrid\Services\PlanogramMatcherService;
use Alkauni\Planogrid\Services\SpatialGridSorter;

class PlanogramProcessor
{
    private GridSorterInterface $sorter;
    private PlanogramMatcherInterface $matcher;
    private ImageAnnotatorInterface $annotator;

    private float $thresholdScore = 100.0;
    private ImageAnnotationConfig $imageConfig;

    public function __construct(
        ?GridSorterInterface $sorter = null,
        ?PlanogramMatcherInterface $matcher = null,
        ?ImageAnnotatorInterface $annotator = null
    ) {
        $this->sorter = $sorter ?? new SpatialGridSorter();
        $this->matcher = $matcher ?? new PlanogramMatcherService();
        $this->annotator = $annotator ?? new ImageAnnotatorService();
        $this->imageConfig = ImageAnnotationConfig::default();
    }

    /**
     * Fluent setter for row sorting strategy (Strategy 0 s/d Strategy 5)
     */
    public function setRowStrategy(RowSortingStrategyInterface $strategy): static
    {
        $this->sorter->setRowStrategy($strategy);
        return $this;
    }

    /**
     * Fluent setter for minimum compliance score threshold percentage.
     */
    public function setThresholdScore(float $score): static
    {
        $this->thresholdScore = $score;
        return $this;
    }

    /**
     * Fluent setter for image annotator configuration.
     */
    public function setImageConfig(ImageAnnotationConfig $config): static
    {
        $this->imageConfig = $config;
        return $this;
    }

    /**
     * Execute spatial grid sorting (Returns PlanogramGridResult matrix).
     */
    public function process(
        array $customLabels,
        float $imageWidth = 1.0,
        float $imageHeight = 1.0
    ): PlanogramGridResult {
        return $this->sorter->sort($customLabels, $imageWidth, $imageHeight);
    }

    /**
     * Annotate input image with bounding boxes and brand labels.
     */
    public function annotate(
        mixed $imageBinary,
        array $customLabels,
        array $matchStatuses = [],
        float $imageWidth = 1.0,
        float $imageHeight = 1.0
    ): string {
        $gridResult = $this->sorter->sort($customLabels, $imageWidth, $imageHeight);
        $flatDetections = $this->flattenGridResult($gridResult);

        return $this->annotator->annotate(
            imageBinary: $imageBinary,
            detections: $flatDetections,
            matchStatuses: $matchStatuses,
            config: $this->imageConfig
        );
    }

    /**
     * Execute full planogram verification workflow: sorting, template matching, and image annotation.
     */
    public function verify(
        mixed $imageBinary,
        array $customLabels,
        ?PlanogramTemplate $expectedTemplate = null,
        float $imageWidth = 1.0,
        float $imageHeight = 1.0
    ): PlanogramEvaluation {
        // 1. Sort detections into spatial 2D matrix
        $gridResult = $this->sorter->sort($customLabels, $imageWidth, $imageHeight);

        // 2. Perform template matching if expected template is provided
        if ($expectedTemplate !== null) {
            $evaluation = $this->matcher->match($gridResult, $expectedTemplate, $this->thresholdScore);
            $matchStatuses = $evaluation->matchStatuses;
        } else {
            $evaluation = new PlanogramEvaluation(
                complianceScore: 100.0,
                status: 'correct',
                matchedCount: count($this->flattenGridResult($gridResult)),
                totalExpected: count($this->flattenGridResult($gridResult)),
                gridResult: $gridResult
            );
            $matchStatuses = [];
        }

        // 3. Annotate image binary
        $flatDetections = $this->flattenGridResult($gridResult);
        $annotatedImageBinary = $this->annotator->annotate(
            imageBinary: $imageBinary,
            detections: $flatDetections,
            matchStatuses: $matchStatuses,
            config: $this->imageConfig
        );

        return new PlanogramEvaluation(
            complianceScore: $evaluation->complianceScore,
            status: $evaluation->status,
            matchedCount: $evaluation->matchedCount,
            totalExpected: $evaluation->totalExpected,
            gridResult: $gridResult,
            annotatedImage: $annotatedImageBinary,
            matchStatuses: $matchStatuses,
            mismatches: $evaluation->mismatches
        );
    }

    /**
     * Helper to flatten 2D matrix back into 1D list of CustomLabelDetection items.
     * @return array<int, CustomLabelDetection>
     */
    private function flattenGridResult(PlanogramGridResult $gridResult): array
    {
        $flat = [];
        foreach ($gridResult->matrix as $rowItems) {
            foreach ($rowItems as $item) {
                $flat[] = $item;
            }
        }
        return $flat;
    }
}
