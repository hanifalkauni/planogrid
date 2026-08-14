# Strategy 4: `VerticalIoUStrategy` (1D Vertical Intersection over Union)

🌐 **Languages / Bahasa**: [English](strategy-4-vertical-iou.md) | [Bahasa Indonesia](strategy-4-vertical-iou.id.md)

---

## 📌 Overview

`VerticalIoUStrategy` leverages the standard Computer Vision evaluation metric **1D Vertical Intersection over Union (IoU)**. The metric computes the ratio of the **intersection length of vertical Y spans** over the **total union length of vertical Y spans** between a candidate product bounding box and active row spans.

If the vertical IoU score is $\ge 0.40$ ($40\%$), the product is objectively assigned to that row.

---

## 📐 Mathematical Formula & Process Logic

1. **Define Vertical Spans**:
   - Item Box: $[Y_{\text{top}}, Y_{\text{bottom}}]$ with $\text{height}_1 = Y_{\text{bottom}} - Y_{\text{top}}$
   - Row Span Box: $[R_{\text{top}}, R_{\text{bottom}}]$ with $\text{height}_2 = R_{\text{bottom}} - R_{\text{top}}$
2. **Calculate Vertical Intersection ($\text{Intersection}_Y$)**:
   $$\text{Intersection}_Y = \max(0, \min(Y_{\text{bottom}}, R_{\text{bottom}}) - \max(Y_{\text{top}}, R_{\text{top}}))$$
3. **Calculate Vertical Union ($\text{Union}_Y$)**:
   $$\text{Union}_Y = \text{height}_1 + \text{height}_2 - \text{Intersection}_Y$$
4. **Calculate 1D Vertical IoU Metric**:
   $$\text{IoU}_Y = \frac{\text{Intersection}_Y}{\text{Union}_Y}$$
5. **Row Joining Rule**:
   $$\text{If } \text{IoU}_Y \ge 0.40 \quad (40\%)$$
   Select the row with the highest $\text{IoU}_Y$ score and merge the product into it.

---

## 💡 Real-World Simulation Example

### Vertical IoU Calculation:
- **Row 0 Active Span**: Top = `100px`, Bottom = `300px` (Height = `200px`)
- **New Product X**: Top = `150px`, Bottom = `350px` (Height = `200px`)

### Calculation:
1. $\text{Intersection}_Y = \min(300, 350) - \max(100, 150) = 300 - 150 = \mathbf{150px}$.
2. $\text{Union}_Y = 200 + 200 - 150 = \mathbf{250px}$.
3. $\text{IoU}_Y = \frac{150}{250} = \mathbf{0.60} \quad (60\%)$.
4. Since $0.60 \ge 0.40$, New Product X is mathematically proven valid to merge into **Row 0**.

---

## 💻 PHP Code Example

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\VerticalIoUStrategy;

$processor = new PlanogramProcessor();

// Set 1D IoU threshold to 0.40 (40%)
$result = $processor
    ->setRowStrategy(new VerticalIoUStrategy(minIoUThreshold: 0.40))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 When to Use This Strategy?

- **Recommendation**: Academic benchmarks / Computer Vision industrial audits requiring measurable, standardized mathematical metrics.
- **Pros**: Uses the most objective, standardized Computer Vision evaluation metric.
- **Cons**: Requires float division operations per item iteration.
