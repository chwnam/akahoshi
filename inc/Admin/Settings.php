<?php

namespace Chwnam\Akahoshi\Admin;

use Chwnam\Akahoshi\Scrap\Scraper;

class Settings
{
    public static function register(): void
    {
        register_setting(
            'akahoshi',
            'akahoshi_settings',
            [
                'type'              => 'array',
                'group'             => 'akahoshi_settings',
                'sanitize_callback' => [__CLASS__, 'sanitizeSettings'],
                'show_in_rest'      => false,
                'default'           => [
                    'nihongo' => [
                        ... self::getDefaultValues(),
                        'id'    => 'nihongo',
                        'label' => '일본어',
                    ],
                    'health'  => [
                        ... self::getDefaultValues(),
                        'id'    => 'health',
                        'label' => '건강',
                    ],
                ],
            ]
        );
    }

    public static function getDefaultValues(): array
    {
        return [
            'id'          => '',
            'enable'      => false,
            'keywords'    => '',
            'label'       => '',
            'term_id'     => 0,
            'user_id'     => 0,
            'notify'      => '',
            'notify_at'   => -1,
            'count_limit' => 0,
            'crawling'    => false,
        ];
    }

    public static function sanitize(array $value): array
    {
        $sanitized = [];
        $default   = self::getDefaultValues();

        // ID
        $sanitized['id'] = sanitize_key($value['id'] ?? $default['id']);
        // Label
        $sanitized['label'] = sanitize_text_field($value['label'] ?? $default['label']);
        // Enable
        $sanitized['enable'] = 'yes' === ($value['enable'] ?? '') ? 'yes' : $default['enable'];
        // Keywords
        $sanitized['keywords'] = sanitize_text_field($value['keywords'] ?? $default['keywords']);
        // Term ID
        $sanitized['term_id'] = absint($value['term_id'] ?? $default['term_id']);
        // User ID
        $sanitized['user_id'] = absint($value['user_id'] ?? $default['user_id']);
        // Notify
        $sanitized['notify'] = sanitize_email($value['notify'] ?? $default['notify']);
        // Notify At
        $sanitized['notify_at'] = intval($value['notify_at'] ?? $default['notify_at']);
        // Count Limit
        $sanitized['count_limit'] = absint($value['count_limit'] ?? $default['count_limit']);
        // Crawling
        $sanitized['crawling'] = filter_var($value['crawling'] ?? $default['crawling'], FILTER_VALIDATE_BOOLEAN);

        return $sanitized;
    }

    public static function sanitizeSettings(mixed $value): array
    {
        $targets   = Scraper::getScrapTargets();
        $default   = self::getDefaultValues();
        $sanitized = [];

        foreach ($targets as $t) {
            $sanitized[$t->id] = [
                ...$default,
                'id'    => $t->id,
                'label' => $t->label,
            ];
        }

        if (!is_array($value)) {
            return $sanitized;
        }

        foreach ($targets as $t) {
            if (isset($value[$t->id])) {
                $sanitized[$t->id] = Settings::sanitize($value[$t->id]);
            }
        }

        return $sanitized;
    }
}