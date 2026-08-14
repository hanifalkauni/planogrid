# Planogrid (`alkauni/planogrid`)

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://packagist.org/packages/alkauni/planogrid)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Framework Agnostic](https://img.shields.io/badge/Framework-Agnostic-success.svg)](#framework-agnostic)

🌐 **Bahasa / Language**: [Bahasa Indonesia](README.id.md) | [English](README.md)

---

**Planogrid** adalah standalone PHP package (*framework-agnostic*) berkinerja tinggi yang dirancang untuk **Pengurutan Spasial Grid Planogram 2D (Baris x Kolom)** serta **Generasi Gambar Output (*Bounding Box & Label Annotations*)** berdasarkan hasil deteksi AI (seperti AWS Rekognition Custom Labels atau Vision AI).

Package ini murni dibuat menggunakan **PHP 8.1+**, bebas ketergantungan pada framework tertentu, dan mengimplementasikan **Strategy Pattern** dengan 6 pilihan algoritma pengurutan spasial untuk mengatasi berbagai tantangan posisi rak di lapangan (foto miring, variasi tinggi produk, pergeseran bertahap/drift, dll).

---

## 📌 Fitur Utama

- **Framework-Agnostic**: Dapat digunakan pada **Laravel (10/11/12)**, **Symfony**, **CodeIgniter 4**, **Yii2**, maupun **Native PHP Script**.
- **Spatial Grid Sorter 2D**: Mengurutkan posisi objek deteksi bounding box dari acak menjadi matriks terurut **Baris (Top to Bottom)** dan **Kolom (Left to Right)**.
- **6 Strategi Pengurutan Baris (Strategy Pattern)**:
  - 🔹 **Strategy 0 (`SequentialDeltaStrategy`)**: Default sequential delta `diffRangeValue >= minHeight`.
  - 🔹 **Strategy 1 (`BaselineAnchorStrategy`)**: Mengunci baseline top awal baris untuk mencegah akumulasi *chained drift*.
  - 🔹 **Strategy 2 (`CenterYOverlapStrategy`)**: Pengelompokan berbasis rentang vertikal titik tengah (Center-Y), ideal untuk variasi produk tinggi dan pendek pada rak yang sama.
  - 🔹 **Strategy 3 (`SpatialClusterStrategy`)**: 1D Density Clustering pada Center-Y untuk toleransi foto miring (*perspective tilt*).
  - 🔹 **Strategy 4 (`VerticalIoUStrategy`)**: Pengelompokan menggunakan metrik standar Computer Vision **1D Vertical Intersection over Union (IoU)**.
  - 🔹 **Strategy 5 (`ShelfProjectionStrategy`)**: Proyeksi gap histogram 1D vertikal untuk mendeteksi papan rak fisik.
- **Visual Image Annotator Interaktif (Intervention Image v3)**:
  - Bounding box 2px ganda (*double-line offset*) anti-aliasing blur.
  - **Warna Bounding Box Dinamis**:
    - 🟩 **Hijau (`#00d400`)**: Match / Verifikasi sesuai planogram.
    - 🟥 **Merah (`#ff0000`)**: Misplaced / Competitor item.
    - 🟨 **Kuning (`#ffcc00`)**: Confidence score di bawah threshold.
  - **Adaptive Font Sizing & Lebar Label**: Otomatis menyesuaikan panjang string nama produk.
  - **Dukungan Font TrueType (`.ttf`) Custom**.
- **Template Verifier & Compliance Scorer**: Membandingkan matriks hasil deteksi terhadap `PlanogramTemplate` acuan untuk mendapatkan skor persentase kesesuaian (% compliance).

---

## 💻 Persyaratan Minimum

- **PHP**: `^8.1` (Disarankan PHP 8.2 / 8.3)
- **Ekstensi PHP**: `gd` atau `imagick`
- **Package Dependency**: `intervention/image: ^3.0`

---

## 🚀 Instalasi

Instal package via Composer:

```bash
composer require alkauni/planogrid
```

---

## 📖 Panduan Penggunaan

### 1. Pengurutan Grid Spasial Sederhana (`process`)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\CenterYOverlapStrategy;

// 1. Data hasil deteksi AWS Rekognition Custom Labels
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

// 2. Inisialisasi Processor dengan Strategi Pilihan
$processor = new PlanogramProcessor();
$gridResult = $processor
    ->setRowStrategy(new CenterYOverlapStrategy())
    ->process(
        customLabels: $customLabels,
        imageWidth: 1000,   // Resolusi lebar gambar asli (piksel)
        imageHeight: 1000   // Resolusi tinggi gambar asli (piksel)
    );

