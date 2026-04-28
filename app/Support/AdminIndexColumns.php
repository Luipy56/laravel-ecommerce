<?php

namespace App\Support;

/**
 * Normalizes admin index column preferences against the registry in config/admin_index_columns.php.
 */
final class AdminIndexColumns
{
    /**
     * @return array<string, list<string>>
     */
    public static function registry(): array
    {
        /** @var array<string, list<string>> $reg */
        $reg = config('admin_index_columns', []);

        return $reg;
    }

    /**
     * @param  array<string, mixed>|null  $stored  table_id => list of column ids
     * @return array<string, list<string>>
     */
    public static function normalize(?array $stored): array
    {
        $reg = self::registry();
        $out = [];
        foreach ($reg as $tableId => $allowed) {
            $row = is_array($stored) ? ($stored[$tableId] ?? null) : null;
            if (! is_array($row) || $row === []) {
                $out[$tableId] = $allowed;

                continue;
            }
            /** @var list<string> $filtered Preserve client order; drop unknown ids and duplicates. */
            $filtered = [];
            $seen = [];
            foreach ($row as $id) {
                if (! is_string($id) || ! in_array($id, $allowed, true) || isset($seen[$id])) {
                    continue;
                }
                $seen[$id] = true;
                $filtered[] = $id;
            }
            $out[$tableId] = $filtered !== [] ? $filtered : $allowed;
        }

        return $out;
    }
}
