# Strategy 2: `CenterYOverlapStrategy` (Vertical Range Intersect)

🌐 **Languages / Bahasa**: [English](strategy-2-center-y-overlap.md) | [Bahasa Indonesia](strategy-2-center-y-overlap.id.md)

---

## 📌 Overview

`CenterYOverlapStrategy` clusters products into rows based on their **vertical center point (`Center Y`)** and **vertical range overlap ratio (`[Top, Bottom]`)**. If a new product's `Center Y` falls within the vertical bounds of an existing row, or if its vertical range overlaps the row by $\ge 50\%$, the product is automatically joined into that row.

This strategy is **exceptionally robust** for mixed-height shelves containing both tall items (like syrup/sauce bottles) and short items (like spice packets/small cans) side-by-side on the same shelf.

---

## 📐 Mathematical Formula & Process Logic

1. **Calculate Vertical Center Point (`Center Y`)**:
   $$\text{Center Y} = \text{Top} + \frac{\text{Height}}{2}$$
2. **Calculate Vertical Range Overlap**:
   $$\text{overlapTop} = \max(\text{row.top}, \text{item.top})$$
   $$\text{overlapBottom} = \min(\text{row.bottom}, \text{item.bottom})$$
   $$\text{overlapHeight} = \max(0, \text{overlapBottom} - \text{overlapTop})$$
   $$\text{overlapRatio} = \frac{\text{overlapHeight}}{\text{item.height}}$$
3. **Row Membership Condition**:
   $$\text{If } (\text{row.top} \le \text{item.CenterY} \le \text{row.bottom}) \quad \text{or} \quad \text{overlapRatio} \ge 0.50$$
   Then merge item into that row and dynamically adjust the row vertical bounds:
   $$\text{row.top} = \min(\text{row.top}, \text{item.top})$$
   $$\text{row.bottom} = \max(\text{row.bottom}, \text{item.bottom})$$

---

## 💡 Real-World Simulation Example

### Scenario: Tall Bottle vs Short Can on Shelf 1

- **Syrup Bottle A**: Top = `100px`, Height = `300px` $\rightarrow$ Bottom = `400px`, CenterY = `250px`. (Row 0 Active Span: `[100, 400]`).
- **Small Can B**: Top = `180px`, Height = `100px` $\rightarrow$ Bottom = `280px`, CenterY = `230px`.

### Evaluation Test:
- Can B's `CenterY` (`230px`) falls inside Row 0's span `[100, 400]`.
- Vertical Overlap = `[180, 280]` (100px) / Can B's Height (100px) = **100% overlap**.
- **Result**: Small Can B is successfully placed into **Row 0** alongside Syrup Bottle A despite the drastic height difference!

---

## 💻 PHP Code Example

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\CenterYOverlapStrategy;

$processor = new PlanogramProcessor();

// Set minimum overlap ratio to 50%
$result = $processor
    ->setRowStrategy(new CenterYOverlapStrategy(minOverlapRatio: 0.50))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 When to Use This Strategy?

- **Recommendation**: Mixed display shelves housing products of contrasting heights (e.g. 1L bottles next to 250ml cans).
- **Pros**: Prevents erroneous row splitting when short and tall products share a shelf.
- **Cons**: Minor overhead for dynamic row boundary updates.
