<?php

namespace Alkauni\Planogrid\DTO;

class BoundingBox
{
    public function __construct(
        public readonly float $left,
        public readonly float $top,
        public readonly float $width,
        public readonly float $height,
        public readonly float $confidence = 100.0
    ) {}

    public function right(): float
    {
        return $this->left + $this->width;
    }

    public function bottom(): float
    {
        return $this->top + $this->height;
    }

    public function centerX(): float
    {
        return $this->left + ($this->width / 2.0);
    }

    public function centerY(): float
    {
        return $this->top + ($this->height / 2.0);
    }

    /**
     * Calculate 1D Vertical Intersection over Union (IoU) with another bounding box.
     */
    public function verticalIoU(BoundingBox $other): float
    {
        $top = max($this->top, $other->top);
        $bottom = min($this->bottom(), $other->bottom());
        $intersection = max(0.0, $bottom - $top);

        if ($intersection <= 0.0) {
            return 0.0;
        }

        $union = ($this->height + $other->height) - $intersection;

        return $union > 0.0 ? $intersection / $union : 0.0;
    }

    /**
     * Calculate 1D Vertical Overlap Ratio relative to this box's height.
     */
    public function verticalOverlapRatio(BoundingBox $other): float
    {
        $top = max($this->top, $other->top);
        $bottom = min($this->bottom(), $other->bottom());
        $intersection = max(0.0, $bottom - $top);

        return $this->height > 0.0 ? $intersection / $this->height : 0.0;
    }

    public function toArray(): array
    {
        return [
            'top' => round($this->top, 2),
            'left' => round($this->left, 2),
            'height' => round($this->height, 2),
            'width' => round($this->width, 2),
            'confidence' => round($this->confidence, 2),
        ];
    }
}
