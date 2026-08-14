<?php

namespace Alkauni\Planogrid\Services;

use Alkauni\Planogrid\Contracts\ImageAnnotatorInterface;
use Alkauni\Planogrid\DTO\CustomLabelDetection;
use Alkauni\Planogrid\DTO\ImageAnnotationConfig;
use Alkauni\Planogrid\Enums\MatchStatus;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class ImageAnnotatorService implements ImageAnnotatorInterface
{
    private ImageManager $manager;

    public function __construct(?ImageManager $manager = null)
    {
        if ($manager !== null) {
            $this->manager = $manager;
        } else {
            $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
            $this->manager = new ImageManager($driver);
        }
    }

    public function annotate(
        mixed $imageBinary,
        array $detections,
        array $matchStatuses = [],
        ?ImageAnnotationConfig $config = null
    ): string {
        $config ??= ImageAnnotationConfig::default();
        $image = $this->manager->read($imageBinary);

        $imgWidth = (float) $image->width();
        $imgHeight = (float) $image->height();

        foreach ($detections as $index => $item) {
            $detection = $item instanceof CustomLabelDetection
                ? $item
                : CustomLabelDetection::fromArray((array) $item, $imgWidth, $imgHeight);

            $status = $matchStatuses[$index] ?? null;
            $color = $this->resolveBoxColor($detection, $status, $config);

            $x1 = (int) round($detection->box->left);
            $y1 = (int) round($detection->box->top);
            $x2 = (int) round($detection->box->right());
            $y2 = (int) round($detection->box->bottom());

            // 1. Draw 2px Bounding Box (double-line offset to prevent anti-aliasing blur)
            $this->drawBoundingBox($image, $x1, $y1, $x2, $y2, $color, $config->borderThickness);

            // 2. Calculate Label Background Box Dimensions
            $boxWidth = $detection->box->width;
            $textHolderWidth = (int) round($boxWidth * 0.85);

            if ($config->adaptiveFontSize) {
                // Adjust text holder width based on product name length
                $nameLength = strlen($detection->name);
                $estimatedTextWidth = $nameLength * 7 + 10;
                $textHolderWidth = (int) max($textHolderWidth, min($boxWidth, $estimatedTextWidth));
            }

            $holderHeight = $config->showConfidenceText ? 24 : 15;

            // Draw text background rectangle (Black #000000)
            $image->drawRectangle($x1, $y1, function ($rectangle) use ($textHolderWidth, $holderHeight) {
                $rectangle->size($textHolderWidth, $holderHeight);
                $rectangle->background('#000000');
            });

            // 3. Draw Product Name Text (White #ffffff)
            $productName = $detection->name;
            if (strlen($productName) > 25 && $textHolderWidth < 200) {
                $productName = substr($productName, 0, 22) . '...';
            }

            $fontSize = $config->fontSize;

            $image->text($productName, $x1 + 2, $y1 + 2, function ($font) use ($config, $fontSize) {
                $font->size($fontSize);
                $font->color('#ffffff');
                if ($config->fontPath && file_exists($config->fontPath)) {
                    $font->filename($config->fontPath);
                }
                $font->align('left');
                $font->valign('top');
            });

            // 4. Draw Confidence Text if enabled
            if ($config->showConfidenceText) {
                $confidenceText = sprintf('Conf: %.1f%%', $detection->confidence);
                $image->text($confidenceText, $x1 + 2, $y1 + 13, function ($font) use ($config, $color) {
                    $font->size(10);
                    $font->color($color);
                    if ($config->fontPath && file_exists($config->fontPath)) {
                        $font->filename($config->fontPath);
                    }
                    $font->align('left');
                    $font->valign('top');
                });
            }
        }

        return $image->toPng()->toString();
    }

    private function resolveBoxColor(CustomLabelDetection $detection, mixed $status, ImageAnnotationConfig $config): string
    {
        if ($status instanceof MatchStatus) {
            return match ($status) {
                MatchStatus::MATCH => $config->matchColor,
                MatchStatus::MISMATCH, MatchStatus::COMPETITOR => $config->mismatchColor,
                MatchStatus::LOW_CONFIDENCE => $config->lowConfidenceColor,
                MatchStatus::UNMATCHED => '#888888',
            };
        }

        if (is_string($status)) {
            return match (strtolower($status)) {
                'match', 'correct' => $config->matchColor,
                'mismatch', 'incorrect', 'competitor' => $config->mismatchColor,
                'low_confidence' => $config->lowConfidenceColor,
                default => $config->matchColor,
            };
        }

        if ($detection->confidence < $config->confidenceThreshold) {
            return $config->lowConfidenceColor;
        }

        return $config->matchColor;
    }

    private function drawBoundingBox($image, int $x1, int $y1, int $x2, int $y2, string $color, int $thickness = 2): void
    {
        for ($t = 0; $t < $thickness; $t++) {
            // Top line
            $image->drawLine(fn($l) => $l->from($x1, $y1 + $t)->to($x2, $y1 + $t)->color($color));
            // Left line
            $image->drawLine(fn($l) => $l->from($x1 + $t, $y1)->to($x1 + $t, $y2)->color($color));
            // Bottom line
            $image->drawLine(fn($l) => $l->from($x1, $y2 + $t)->to($x2, $y2 + $t)->color($color));
            // Right line
            $image->drawLine(fn($l) => $l->from($x2 + $t, $y1)->to($x2 + $t, $y2)->color($color));
        }
    }
}
