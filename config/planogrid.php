<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Row Sorting Strategy
    |--------------------------------------------------------------------------
    |
    | The default strategy class used for 2D spatial row sorting.
    | Options:
    | - \Alkauni\Planogrid\Strategies\SequentialDeltaStrategy::class (Strategy 0)
    | - \Alkauni\Planogrid\Strategies\BaselineAnchorStrategy::class (Strategy 1)
    | - \Alkauni\Planogrid\Strategies\CenterYOverlapStrategy::class (Strategy 2)
    | - \Alkauni\Planogrid\Strategies\SpatialClusterStrategy::class (Strategy 3)
    | - \Alkauni\Planogrid\Strategies\VerticalIoUStrategy::class (Strategy 4)
    | - \Alkauni\Planogrid\Strategies\ShelfProjectionStrategy::class (Strategy 5)
    |
    */
    'default_strategy' => \Alkauni\Planogrid\Strategies\SequentialDeltaStrategy::class,

    /*
    |--------------------------------------------------------------------------
    | Default Verification Threshold Score (%)
    |--------------------------------------------------------------------------
    |
    | Minimum compliance score percentage required to declare verification "correct".
    |
    */
    'threshold_score' => 100.0,

    /*
    |--------------------------------------------------------------------------
    | Image Annotator Styling Configuration
    |--------------------------------------------------------------------------
    |
    | Customization options for bounding box drawing and label tags.
    |
    */
    'image' => [
        'match_color' => '#00d400',
        'mismatch_color' => '#ff0000',
        'low_confidence_color' => '#ffcc00',
        'confidence_threshold' => 85.0,
        'font_path' => null,
        'font_size' => 12,
        'border_thickness' => 2,
        'adaptive_font_size' => true,
        'show_confidence_text' => true,
    ],
];
