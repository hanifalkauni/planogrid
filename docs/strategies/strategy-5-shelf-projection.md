# Strategy 5: `ShelfProjectionStrategy` (1D Histogram Gap Projection)

🌐 **Languages / Bahasa**: [English](strategy-5-shelf-projection.md) | [Bahasa Indonesia](strategy-5-shelf-projection.id.md)

---

## 📌 Overview

`ShelfProjectionStrategy` projects the vertical height spans of all bounding boxes onto a **1D Density Histogram Array**. Density peaks in the histogram signify product stacks on physical shelves, while valleys (gaps with near-zero density) mark **physical shelf divider boards**.

This strategy is **intuitively aligned** with physical shelf dividers in real-world store environments.

---

## 📐 Mathematical Formula & Process Logic

1. **Initialize 1D Histogram Bins**:
   Create an array $H$ of $N$ bins (default $N = 200$ resolution bins) spanning from $Y_{\min}$ to $Y_{\max}$.
2. **Project Accumulated Vertical Area**:
   For each product $i$, calculate its vertical bin range $[b_{\text{start}}, b_{\text{end}}]$ and increment density counters:
   $$H[b] \leftarrow H[b] + 1 \quad \forall b \in [b_{\text{start}}, b_{\text{end}}]$$
3. **Detect Physical Shelf Board Valleys**:
   - Identify bin intervals with density $H[b] > 0$ as active shelf regions.
   - Zero-density regions (empty air gaps between shelf boards) define **physical shelf divider gaps**.
4. **Product Shelf Assignment**:
   Each product is assigned to the shelf region with which it shares maximum vertical overlap.

---

## 💡 1D Density Histogram Visualization

```
Vertical Height Y (Pixel)   Density Histogram H[b]       Physical Shelf Segmentation
0px - 100px  | [ ] 0                          <-- Empty Air Gap above Top Shelf
100px - 300px| [████████████████] 15 items    <-- SHELF 1 (Row 0)
300px - 350px| [ ] 0                          <-- SHELF 1 BOARD GAP (Valley)
350px - 550px| [██████████████████] 18 items  <-- SHELF 2 (Row 1)
550px - 600px| [ ] 0                          <-- SHELF 2 BOARD GAP (Valley)
```

---

## 💻 PHP Code Example

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\ShelfProjectionStrategy;

$processor = new PlanogramProcessor();

// Set histogram resolution to 200 bins
$result = $processor
    ->setRowStrategy(new ShelfProjectionStrategy(histogramResolution: 200))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 When to Use This Strategy?

- **Recommendation**: Physical retail store shelves (Minimarkets/Supermarkets) with visible air gaps between wooden/metal shelf boards.
- **Pros**: Matches physical shelf boundaries in the real world.
- **Cons**: Requires noticeable vertical air gaps between shelves.
