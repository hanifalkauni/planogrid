# Strategy 3: `SpatialClusterStrategy` (1D Center-Y Density Clustering)

🌐 **Languages / Bahasa**: [English](strategy-3-spatial-cluster.md) | [Bahasa Indonesia](strategy-3-spatial-cluster.id.md)

---

## 📌 Overview

`SpatialClusterStrategy` adopts a **1D Density-Based Clustering** algorithm (similar to 1D DBSCAN / K-Means) on all product `Center Y` coordinates. The algorithm identifies clusters of vertical midpoints with high density bounded by a maximum neighborhood distance ($\varepsilon$).

This strategy is **exceptionally resilient against photo perspective tilt** caused by capturing shelf photos from side angles or non-perpendicular camera orientations.

---

## 📐 Mathematical Formula & Process Logic

1. **Extract Center Y**:
   $$\text{Center Y}_i = \text{Top}_i + \frac{\text{Height}_i}{2}$$
2. **Calculate Epsilon Distance ($\varepsilon$)**:
   $$\varepsilon = \text{medianHeight} \times \text{epsFactor} \quad (\text{default epsFactor} = 0.45)$$
3. **Density Grouping**:
   - Sort all $\text{Center Y}$ points ascending.
   - Iterate each point: If the vertical distance to the active cluster centroid $|\text{Center Y}_i - \text{clusterCentroid}| \le \varepsilon$, merge point into active cluster and update running centroid.
   - If distance $> \varepsilon$, open a new cluster.

---

## 💡 Real-World Simulation Example

### Scenario: Camera Perspective Tilt
Due to holding the camera tilted 15 degrees to the right, products on the right side of the shelf have `Top` coordinates 60px lower than products on the left side of the same physical shelf.

- **Left Product**: Top = `100px`, Bottom = `300px` $\rightarrow$ CenterY = `200px`
- **Middle Product**: Top = `130px`, Bottom = `330px` $\rightarrow$ CenterY = `230px`
- **Right Product**: Top = `160px`, Bottom = `360px` $\rightarrow$ CenterY = `260px`

Using conventional `Top` thresholds, the Right Product might get mistakenly split into a new row.
With **SpatialClusterStrategy** ($\varepsilon = 90\text{px}$):
- Initial Cluster 0 Centroid = `200px`.
- Middle Product Distance = $|200 - 230| = 30\text{px} \le 90\text{px} \rightarrow$ **Cluster 0** (Updated Centroid = 215px).
- Right Product Distance = $|215 - 260| = 45\text{px} \le 90\text{px} \rightarrow$ **Cluster 0**!
- **Result**: All products across the tilted shelf are successfully grouped into **Row 0**.

---

## 💻 PHP Code Example

```php
use Alkauni\Planogrid\PlanogramProcessor;
use Alkauni\Planogrid\Strategies\SpatialClusterStrategy;

$processor = new PlanogramProcessor();

// Set epsFactor to 0.45
$result = $processor
    ->setRowStrategy(new SpatialClusterStrategy(epsFactor: 0.45))
    ->process($customLabels, $imageWidth, $imageHeight);
```

---

## 🎯 When to Use This Strategy?

- **Recommendation**: Smartphone photos taken in field retail audits by merchandisers under hurried conditions where camera angles are tilted.
- **Pros**: Highly tolerant of camera orientation tilt and perspective distortion.
- **Cons**: Requires initial 1D array sorting.
