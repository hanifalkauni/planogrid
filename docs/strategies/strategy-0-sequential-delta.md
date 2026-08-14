# Strategy 0: `SequentialDeltaStrategy` (Default Implementation)

🌐 **Languages / Bahasa**: [English](strategy-0-sequential-delta.md) | [Bahasa Indonesia](strategy-0-sequential-delta.id.md)

---

## 📌 Overview

`SequentialDeltaStrategy` is the default spatial row sorting strategy. It measures the vertical delta Y between product N's `Top` position and product (N-1)'s `Top` position against a minimum height threshold (`minHeight`).

---

## 📐 Mathematical Formula & Process Logic

1. **Calculate `minHeight`**:
   $$\text{minHeight} = \min(\{ \text{height}_1, \text{height}_2, \dots, \text{height}_n \})$$
2. **Sort All Bounding Boxes**:
   Sorted ascending by top vertical coordinate (`Top`) from top to bottom.
3. **Evaluate Vertical Delta (`diffRangeValue`)**:
   $$\text{diffRangeValue} = | \text{lastTop} - \text{currentTop} |$$
4. **Row Transition Decision Rule**:
   - **Condition 1: Transition to New Row (`indexRow++`)**
     $$\text{If } \text{diffRangeValue} \ge \text{minHeight}$$
     Product is placed into a new row below.
   - **Condition 2: Remain in Current Row**
     $$\text{If } \text{diffRangeValue} < \text{minHeight}$$
     Product stays in the active horizontal row.

---

## 💡 Real-World Simulation Example (Data Tracing)

### Input Detections Data:
- **Product A**: Top = `100px`, Height = `200px`
- **Product B**: Top = `120px`, Height = `210px`
- **Product C**: Top = `350px`, Height = `190px`
- **Product D**: Top = `360px`, Height = `200px`

### Execution Tracing:
1. `minHeight` = $\min(200, 210, 190, 200) = \mathbf{190px}$.
2. **Product A** (Top 100px): First product $\rightarrow$ assigned to **Row 0**. (`lastTop` = 100px).
3. **Product B** (Top 120px): $\text{diffRangeValue} = |100 - 120| = 20\text{px} < 190\text{px} \rightarrow$ **Row 0** (`lastTop` = 120px).
4. **Product C** (Top 350px): $\text{diffRangeValue} = |120 - 350| = 230\text{px} \ge 190\text{px} \rightarrow$ **Row 1 (New Row!)** (`lastTop` = 350px).
5. **Product D** (Top 360px): $\text{diffRangeValue} = |350 - 360| = 10\text{px} < 190\text{px} \rightarrow$ **Row 1** (`lastTop` = 360px).

### Final Matrix Output:
- **Row 0**: `[Product A, Product B]`
- **Row 1**: `[Product C, Product D]`

---

## 💻 PHP Code Example

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\SequentialDeltaStrategy;

$processor = new PlanogramProcessor();
$result = $processor
    ->setRowStrategy(new SequentialDeltaStrategy())
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 When to Use This Strategy?

- **Recommendation**: Default choice for standard shelves with relatively uniform product heights and straight camera shots.
- **Pros**: Blazing fast execution speed ($O(N \log N)$), very light computation.
- **Cons**: Vulnerable to *chained drift* if products on a long shelf lie on a slightly staggered slope.
