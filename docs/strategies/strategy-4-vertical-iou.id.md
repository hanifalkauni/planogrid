# Strategy 4: `VerticalIoUStrategy` (1D Vertical Intersection over Union)

🌐 **Languages / Bahasa**: [English](strategy-4-vertical-iou.md) | [Bahasa Indonesia](strategy-4-vertical-iou.id.md)

---

## 📌 Ringkasan

`VerticalIoUStrategy` menggunakan metrik standar evaluasi Computer Vision **1D Vertical Intersection over Union (IoU)**. Metrik mengukur rasio perbandingan antara **panjang irisan rentang vertikal Y** dengan **panjang total gabungan rentang vertikal Y** antara bounding box produk baru dengan rentang baris aktif.

Jika nilai IoU vertikal $\ge 0.40$ ($40\%$), produk tersebut secara objektif digabungkan ke dalam baris terpilih.

---

## 📐 Formula Matematika & Logika Proses

1. **Definisikan Rentang Vertikal**:
   - Item Box: $[Y_{\text{top}}, Y_{\text{bottom}}]$ dengan $\text{height}_1 = Y_{\text{bottom}} - Y_{\text{top}}$
   - Row Span Box: $[R_{\text{top}}, R_{\text{bottom}}]$ dengan $\text{height}_2 = R_{\text{bottom}} - R_{\text{top}}$
2. **Hitung Panjang Irisan Vertikal ($\text{Intersection}_Y$)**:
   $$\text{Intersection}_Y = \max(0, \min(Y_{\text{bottom}}, R_{\text{bottom}}) - \max(Y_{\text{top}}, R_{\text{top}}))$$
3. **Hitung Panjang Gabungan Vertikal ($\text{Union}_Y$)**:
   $$\text{Union}_Y = \text{height}_1 + \text{height}_2 - \text{Intersection}_Y$$
4. **Hitung Metrik 1D Vertical IoU**:
   $$\text{IoU}_Y = \frac{\text{Intersection}_Y}{\text{Union}_Y}$$
5. **Syarat Penggabungan Baris**:
   $$\text{Jika } \text{IoU}_Y \ge 0.40 \quad (40\%)$$
   Pilih baris dengan nilai $\text{IoU}_Y$ tertinggi dan gabungkan produk ke baris tersebut.

---

## 💡 Contoh Simulasi Kasus Nyata

### Perhitungan IoU Vertikal:
- **Baris 0 Active Span**: Top = `100px`, Bottom = `300px` (Height = `200px`)
- **Produk Baru X**: Top = `150px`, Bottom = `350px` (Height = `200px`)

### Kalkulasi:
1. $\text{Intersection}_Y = \min(300, 350) - \max(100, 150) = 300 - 150 = \mathbf{150px}$.
2. $\text{Union}_Y = 200 + 200 - 150 = \mathbf{250px}$.
3. $\text{IoU}_Y = \frac{150}{250} = \mathbf{0.60} \quad (60\%)$.
4. Karena $0.60 \ge 0.40$, Produk Baru X secara matematis terbukti valid digabungkan ke **Baris 0**.

---

## 💻 Contoh Penggunaan Code (PHP)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\VerticalIoUStrategy;

$processor = new PlanogramProcessor();

// Set threshold 1D IoU ke 0.40 (40%)
$result = $processor
    ->setRowStrategy(new VerticalIoUStrategy(minIoUThreshold: 0.40))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 Kapan Harus Menggunakan Strategi Ini?

- **Rekomendasi**: Pengujian benchmark akademis / audit Computer Vision standar industri yang membutuhkan tolok ukur matematis yang terukur dan paling teruji secara teoritis.
- **Kelebihan**: Menggunakan metrik evaluasi Computer Vision yang paling objektif, bersih, dan terstandarisasi.
- **Kekurangan**: Membutuhkan operasi pembagian float per iterasi item.
