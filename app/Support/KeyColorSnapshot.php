<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\KeyColor;
use App\Models\KeyColorTranslation;
use App\Models\Order;
use App\Models\OrderLine;

/**
 * Persists localized key color labels on order lines at checkout time.
 */
final class KeyColorSnapshot
{
    public static function freezeOnOrderLines(Order $order): void
    {
        $order->loadMissing(['lines.keyColor.translations']);

        foreach ($order->lines as $line) {
            self::freezeLine($line);
        }
    }

    public static function freezeLine(OrderLine $line): void
    {
        if (! $line->key_color_id) {
            if ($line->key_color_rgb !== null || $line->key_color_name !== null) {
                $line->forceFill([
                    'key_color_rgb' => null,
                    'key_color_name' => null,
                ])->saveQuietly();
            }

            return;
        }

        $color = $line->relationLoaded('keyColor')
            ? $line->keyColor
            : KeyColor::query()->with('translations')->find($line->key_color_id);

        if (! $color) {
            return;
        }

        $line->forceFill([
            'key_color_rgb' => $color->rgb_code,
            'key_color_name' => $color->translatedName(),
        ])->saveQuietly();
    }
}
