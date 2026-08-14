# Planogrid (`alkauni/planogrid`)

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://packagist.org/packages/alkauni/planogrid)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Framework Agnostic](https://img.shields.io/badge/Framework-Agnostic-success.svg)](#framework-agnostic)

🌐 **Languages**: [English](README.md) | [Bahasa Indonesia](README.id.md)

---

**Planogrid** is a high-performance, **framework-agnostic** standalone PHP package designed for **2D Planogram Spatial Grid Sorting (Row x Column)** and **Output Image Generation (Bounding Box & Label Annotations)** powered by AI detection results (such as AWS Rekognition Custom Labels or Vision AI).

Built purely with **PHP 8.1+**, it has zero framework dependencies and implements the **Strategy Pattern** with 6 spatial sorting algorithms to solve real-world shelf layout challenges (perspective tilt, varying product heights, chained vertical drift, physical shelf gap detection, etc.).

---

## 📌 Key Features

- **Framework-Agnostic**: Compatible with **Laravel (10/11/12)**, **Symfony**, **CodeIgniter 4**, **Yii2**, or **Native PHP Scripts**.
- **2D Spatial Grid Sorter**: Converts unordered bounding box detections into an ordered 2D matrix: **Rows (Top to Bottom)** and **Columns (Left to Right)**.
- **6 Spatial Row Sorting Strategies (Strategy Pattern)**:
  - 🔹 **Strategy 0 (`SequentialDeltaStrategy`)**: Default sequential delta (`diffRangeValue >= minHeight`).
  - 🔹 **Strategy 1 (`BaselineAnchorStrategy`)**: Locks row top baseline anchor to eliminate cumulative chained drift.
  - 🔹 **Strategy 2 (`CenterYOverlapStrategy`)**: Groups items based on vertical Center-Y range overlap; ideal for mixing tall bottles and short boxes on the same shelf.
  - 🔹 **Strategy 3 (`SpatialClusterStrategy`)**: 1D Density Clustering on Center-Y for camera perspective tilt tolerance.
  - 🔹 **Strategy 4 (`VerticalIoUStrategy`)**: Objectively clusters rows using standard Computer Vision **1D Vertical Intersection over Union (IoU)** metric.
  - 🔹 **Strategy 5 (`ShelfProjectionStrategy`)**: 1D Vertical density histogram gap projection to detect physical shelf dividers.
- **Interactive Visual Image Annotator (Intervention Image v3)**:
  - Parallel double-line 2px bounding box border to prevent anti-aliasing blur.
  - **Dynamic Bounding Box Colors**:
    - 🟩 **Green (`#00d400`)**: Match / Verified according to planogram.
    - 🟥 **Red (`#ff0000`)**: Misplaced item / Competitor brand.
    - 🟨 **Yellow (`#ffcc00`)**: Confidence score below threshold.
  - **Adaptive Font Sizing & Label Holder**: Dynamically scales label background to product name length.
  - **Custom TrueType Font (`.ttf`) Support**.
- **Template Verifier & Compliance Scorer**: Evaluates detected grid matrix against target `PlanogramTemplate` to produce compliance percentage scores (% compliance).

---

## 💻 Minimum Requirements

- **PHP**: `^8.1` (PHP 8.2 / 8.3 recommended)
- **PHP Extensions**: `gd` or `imagick`
- **Package Dependency**: `intervention/image: ^3.0`

---

## 🚀 Installation

Install the package via Composer:

```bash
composer require alkauni/planogrid
```

---

## 📖 Usage Guide

### 1. Spatial Grid Sorting (`process`)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\CenterYOverlapStrategy;

// 1. Raw detection data from AWS Rekognition Custom Labels
$customLabels = [
    [
        'Name' => 'Product Alpha 250ml',
        'Confidence' => 98.45,
        'Geometry' => ['BoundingBox' => ['Width' => 0.12, 'Height' => 0.25, 'Left' => 0.10, 'Top' => 0.15]],
    ],
    [
        'Name' => 'Product Beta 500ml',
        'Confidence' => 99.10,
        'Geometry' => ['BoundingBox' => ['Width' => 0.15, 'Height' => 0.25, 'Left' => 0.30, 'Top' => 0.16]],
    ],
];

// 2. Initialize Processor with Chosen Strategy
$processor = new PlanogramProcessor();
$gridResult = $processor
    ->setRowStrategy(new CenterYOverlapStrategy())
    ->process(
        customLabels: $customLabels,
        imageWidth: 1000,   // Image width in pixels
        imageHeight: 1000   // Image height in pixels
    );

