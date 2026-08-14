<?php

namespace Alkauni\Planogrid\Integrations\Laravel;

use Alkauni\Planogrid\DTO\ImageAnnotationConfig;
use Alkauni\Planogrid\PlanogramProcessor;
use Illuminate\Support\ServiceProvider;

class PlanogridServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $configPath = __DIR__ . '/../../../config/planogrid.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'planogrid');
        }

        $this->app->singleton(PlanogramProcessor::class, function ($app) {
            $processor = new PlanogramProcessor();

            $strategyClass = $app['config']->get('planogrid.default_strategy');
            if ($strategyClass && class_exists($strategyClass)) {
                $processor->setRowStrategy(new $strategyClass());
            }

            $threshold = $app['config']->get('planogrid.threshold_score');
            if ($threshold !== null) {
                $processor->setThresholdScore((float) $threshold);
            }

            $imgConfig = $app['config']->get('planogrid.image', []);
            if (!empty($imgConfig)) {
                $processor->setImageConfig(new ImageAnnotationConfig(
                    matchColor: $imgConfig['match_color'] ?? '#00d400',
                    mismatchColor: $imgConfig['mismatch_color'] ?? '#ff0000',
                    lowConfidenceColor: $imgConfig['low_confidence_color'] ?? '#ffcc00',
                    confidenceThreshold: (float) ($imgConfig['confidence_threshold'] ?? 85.0),
                    fontPath: $imgConfig['font_path'] ?? null,
                    fontSize: (int) ($imgConfig['font_size'] ?? 12),
                    borderThickness: (int) ($imgConfig['border_thickness'] ?? 2),
                    adaptiveFontSize: (bool) ($imgConfig['adaptive_font_size'] ?? true),
                    showConfidenceText: (bool) ($imgConfig['show_confidence_text'] ?? true)
                ));
            }

            return $processor;
        });

        $this->app->alias(PlanogramProcessor::class, 'planogrid');
    }

    public function boot(): void
    {
        $configPath = __DIR__ . '/../../../config/planogrid.php';
        if ($this->app->runningInConsole() && file_exists($configPath)) {
            $this->publishes([
                $configPath => $this->app->configPath('planogrid.php'),
            ], 'planogrid-config');
        }
    }
}