// 3. Mengambil Output Data
$resultGeometry = $gridResult->getResultGeometry(); // Array koordinat piksel terurut [row][col]
$resultBrands   = $gridResult->getResult();         // Array matriks "Brand 1", "Brand 2"
$jsonOutput     = $gridResult->toJson();
```

---

### 2. Verifikasi Planogram & Output Gambar (`verify`)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\DTO\PlanogramTemplate;
use Alkauni\Planogrid\DTO\PlanogramRow;
use Alkauni\Planogrid\DTO\PlanogramItem;
use Alkauni\Planogrid\Strategies\VerticalIoUStrategy;

// 1. Definisikan Template Planogram Acuan (Ideal Grid)
$expectedTemplate = new PlanogramTemplate([
    // Baris 1 (Rak Atas)
    new PlanogramRow([
        new PlanogramItem('Product Alpha 250ml'),
        new PlanogramItem('Product Beta 500ml'),
    ]),
    // Baris 2 (Rak Bawah)
    new PlanogramRow([
        new PlanogramItem('Product Gamma 1L'),
        new PlanogramItem('Product Delta 100g'),
    ]),
]);

// 2. Baca Binary File Foto
$imageBinary = file_get_contents('shelf_photo.jpg');

// 3. Eksekusi Verifikasi Complete Workflow
$processor = new PlanogramProcessor();
$evaluation = $processor
    ->setRowStrategy(new VerticalIoUStrategy())
    ->setThresholdScore(100.0) // Threshold kelulusan (100%)
    ->verify(
        imageBinary: $imageBinary,
        customLabels: $customLabels,
        expectedTemplate: $expectedTemplate,
        imageWidth: 1000,
        imageHeight: 1000
    );

// 4. Ambil Hasil Verifikasi
echo "Status: " . $evaluation->getStatus(); // "correct" atau "incorrect"
echo "Score: " . $evaluation->getComplianceScore() . "%";
echo "Matched: " . $evaluation->getMatchedCount() . " dari " . $evaluation->totalExpected;

// 5. Simpan Gambar Output PNG Ber-Bounding Box
file_put_contents('output_annotated.png', $evaluation->getAnnotatedImage());
```

---

## 📊 Komparasi 6 Strategi Pengurutan Baris

| Strategi | Class Name | Karakteristik Utama | Panduan Detail | Rekomendasi Kasus Penggunaan |
| :--- | :--- | :--- | :---: | :--- |
| **Strategy 0** | `SequentialDeltaStrategy` | Delasi posisi Top `diffRangeValue >= minHeight` | [🇮🇩 Indonesia](docs/strategies/strategy-0-sequential-delta.id.md) / [🇬🇧 English](docs/strategies/strategy-0-sequential-delta.md) | Default sederhana & sangat cepat |
| **Strategy 1** | `BaselineAnchorStrategy` | Mengunci top anchor awal baris + multiplier | [🇮🇩 Indonesia](docs/strategies/strategy-1-baseline-anchor.id.md) / [🇬🇧 English](docs/strategies/strategy-1-baseline-anchor.md) | Rak panjang untuk mencegah akumulasi pergeseran |
| **Strategy 2** | `CenterYOverlapStrategy` | Overlap rentang vertikal titik tengah Center-Y | [🇮🇩 Indonesia](docs/strategies/strategy-2-center-y-overlap.id.md) / [🇬🇧 English](docs/strategies/strategy-2-center-y-overlap.md) | Variasi tinggi produk berbeda di rak yang sama |
| **Strategy 3** | `SpatialClusterStrategy` | 1D Density Clustering pada Center-Y | [🇮🇩 Indonesia](docs/strategies/strategy-3-spatial-cluster.id.md) / [🇬🇧 English](docs/strategies/strategy-3-spatial-cluster.md) | Kamera miring / sudut pandang perspektif miring |
| **Strategy 4** | `VerticalIoUStrategy` | Metrik 1D Intersection over Union (IoU >= 0.40) | [🇮🇩 Indonesia](docs/strategies/strategy-4-vertical-iou.id.md) / [🇬🇧 English](docs/strategies/strategy-4-vertical-iou.md) | Metrik standar Computer Vision yang objektif |
| **Strategy 5** | `ShelfProjectionStrategy` | Proyeksi gap histogram densitas vertikal 1D | [🇮🇩 Indonesia](docs/strategies/strategy-5-shelf-projection.id.md) / [🇬🇧 English](docs/strategies/strategy-5-shelf-projection.md) | Memisahkan produk berdasarkan papan rak fisik |

---

## 🎨 Kustomisasi Gambar Annotator

Anda dapat mengkonfigurasi warna bounding box, ukuran font, font custom TTF, dan opsi tampilan lainnya via `ImageAnnotationConfig`:

```php
use Alkauni\Planogrid\DTO\ImageAnnotationConfig;

$config = new ImageAnnotationConfig(
    matchColor: '#00d400',          // Hijau
    mismatchColor: '#ff0000',       // Merah
    lowConfidenceColor: '#ffcc00',  // Kuning
    confidenceThreshold: 85.0,      // Below 85% = Yellow box
    fontPath: '/path/to/Inter.ttf', // TrueType Font
    fontSize: 14,
    borderThickness: 2,
    adaptiveFontSize: true,
    showConfidenceText: true
);

$processor->setImageConfig($config);
```

---

## ⚡ Integrasi Framework Laravel (Opsional)

Package ini secara otomatis menautkan ServiceProvider jika di-install di Laravel via package auto-discovery.

Di Controller Laravel:

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

## 🧪 Menguji Package (Unit Testing)

Jalankan pengujian unit dan integrasi via PHPUnit:

```bash
vendor/bin/phpunit
```

---

## 📜 Lisensi

Package ini dilisensikan di bawah lisensi [MIT License](LICENSE).
