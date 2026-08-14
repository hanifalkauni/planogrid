# Strategy 2: `CenterYOverlapStrategy` (Vertical Range Intersect)

🌐 **Languages / Bahasa**: [English](strategy-2-center-y-overlap.md) | [Bahasa Indonesia](strategy-2-center-y-overlap.id.md)

---

## 📌 Ringkasan

`CenterYOverlapStrategy` mengelompokkan produk ke dalam baris berdasarkan **titik tengah vertikal (`Center Y`)** dan **rasio irisan rentang vertikal (`[Top, Bottom]`)**. Jika `Center Y` suatu produk baru berada di dalam batas vertikal baris yang sudah ada, atau jika rentang vertikalnya saling beririsan (overlap) lebih dari $\ge 50\%$, produk tersebut secara otomatis digabungkan ke baris tersebut.

Strategi ini **sangat tangguh** untuk rak yang memuat gabungan produk tinggi (seperti botol sirup/kecap) dan produk pendek (seperti bumbu saset/kaleng kecil) pada rak yang sama.

---

## 📐 Formula Matematika & Logika Proses

1. **Hitung Titik Tengah Vertikal (`Center Y`)**:
   $$\text{Center Y} = \text{Top} + \frac{\text{Height}}{2}$$
2. **Hitung Irisan Rentang Vertikal (*Vertical Overlap*)**:
   $$\text{overlapTop} = \max(\text{row.top}, \text{item.top})$$
   $$\text{overlapBottom} = \min(\text{row.bottom}, \text{item.bottom})$$
   $$\text{overlapHeight} = \max(0, \text{overlapBottom} - \text{overlapTop})$$
   $$\text{overlapRatio} = \frac{\text{overlapHeight}}{\text{item.height}}$$
3. **Syarat Penggabungan Baris**:
   $$\text{Jika } (\text{row.top} \le \text{item.CenterY} \le \text{row.bottom}) \quad \text{atau} \quad \text{overlapRatio} \ge 0.50$$
   Maka item digabungkan ke baris tersebut dan batas rentang baris di-update:
   $$\text{row.top} = \min(\text{row.top}, \text{item.top})$$
   $$\text{row.bottom} = \max(\text{row.bottom}, \text{item.bottom})$$

---

## 💡 Contoh Simulasi Kasus Nyata

### Kasus: Botol Tinggi vs Kaleng Pendek pada Rak 1

- **Botol Sirup A**: Top = `100px`, Height = `300px` $\rightarrow$ Bottom = `400px`, CenterY = `250px`. (Baris 0 Rentang: `[100, 400]`).
- **Kaleng Kecil B**: Top = `180px`, Height = `100px` $\rightarrow$ Bottom = `280px`, CenterY = `230px`.

### Uji Evaluasi:
- `CenterY` Kaleng B (`230px`) jatuh di dalam rentang Baris 0 `[100, 400]`.
- Irisan rentang vertikal = `[180, 280]` (100px) / Height Kaleng B (100px) = **100% overlap**.
- **Hasil**: Kaleng Kecil B sukses digabungkan ke **Baris 0** bersama Botol Sirup A meskipun tingginya berbeda jauh!

---

## 💻 Contoh Penggunaan Code (PHP)

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\CenterYOverlapStrategy;

$processor = new PlanogramProcessor();

// Set minimum overlap ratio 50%
$result = $processor
    ->setRowStrategy(new CenterYOverlapStrategy(minOverlapRatio: 0.50))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 Kapan Harus Menggunakan Strategi Ini?

- **Rekomendasi**: Rak display campuran yang menampung variasi tinggi produk yang sangat kontras (misal: botol 1L disandingkan dengan kaleng 250ml).
- **Kelebihan**: Bebas bug pemisahan baris salah pada produk pendek vs produk tinggi.
- **Kekurangan**: Membutuhkan sedikit kalkulasi rentang dinamis (*boundary update*).
