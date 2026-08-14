# Strategy 5: `ShelfProjectionStrategy` (Proyeksi Gap Histogram Rak 1D)

🌐 **Languages / Bahasa**: [English](strategy-5-shelf-projection.md) | [Bahasa Indonesia](strategy-5-shelf-projection.id.md)

---

## 📌 Ringkasan

`ShelfProjectionStrategy` memproyeksikan cakupan rentang vertikal seluruh bounding box ke dalam **1D Density Histogram Array**. Puncak densitas (*peaks*) pada histogram menandakan kehadiran tumpukan produk di rak, sedangkan lembah (*valleys / gaps*) dengan densitas terendah mendefinisikan posisi **papan fisik pembatas rak**.

Strategi ini **sangat intuitif dan alami** dalam memisahkan produk berdasarkan batasan papan rak nyata di lapangan.

---

## 📐 Formula Matematika & Logika Proses

1. **Inisialisasi 1D Histogram Bins**:
   Buat array $H$ berisi $N$ bin (default $N = 200$ resolution bin) dari $Y_{\min}$ hingga $Y_{\max}$.
2. **Proyeksi Luas Akumulasi Vertikal**:
   Untuk setiap produk $i$, hitung rentang bin vertikal $[b_{\text{start}}, b_{\text{end}}]$ dan tambahkan counter densitas:
   $$H[b] \leftarrow H[b] + 1 \quad \forall b \in [b_{\text{start}}, b_{\text{end}}]$$
3. **Deteksi Gap Lembaran Rak (*Valleys*)**:
   - Cari interval rentang bin dengan densitas $H[b] > 0$ sebagai zona rak (*shelf region*).
   - Zona dengan densitas $H[b] = 0$ (ruang kosong antar papan rak) diidentifikasi sebagai **ruang celah papan fisik rak**.
4. **Penempatan Produk ke Rak**:
   Setiap produk dimasukkan ke zona rak yang memiliki overlap vertikal terbesar.

---

## 💡 Contoh Visualisasi Histogram 1D Densitas

```
Tinggi Y (Pixel)   Histogram Densitas H[b]      Segmentasi Rak Fisik
0px - 100px  | [ ] 0                          <-- Gap Kosong Papan Atas
100px - 300px| [████████████████] 15 items    <-- RAK 1 (Baris 0)
300px - 350px| [ ] 0                          <-- GAP PAPAN RAK 1 (Valley)
350px - 550px| [██████████████████] 18 items  <-- RAK 2 (Baris 1)
550px - 600px| [ ] 0                          <-- GAP PAPAN RAK 2 (Valley)
```

---

## 💻 Contoh Penggunaan Code (PHP)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\ShelfProjectionStrategy;

$processor = new PlanogramProcessor();

// Set resolusi histogram ke 200 bins
$result = $processor
    ->setRowStrategy(new ShelfProjectionStrategy(histogramResolution: 200))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 Kapan Harus Menggunakan Strategi Ini?

- **Rekomendasi**: Rak fisik toko retail (Minimarket/Supermarket) di mana terdapat jarak/ruang celah udara yang jelas di antara papan kayu/besi penyangga rak.
- **Kelebihan**: Mengikuti batas fisik rak nyata di dunia nyata.
- **Kekurangan**: Membutuhkan keberadaan celah ruang kosong vertikal yang relatif terlihat antar rak.
