<?php

namespace Alkauni\Planogrid\Integrations\Laravel;

use Alkauni\Planogrid\PlanogramProcessor;
use Illuminate\Support\ServiceProvider;

class PlanogridServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PlanogramProcessor::class, function () {
            return new PlanogramProcessor();
        });

        $this->app->alias(PlanogramProcessor::class, 'planogrid');
    }

    public function boot(): void
    {
        // Standalone package bootstrap hook
    }
}
