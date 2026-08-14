<?php

namespace Alkauni\Planogrid\Contracts;

use Alkauni\Planogrid\DTO\ImageAnnotationConfig;

interface ImageAnnotatorInterface
{
    /**
     * Annotate input image binary with bounding boxes and brand labels.
     *
     * @param string|resource $imageBinary Raw image binary stream or file path
     * @param array $detections List of CustomLabelDetection items or raw AWS labels
     * @param array $matchStatuses Optional map of item status (e.g. MatchStatus enum or string per item)
     * @param ImageAnnotationConfig|null $config Optional custom drawing options
     * @return string PNG binary image stream
     */
    public function annotate(
        mixed $imageBinary,
        array $detections,
        array $matchStatuses = [],
        ?ImageAnnotationConfig $config = null
    ): string;
}
