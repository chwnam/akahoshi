<?php

namespace Chwnam\Akahoshi;

use Chwnam\Akahoshi\Admin\Admin;
use Chwnam\Akahoshi\Scrap\Scraper;

class Akahoshi
{
    public Admin $admin;

    public function __construct()
    {
        register_activation_hook(AKAHOSHI_MAIN, [$this, 'activation']);
        register_deactivation_hook(AKAHOSHI_MAIN, [$this, 'deactivation']);

        add_action('init', [$this, 'init']);
        add_action('akahoshi_scrap', [$this, 'scrap']);

        $this->admin = new Admin();
    }

    public function activation(): void
    {
        if (!wp_next_scheduled('akahoshi_scrap')) {
            wp_schedule_event(time(), 'hourly', 'akahoshi_scrap');
        }
    }

    public function deactivation(): void
    {
        wp_unschedule_hook('akahoshi_scrap');
    }

    public function init(): void
    {
        register_setting(
            'akahoshi',
            'akahoshi_settings',
            [
                'type'              => 'array',
                'group'             => 'akahoshi_settings',
                'sanitize_callback' => [$this, 'sanitizeSettings'],
                'show_in_rest'      => false,
                'default'           => [
                    'nihongo' => [
                        'id'       => 'nihongo',
                        'enable'   => false,
                        'keywords' => '',
                        'term_id'  => 0,
                        'notify'   => '',
                    ],
                    'health'  => [
                        'id'       => 'health',
                        'enable'   => false,
                        'keywords' => '',
                        'term_id'  => 0,
                        'notify'   => '',
                    ],
                ],
            ]
        );
    }

    public function sanitizeSettings(array $value): array
    {
        return [
            'nihongo' => [
                'id'       => 'nihongo',
                'enable'   => 'yes' === ($value['nihongo']['enable'] ?? '') ? 'yes' : 'no',
                'keywords' => sanitize_text_field($value['nihongo']['keywords']),
                'term_id'  => absint($value['nihongo']['term_id'] ?? '0'),
                'notify'   => sanitize_email($value['nihongo']['notify'] ?? ''),
            ],
            'health'  => [
                'id'       => 'health',
                'enable'   => 'yes' === ($value['health']['enable'] ?? '') ? 'yes' : 'no',
                'keywords' => sanitize_text_field($value['health']['keywords']),
                'term_id'  => absint($value['health']['term_id'] ?? '0'),
                'notify'   => sanitize_email($value['health']['notify'] ?? ''),
            ],
        ];
    }

    public function scrap(): void
    {
        (new Scraper())->scrap();
        // Test email
        wp_mail(
            to: get_bloginfo('admin_email'),
            subject: 'Akahoshi v1.0.1 시험 메일',
            message: wp_date(get_option('date_format') . ' ' . get_option('time_format')) . '에 실행됨',
        );
    }
}
