<?php

namespace Alkauni\Planogrid\Services;

use Alkauni\Planogrid\Contracts\PlanogramMatcherInterface;
use Alkauni\Planogrid\DTO\CustomLabelDetection;
use Alkauni\Planogrid\DTO\PlanogramEvaluation;
use Alkauni\Planogrid\DTO\PlanogramGridResult;
use Alkauni\Planogrid\DTO\PlanogramItem;
use Alkauni\Planogrid\DTO\PlanogramTemplate;
use Alkauni\Planogrid\Enums\MatchStatus;

class PlanogramMatcherService implements PlanogramMatcherInterface
{
    public function match(
        PlanogramGridResult $gridResult,
        PlanogramTemplate $template,
        float $thresholdScore = 100.0
    ): PlanogramEvaluation {
        $detectedMatrix = $gridResult->matrix;
        $expectedRows = $template->rows;

        $totalExpected = $template->totalItemCount();
        $matchedCount = 0;
        $mismatches = [];
        $matchStatuses = [];
        $flatDetectionsIndex = 0;

        foreach ($detectedMatrix as $rowIndex => $rowItems) {
            $expectedRow = $expectedRows[$rowIndex] ?? null;

            foreach ($rowItems as $colIndex => $detection) {
                /** @var CustomLabelDetection $detection */
                $expectedItem = $expectedRow?->items[$colIndex] ?? null;

                if ($expectedItem === null) {
                    $matchStatuses[$flatDetectionsIndex] = MatchStatus::UNMATCHED;
                    $mismatches[] = sprintf(
                        'Extra item detected at Row %d, Col %d: "%s"',
                        $rowIndex + 1,
                        $colIndex + 1,
                        $detection->name
                    );
                } elseif ($expectedItem->isCompetitor) {
                    $matchStatuses[$flatDetectionsIndex] = MatchStatus::COMPETITOR;
                    $mismatches[] = sprintf(
                        'Competitor detected at Row %d, Col %d: "%s"',
                        $rowIndex + 1,
                        $colIndex + 1,
                        $detection->name
                    );
                } elseif ($this->isItemMatch($detection->name, $expectedItem)) {
                    $matchedCount++;
                    $matchStatuses[$flatDetectionsIndex] = MatchStatus::MATCH;
                } else {
                    $matchStatuses[$flatDetectionsIndex] = MatchStatus::MISMATCH;
                    $mismatches[] = sprintf(
                        'Misplaced item at Row %d, Col %d: expected "%s", got "%s"',
                        $rowIndex + 1,
                        $colIndex + 1,
                        $expectedItem->name,
                        $detection->name
                    );
                }

                $flatDetectionsIndex++;
            }
        }

        // Check for missing items in expected template
        foreach ($expectedRows as $rowIndex => $row) {
            foreach ($row->items as $colIndex => $expectedItem) {
                $detectedItem = $detectedMatrix[$rowIndex][$colIndex] ?? null;
                if ($detectedItem === null && !$expectedItem->isCompetitor) {
                    $mismatches[] = sprintf(
                        'Missing item at Row %d, Col %d: expected "%s"',
                        $rowIndex + 1,
                        $colIndex + 1,
                        $expectedItem->name
                    );
                }
            }
        }

        $complianceScore = $totalExpected > 0
            ? ($matchedCount / $totalExpected) * 100.0
            : 0.0;

        $status = $complianceScore >= $thresholdScore ? 'correct' : 'incorrect';

        return new PlanogramEvaluation(
            complianceScore: $complianceScore,
            status: $status,
            matchedCount: $matchedCount,
            totalExpected: $totalExpected,
            gridResult: $gridResult,
            annotatedImage: null,
            matchStatuses: $matchStatuses,
            mismatches: $mismatches
        );
    }

    private function isItemMatch(string $detectedName, PlanogramItem $expectedItem): bool
    {
        $dName = strtolower(trim($detectedName));
        $eName = strtolower(trim($expectedItem->name));

        if ($dName === $eName) {
            return true;
        }

        if ($expectedItem->id && strtolower(trim($expectedItem->id)) === $dName) {
            return true;
        }

        if ($expectedItem->sku && strtolower(trim($expectedItem->sku)) === $dName) {
            return true;
        }

        return false;
    }
}
