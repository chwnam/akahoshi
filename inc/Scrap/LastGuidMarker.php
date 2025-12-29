<?php

namespace Chwnam\Akahoshi\Scrap;

class LastGuidMarker
{
    public static function get(string $id): string
    {
        $stored = self::load();

        return $stored[$id] ?? '';
    }

    public static function set(string $id, string $guid): void
    {
        $stored      = self::load();
        $stored[$id] = $guid;

        self::store($stored);
    }

    public static function destroy(): void
    {
        delete_option('akahoshi_last_guid');
    }

    private static function load(): array
    {
        $values = get_option('akahoshi_last_guid');

        if (!is_array($values)) {
            $values = [];
        }

        return $values;
    }

    private static function store(array $values): void
    {
        update_option('akahoshi_last_guid', $values, false);
    }
}