// 3. Retrieve Formatted Output
$resultGeometry = $gridResult->getResultGeometry(); // Sorted pixel geometry array [row][col]
$resultBrands   = $gridResult->getResult();         // Matrix of "Brand 1", "Brand 2"
$jsonOutput     = $gridResult->toJson();
```

---

### 2. Full Verification & Image Annotation (`verify`)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\DTO\PlanogramTemplate;
use Alkauni\Planogrid\DTO\PlanogramRow;
use Alkauni\Planogrid\DTO\PlanogramItem;
use Alkauni\Planogrid\Strategies\VerticalIoUStrategy;

// 1. Define Target Planogram Template (Ideal Grid Layout)
$expectedTemplate = new PlanogramTemplate([
    // Row 1 (Top Shelf)
    new PlanogramRow([
        new PlanogramItem('Product Alpha 250ml'),
        new PlanogramItem('Product Beta 500ml'),
    ]),
    // Row 2 (Bottom Shelf)
    new PlanogramRow([
        new PlanogramItem('Product Gamma 1L'),
        new PlanogramItem('Product Delta 100g'),
    ]),
]);

// 2. Load Input Photo Binary
$imageBinary = file_get_contents('shelf_photo.jpg');

// 3. Execute Complete Verification Workflow
$processor = new PlanogramProcessor();
$evaluation = $processor
    ->setRowStrategy(new VerticalIoUStrategy())
    ->setThresholdScore(100.0) // Compliance score threshold (100%)
    ->verify(
        imageBinary: $imageBinary,
        customLabels: $customLabels,
        expectedTemplate: $expectedTemplate,
        imageWidth: 1000,
        imageHeight: 1000
    );

// 4. Inspect Verification Results
echo "Status: " . $evaluation->getStatus(); // "correct" or "incorrect"
echo "Compliance Score: " . $evaluation->getComplianceScore() . "%";
echo "Matched Items: " . $evaluation->getMatchedCount() . " of " . $evaluation->totalExpected;

// 5. Save Annotated Output PNG Image
file_put_contents('output_annotated.png', $evaluation->getAnnotatedImage());
```

---

## 📊 Comparison of 6 Spatial Sorting Strategies

| Strategy | Class Name | Core Algorithm | Detailed Guide | Recommended Use Case |
| :--- | :--- | :--- | :---: | :--- |
| **Strategy 0** | `SequentialDeltaStrategy` | Sequential delta `diffRangeValue >= minHeight` | [🇬🇧 English](docs/strategies/strategy-0-sequential-delta.md) / [🇮🇩 Indonesia](docs/strategies/strategy-0-sequential-delta.id.md) | Fast & simple default |
| **Strategy 1** | `BaselineAnchorStrategy` | Anchor initial top position + height multiplier | [🇬🇧 English](docs/strategies/strategy-1-baseline-anchor.md) / [🇮🇩 Indonesia](docs/strategies/strategy-1-baseline-anchor.id.md) | Long rows to prevent cumulative drift |
| **Strategy 2** | `CenterYOverlapStrategy` | Center-Y vertical overlap range | [🇬🇧 English](docs/strategies/strategy-2-center-y-overlap.md) / [🇮🇩 Indonesia](docs/strategies/strategy-2-center-y-overlap.id.md) | Mixing tall bottles & short boxes on same shelf |
| **Strategy 3** | `SpatialClusterStrategy` | 1D Center-Y density clustering | [🇬🇧 English](docs/strategies/strategy-3-spatial-cluster.md) / [🇮🇩 Indonesia](docs/strategies/strategy-3-spatial-cluster.id.md) | Tilted photos / camera perspective skew |
| **Strategy 4** | `VerticalIoUStrategy` | 1D Vertical Intersection over Union (IoU >= 0.40) | [🇬🇧 English](docs/strategies/strategy-4-vertical-iou.md) / [🇮🇩 Indonesia](docs/strategies/strategy-4-vertical-iou.id.md) | Objective Computer Vision standard metric |
| **Strategy 5** | `ShelfProjectionStrategy` | 1D vertical density histogram gap projection | [🇬🇧 English](docs/strategies/strategy-5-shelf-projection.md) / [🇮🇩 Indonesia](docs/strategies/strategy-5-shelf-projection.id.md) | Segmenting products by physical shelf dividers |

---

## 🎨 Image Annotator Customization

Customize bounding box colors, font size, custom TTF fonts, and labels via `ImageAnnotationConfig`:

```php
use Alkauni\Planogrid\DTO\ImageAnnotationConfig;

$config = new ImageAnnotationConfig(
    matchColor: '#00d400',          // Green for match
    mismatchColor: '#ff0000',       // Red for mismatch
    lowConfidenceColor: '#ffcc00',  // Yellow for low confidence
    confidenceThreshold: 85.0,      // Items with < 85% confidence get yellow box
    fontPath: '/path/to/Inter.ttf', // Custom TTF Font
    fontSize: 14,
    borderThickness: 2,
    adaptiveFontSize: true,
    showConfidenceText: true
);

$processor->setImageConfig($config);
```

