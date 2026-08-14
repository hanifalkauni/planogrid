# Strategy 3: `SpatialClusterStrategy` (1D Center-Y Density Clustering)

🌐 **Languages / Bahasa**: [English](strategy-3-spatial-cluster.md) | [Bahasa Indonesia](strategy-3-spatial-cluster.id.md)

---

## 📌 Ringkasan

`SpatialClusterStrategy` mengadopsi algoritma **1D Density-Based Clustering** (mirip 1D DBSCAN / K-Means) pada koordinat `Center Y` seluruh produk. Algoritma mencari kelompok titik koordinat tengah vertikal yang paling rapat (*dense cluster*) dengan ambang batas jarak tetangga ($\varepsilon$).

Strategi ini **paling tangguh terhadap foto miring (*perspective tilt*)** akibat pengambilan gambar dari samping atau sudut kamera yang tidak tegak lurus.

---

## 📐 Formula Matematika & Logika Proses

1. **Ekstrak Center Y**:
   $$\text{Center Y}_i = \text{Top}_i + \frac{\text{Height}_i}{2}$$
2. **Hitung Epsilon Cluster ($\varepsilon$)**:
   $$\varepsilon = \text{medianHeight} \times \text{epsFactor} \quad (\text{default epsFactor} = 0.45)$$
3. **Density Grouping**:
   - Urutkan seluruh titik $\text{Center Y}$ ascending.
   - Iterasi setiap titik: Jika jarak vertikal ke centroid kelompok aktif $|\text{Center Y}_i - \text{clusterCentroid}| \le \varepsilon$, titik dimasukkan ke cluster aktif dan centroid di-update.
   - Jika jarak $> \varepsilon$, buat cluster baru.

---

## 💡 Contoh Simulasi Kasus Nyata

### Kasus: Foto Miring Perspektif Kamera (*Tilted Photo*)
Akibat kamera miring 15 derajat ke kanan, produk di sebelah kanan rak memiliki posisi `Top` lebih rendah 60px dibanding produk di sebelah kiri rak pada baris yang sama.

- **Produk Kiri**: Top = `100px`, Bottom = `300px` $\rightarrow$ CenterY = `200px`
- **Produk Tengah**: Top = `130px`, Bottom = `330px` $\rightarrow$ CenterY = `230px`
- **Produk Kanan**: Top = `160px`, Bottom = `360px` $\rightarrow$ CenterY = `260px`

Jika menggunakan threshold `Top` konvensional, Produk Kanan mungkin terdeteksi sebagai baris baru.
Dengan **SpatialClusterStrategy** ($\varepsilon = 90\text{px}$):
- Centroid Cluster 0 mula-mula = `200px`.
- Jarak Produk Tengah = $|200 - 230| = 30\text{px} \le 90\text{px} \rightarrow$ **Cluster 0** (Centroid baru = 215px).
- Jarak Produk Kanan = $|215 - 260| = 45\text{px} \le 90\text{px} \rightarrow$ **Cluster 0**!
- **Hasil**: Seluruh produk pada rak miring berhasil dikelompokkan secara sempurna ke dalam **Baris 0**.

---

## 💻 Contoh Penggunaan Code (PHP)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\SpatialClusterStrategy;

$processor = new PlanogramProcessor();

// Set epsFactor ke 0.45
$result = $processor
    ->setRowStrategy(new SpatialClusterStrategy(epsFactor: 0.45))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 Kapan Harus Menggunakan Strategi Ini?

- **Rekomendasi**: Pengambilan foto di lapangan yang dilakukan oleh sales/merchandiser menggunakan smartphone secara terburu-buru sehingga kamera cenderung miring (*tilted angle*).
- **Kelebihan**: Sangat toleran terhadap perspektif miring dan perbedaan orientasi kamera.
- **Kekurangan**: Membutuhkan sorting 1D tambahan di awal.
