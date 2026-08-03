<?php
namespace App\Support;

final class Avatar {
    private const PALETTE = [
        '#3457D5', '#0F9B8E', '#1E8E5A', '#C23B5D', '#5B4B8A', '#8B5FBF', '#D97B29',
        '#C2447A', '#264653', '#457B9D', '#4A4E69', '#A3294A', '#2A6F62', '#8A5A44',
        
    ];

    public static function color(string $seed): string {
        return self::PALETTE[
            crc32($seed) % count(self::PALETTE)
        ];
    }
}