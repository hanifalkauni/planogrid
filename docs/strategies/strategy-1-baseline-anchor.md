# Strategy 1: `BaselineAnchorStrategy` (Anchor Baseline + Multiplier)

🌐 **Languages / Bahasa**: [English](strategy-1-baseline-anchor.md) | [Bahasa Indonesia](strategy-1-baseline-anchor.id.md)

---

## 📌 Overview

`BaselineAnchorStrategy` locks the `Top` coordinate of the first product in the active row as the **Row Baseline Anchor**. Subsequent products in that row are always compared directly against this initial anchor (rather than the preceding product), using a tolerance threshold scaled by median product height (`thresholdMultiplier * medianHeight`).

This strategy **eliminates cumulative chained drift** that often plagues long shelf rows.

---

## 📐 Mathematical Formula & Process Logic

1. **Calculate Median Height (`medianHeight`)**:
   Sort all bounding box heights and select the median value as the shelf scale benchmark.
2. **Calculate Tolerance Threshold (`threshold`)**:
   $$\text{threshold} = \text{medianHeight} \times \text{thresholdMultiplier} \quad (\text{default multiplier} = 0.50)$$
3. **Initialize First Row Anchor (`rowAnchorTop`)**:
   The first item sets $\text{rowAnchorTop} = \text{item}_1.\text{top}$.
4. **Evaluate Delta Against Baseline Anchor**:
   $$\text{diffAnchor} = | \text{rowAnchorTop} - \text{currentTop} |$$
   - **Condition 1: Transition to New Row (`indexRow++`)**
     $$\text{If } \text{diffAnchor} \ge \text{threshold}$$
     Reset new anchor: $\text{rowAnchorTop} = \text{currentTop}$.
   - **Condition 2: Remain in Current Row**
     $$\text{If } \text{diffAnchor} < \text{threshold}$$
     Product is placed into the active row.

---

## 💡 Real-World Simulation Example

### Chained Drift Scenario on Long Shelves:
Suppose 5 products sit on 1 long shelf with slightly staggered vertical placement:
- Product 1: Top = `100px` (Anchor)
- Product 2: Top = `115px` (Delta vs P1 = 15px)
- Product 3: Top = `130px` (Delta vs P2 = 15px)
- Product 4: Top = `145px` (Delta vs P3 = 15px)
- Product 5: Top = `160px` (Delta vs P4 = 15px)

Under **Strategy 0**, each step delta is 15px (< minHeight 190px), so Products 1 to 5 would be erroneously kept in 1 row.
With **Strategy 1** (`threshold` = 50px):
- Product 2: $|100 - 115| = 15\text{px} < 50\text{px} \rightarrow$ Row 0
- Product 3: $|100 - 130| = 30\text{px} < 50\text{px} \rightarrow$ Row 0
- Product 4: $|100 - 145| = 45\text{px} < 50\text{px} \rightarrow$ Row 0
- Product 5: $|100 - 160| = 60\text{px} \ge 50\text{px} \rightarrow$ **New Row 1!**

---

## 💻 PHP Code Example

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\BaselineAnchorStrategy;

$processor = new PlanogramProcessor();

// Using custom multiplier 0.60
$result = $processor
    ->setRowStrategy(new BaselineAnchorStrategy(thresholdMultiplier: 0.60))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 When to Use This Strategy?

- **Recommendation**: Long warehouse/supermarket shelves where products have slight gradual vertical slopes across the row.
- **Pros**: Stops cumulative vertical drift (*chained drift*).
- **Cons**: Requires tuning the threshold multiplier if product heights vary drastically.
