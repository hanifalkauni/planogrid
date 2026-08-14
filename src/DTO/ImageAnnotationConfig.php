<?php

namespace Alkauni\Planogrid\DTO;

class ImageAnnotationConfig
{
    public function __construct(
        public readonly string $matchColor = '#00d400',
        public readonly string $mismatchColor = '#ff0000',
        public readonly string $lowConfidenceColor = '#ffcc00',
        public readonly float $confidenceThreshold = 85.0,
        public readonly ?string $fontPath = null,
        public readonly int $fontSize = 12,
        public readonly int $borderThickness = 2,
        public readonly bool $adaptiveFontSize = true,
        public readonly bool $showConfidenceText = true
    ) {}

    public static function default(): self
    {
        return new self();
    }
}
