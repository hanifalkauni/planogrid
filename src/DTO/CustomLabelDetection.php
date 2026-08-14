<?php

namespace Alkauni\Planogrid\DTO;

class CustomLabelDetection
{
    public function __construct(
        public readonly string $name,
        public readonly float $confidence,
        public readonly BoundingBox $box
    ) {}

    /**
     * Parse AWS Rekognition CustomLabels detection item array into DTO.
     */
    public static function fromArray(array $data, float $imageWidth = 1.0, float $imageHeight = 1.0): self
    {
        $name = $data['Name'] ?? $data['name'] ?? 'Unknown';
        $confidence = (float) ($data['Confidence'] ?? $data['confidence'] ?? 0.0);

        $geometry = $data['Geometry'] ?? $data['geometry'] ?? [];
        $boxData = $geometry['BoundingBox'] ?? $geometry['bounding_box'] ?? $data['BoundingBox'] ?? $data['bounding_box'] ?? [];

        $leftRatio = (float) ($boxData['Left'] ?? $boxData['left'] ?? 0.0);
        $topRatio = (float) ($boxData['Top'] ?? $boxData['top'] ?? 0.0);
        $widthRatio = (float) ($boxData['Width'] ?? $boxData['width'] ?? 0.0);
        $heightRatio = (float) ($boxData['Height'] ?? $boxData['height'] ?? 0.0);

        // Check if coordinates are normalized ratio (0.0 to 1.0) or already actual pixels
        $isRatio = ($leftRatio <= 1.0 && $topRatio <= 1.0 && $widthRatio <= 1.0 && $heightRatio <= 1.0)
            && ($imageWidth > 1.0 || $imageHeight > 1.0);

        if ($isRatio) {
            $left = $leftRatio * $imageWidth;
            $top = $topRatio * $imageHeight;
            $width = $widthRatio * $imageWidth;
            $height = $heightRatio * $imageHeight;
        } else {
            $left = $leftRatio;
            $top = $topRatio;
            $width = $widthRatio;
            $height = $heightRatio;
        }

        return new self(
            name: $name,
            confidence: $confidence,
            box: new BoundingBox(
                left: $left,
                top: $top,
                width: $width,
                height: $height,
                confidence: $confidence
            )
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'confidence' => round($this->confidence, 2),
            'top' => round($this->box->top, 2),
            'left' => round($this->box->left, 2),
            'height' => round($this->box->height, 2),
            'width' => round($this->box->width, 2),
        ];
    }
}
