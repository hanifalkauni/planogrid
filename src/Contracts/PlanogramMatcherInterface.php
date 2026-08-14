<?php

namespace Alkauni\Planogrid\Contracts;

use Alkauni\Planogrid\DTO\PlanogramEvaluation;
use Alkauni\Planogrid\DTO\PlanogramGridResult;
use Alkauni\Planogrid\DTO\PlanogramTemplate;

interface PlanogramMatcherInterface
{
    /**
     * Compare detected planogram grid result against expected planogram template.
     */
    public function match(
        PlanogramGridResult $gridResult,
        PlanogramTemplate $template,
        float $thresholdScore = 100.0
    ): PlanogramEvaluation;
}
