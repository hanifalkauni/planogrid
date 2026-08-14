# Strategy 1: `BaselineAnchorStrategy` (Anchor Baseline + Multiplier)

🌐 **Languages / Bahasa**: [English](strategy-1-baseline-anchor.md) | [Bahasa Indonesia](strategy-1-baseline-anchor.id.md)

---

## 📌 Ringkasan

`BaselineAnchorStrategy` mengunci koordinat `Top` dari produk pertama pada baris aktif sebagai **Row Baseline Anchor**. Posisi `Top` dari produk-produk selanjutnya pada baris tersebut selalu dibandingkan langsung dengan anchor awal ini (bukan produk sebelumnya), dengan toleransi threshold berbasis multiplier median tinggi produk (`thresholdMultiplier * medianHeight`).

Strategi ini **menghilangkan efek akumulasi pergeseran bertahap (*chained drift*)** yang sering terjadi pada rak yang sangat panjang.

---

## 📐 Formula Matematika & Logika Proses

1. **Hitung Median Height (`medianHeight`)**:
   Urutkan tinggi seluruh bounding box dan ambil nilai median sebagai tolok ukur skala rak.
2. **Hitung Ambang Batas Toleransi (`threshold`)**:
   $$\text{threshold} = \text{medianHeight} \times \text{thresholdMultiplier} \quad (\text{default multiplier} = 0.50)$$
3. **Inisialisasi Anchor Baris Pertama (`rowAnchorTop`)**:
   Item pertama menetapkan $\text{rowAnchorTop} = \text{item}_1.\text{top}$.
4. **Evaluasi Selisih terhadap Baseline Anchor**:
   $$\text{diffAnchor} = | \text{rowAnchorTop} - \text{currentTop} |$$
   - **Kondisi 1: Pindah Baris Baru (`indexRow++`)**
     $$\text{Jika } \text{diffAnchor} \ge \text{threshold}$$
     Reset anchor baru: $\text{rowAnchorTop} = \text{currentTop}$.
   - **Kondisi 2: Tetap di Baris Sama**
     $$\text{Jika } \text{diffAnchor} < \text{threshold}$$
     Produk dimasukkan ke baris aktif.

---

## 💡 Contoh Simulasi Kasus Nyata

### Skenario *Chained Drift* pada Rak Panjang:
Misalkan terdapat 5 produk dalam 1 rak yang posisinya bergeser sedikit demi sedikit:
- Produk 1: Top = `100px` (Anchor)
- Produk 2: Top = `115px` (Selisih vs P1 = 15px)
- Produk 3: Top = `130px` (Selisih vs P2 = 15px)
- Produk 4: Top = `145px` (Selisih vs P3 = 15px)
- Produk 5: Top = `160px` (Selisih vs P4 = 15px)

Pada **Strategy 0**, selisih per step selalu 15px (< minHeight 190px), sehingga Produk 1 s/d 5 dianggap 1 baris.
Namun dengan **Strategy 1** (`threshold` = 50px):
- Produk 2: $|100 - 115| = 15\text{px} < 50\text{px} \rightarrow$ Baris 0
- Produk 3: $|100 - 130| = 30\text{px} < 50\text{px} \rightarrow$ Baris 0
- Produk 4: $|100 - 145| = 45\text{px} < 50\text{px} \rightarrow$ Baris 0
- Produk 5: $|100 - 160| = 60\text{px} \ge 50\text{px} \rightarrow$ **Pindah Baris 1!**

---

## 💻 Contoh Penggunaan Code (PHP)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\BaselineAnchorStrategy;

$processor = new PlanogramProcessor();

// Menggunakan multiplier custom 0.60
$result = $processor
    ->setRowStrategy(new BaselineAnchorStrategy(thresholdMultiplier: 0.60))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 Kapan Harus Menggunakan Strategi Ini?

- **Rekomendasi**: Rak supermarket/gudang yang sangat panjang horizontal di mana posisi produk memiliki kemiringan gradual.
- **Kelebihan**: Menghentikan pergeseran akumulatif (*chained drift*).
- **Kekurangan**: Perlu menentukan multiplier threshold yang pas jika variasi tinggi produk sangat ekstrem.
