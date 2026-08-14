<?php

namespace Alkauni\Planogrid\Enums;

enum MatchStatus: string
{
    case MATCH = 'match';
    case MISMATCH = 'mismatch';
    case LOW_CONFIDENCE = 'low_confidence';
    case COMPETITOR = 'competitor';
    case UNMATCHED = 'unmatched';

    public function defaultHexColor(): string
    {
        return match ($this) {
            self::MATCH => '#00d400',          // Bright green
            self::MISMATCH => '#ff0000',       // Red
            self::LOW_CONFIDENCE => '#ffcc00', // Yellow/Amber
            self::COMPETITOR => '#ff9900',     // Orange
            self::UNMATCHED => '#888888',      // Gray
        };
    }
}
