# Strategy 0: `SequentialDeltaStrategy` (Implementasi Default)

🌐 **Languages / Bahasa**: [English](strategy-0-sequential-delta.md) | [Bahasa Indonesia](strategy-0-sequential-delta.id.md)

---

## 📌 Ringkasan

`SequentialDeltaStrategy` adalah strategi pengurutan spasial baris default. Strategi ini mengukur selisih vertikal (*delta Y*) antara posisi `Top` produk ke-N dengan posisi `Top` produk ke-(N-1) terhadap ambang batas tinggi terkecil produk (`minHeight`).

---

## 📐 Formula Matematika & Logika Proses

1. **Hitung `minHeight`**:
   $$\text{minHeight} = \min(\{ \text{height}_1, \text{height}_2, \dots, \text{height}_n \})$$
2. **Urutkan Seluruh Bounding Box**:
   Diurutkan berdasarkan posisi vertikal teratas (`Top`) secara ascending (dari paling atas ke bawah).
3. **Evaluasi Selisih Jarak Vertikal (`diffRangeValue`)**:
   $$\text{diffRangeValue} = | \text{lastTop} - \text{currentTop} |$$
4. **Aturan Keputusan Perpindahan Baris**:
   - **Kondisi 1: Pindah Baris Baru (`indexRow++`)**
     $$\text{Jika } \text{diffRangeValue} \ge \text{minHeight}$$
     Produk ditempatkan di baris baru di bawahnya.
   - **Kondisi 2: Tetap di Baris Sama**
     $$\text{Jika } \text{diffRangeValue} < \text{minHeight}$$
     Produk tetap berada di baris horizontal aktif yang sama.

---

## 💡 Contoh Simulasi Kasus Nyata (Tracing Data)

### Data Input Detections:
- **Produk A**: Top = `100px`, Height = `200px`
- **Produk B**: Top = `120px`, Height = `210px`
- **Produk C**: Top = `350px`, Height = `190px`
- **Produk D**: Top = `360px`, Height = `200px`

### Langkah Eksekusi:
1. `minHeight` = $\min(200, 210, 190, 200) = \mathbf{190px}$.
2. **Produk A** (Top 100px): Produk pertama $\rightarrow$ dimasukkan ke **Baris 0**. (`lastTop` = 100px).
3. **Produk B** (Top 120px): $\text{diffRangeValue} = |100 - 120| = 20\text{px} < 190\text{px} \rightarrow$ **Baris 0** (`lastTop` = 120px).
4. **Produk C** (Top 350px): $\text{diffRangeValue} = |120 - 350| = 230\text{px} \ge 190\text{px} \rightarrow$ **Baris 1 (Pindah Baris!)** (`lastTop` = 350px).
5. **Produk D** (Top 360px): $\text{diffRangeValue} = |350 - 360| = 10\text{px} < 190\text{px} \rightarrow$ **Baris 1** (`lastTop` = 360px).

### Hasil Akhir Matriks:
- **Baris 0**: `[Produk A, Produk B]`
- **Baris 1**: `[Produk C, Produk D]`

---

## 💻 Contoh Penggunaan Code (PHP)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\SequentialDeltaStrategy;

$processor = new PlanogramProcessor();
$result = $processor
    ->setRowStrategy(new SequentialDeltaStrategy())
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 Kapan Harus Menggunakan Strategi Ini?

- **Rekomendasi**: Penggunaan default untuk rak standar dengan tinggi produk relatif seragam dan foto diambil tegak lurus.
- **Kelebihan**: Kecepatan eksekusi sangat tinggi ($O(N \log N)$), komputasi sangat ringan.
- **Kekurangan**: Rawan terjadi *chained drift* jika letak produk di rak sedikit miring bertingkat secara gradual.