---

## 📚 Complete API Reference

### 1. `PlanogramProcessor` (Main Facade)

The primary entrypoint class to control spatial grid sorting, template matching, and image annotations.

| Method Signature | Description | Return Type |
| :--- | :--- | :---: |
| `setRowStrategy(RowSortingStrategyInterface $strategy)` | Configures active row sorting strategy (Strategy 0 to 5). | `static` |
| `setThresholdScore(float $score)` | Sets compliance score passing percentage threshold (default `100.0`). | `static` |
| `setImageConfig(ImageAnnotationConfig $config)` | Configures visual bounding box drawing & font options. | `static` |
| `process(array $customLabels, float $imageWidth = 1.0, float $imageHeight = 1.0)` | Executes 2D spatial grid sorting and returns sorted matrix. | `PlanogramGridResult` |
| `verify(mixed $imageBinary, array $customLabels, ?PlanogramTemplate $expectedTemplate = null, float $imageWidth = 1.0, float $imageHeight = 1.0)` | Executes complete verification workflow: sorting, template matching, and image annotation. | `PlanogramEvaluation` |
| `annotate(mixed $imageBinary, array $customLabels, array $matchStatuses = [], float $imageWidth = 1.0, float $imageHeight = 1.0)` | Annotates image binary with bounding boxes & brand tags without template matching. | `string` (PNG binary) |

---

### 2. `PlanogramGridResult` (Spatial Matrix Result DTO)

| Method Signature | Description | Return Type |
| :--- | :--- | :---: |
| `getResultGeometry()` | Returns 2D matrix of sorted pixel coordinates `[row][col]` (`name`, `top`, `left`, `height`, `width`). | `array` |
| `getResult()` | Returns 2D brand label matrix `[row][Brand 1, Brand 2, ...]`. | `array` |
| `toArray()` | Returns combined `result_geometry` and `result` arrays. | `array` |
| `toJson(int $options = JSON_PRETTY_PRINT)` | Converts matrix result to formatted JSON string. | `string` |

---

### 3. `PlanogramEvaluation` (Evaluation Result DTO)

| Method Signature | Description | Return Type |
| :--- | :--- | :---: |
| `isCorrect()` | Checks if planogram verification passed (`true` if score >= threshold). | `bool` |
| `getComplianceScore()` | Returns planogram compliance score percentage (0.0% to 100.0%). | `float` |
| `getStatus()` | Returns verification status string (`"correct"` or `"incorrect"`). | `string` |
| `getMatchedCount()` | Returns count of items successfully matched with target template. | `int` |
| `getDetectedMatrix()` | Returns detected matrix structure. | `array` |
| `getAnnotatedImage()` | Returns annotated PNG image binary stream. | `?string` |
| `toArray()` | Converts evaluation result and mismatch list to array. | `array` |

---

### 4. `ImageAnnotationConfig` (Image Drawing Config DTO)

```php
new ImageAnnotationConfig(
    string $matchColor = '#00d400',         // HEX color for matched items
    string $mismatchColor = '#ff0000',      // HEX color for mismatched/competitor items
    string $lowConfidenceColor = '#ffcc00', // HEX color for low confidence score items
    float $confidenceThreshold = 85.0,     // Confidence score threshold (%)
    ?string $fontPath = null,               // Path to custom TrueType font (.ttf) file
    int $fontSize = 12,                     // Base font size
    int $borderThickness = 2,               // Bounding box border line thickness (px)
    bool $adaptiveFontSize = true,          // Auto font sizing based on product name length
    bool $showConfidenceText = true         // Render confidence score percentage text tag
);
```

---

## ⚡ Laravel Integration (Optional)

The package automatically registers its ServiceProvider via package auto-discovery in Laravel projects.

In your Laravel Controller:

```php
use Alkauni\Planogrid\PlanogramProcessor;

class PlanogramController extends Controller
{
    public function verify(Request $request, PlanogramProcessor $processor)
    {
        $evaluation = $processor->verify(
            imageBinary: $request->file('photo')->get(),
            customLabels: $request->input('custom_labels'),
            expectedTemplate: $expectedTemplate
        );

        return response()->json($evaluation->toArray());
    }
}
```

---

## 🧪 Running Tests

Execute unit and integration tests via PHPUnit:

```bash
vendor/bin/phpunit
```

---

## 📜 License

This package is open-sourced software licensed under the [MIT License](LICENSE).
