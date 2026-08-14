<?php

namespace Alkauni\Planogrid\DTO;

class PlanogramEvaluation
{
    public function __construct(
        public readonly float $complianceScore,
        public readonly string $status,
        public readonly int $matchedCount,
        public readonly int $totalExpected,
        public readonly PlanogramGridResult $gridResult,
        public readonly ?string $annotatedImage = null,
        public readonly array $matchStatuses = [],
        public readonly array $mismatches = []
    ) {}

    public function isCorrect(): bool
    {
        return $this->status === 'correct';
    }

    public function getComplianceScore(): float
    {
        return $this->complianceScore;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMatchedCount(): int
    {
        return $this->matchedCount;
    }

    public function getDetectedMatrix(): array
    {
        return $this->gridResult->toArray();
    }

    public function getAnnotatedImage(): ?string
    {
        return $this->annotatedImage;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'compliance_score' => round($this->complianceScore, 2),
            'matched_count' => $this->matchedCount,
            'total_expected' => $this->totalExpected,
            'grid_result' => $this->gridResult->toArray(),
            'mismatches' => $this->mismatches,
        ];
    }
}
