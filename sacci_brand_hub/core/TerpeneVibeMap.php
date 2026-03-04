<?php

namespace Core;

/**
 * Maps a dominant terpene name to a consumer mood tag.
 *
 * Called whenever terp_1_name is written to a batch record.
 */
class TerpeneVibeMap
{
    private const MAP = [
        'myrcene'       => 'Couchlock',
        'limonene'      => 'Energetic',
        'caryophyllene' => 'Calm',
        'linalool'      => 'Relaxed',
        'pinene'        => 'Alert',
        'terpinolene'   => 'Creative',
        'ocimene'       => 'Uplifting',
        'humulene'      => 'Focused',
    ];

    public static function tag(string $terpName): string
    {
        $key = strtolower(trim($terpName));
        return self::MAP[$key] ?? 'Balanced';
    }
}
