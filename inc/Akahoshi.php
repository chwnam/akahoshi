<?php

namespace Chwnam\Akahoshi;

use Chwnam\Akahoshi\Admin\Admin;
use Chwnam\Akahoshi\Admin\Settings;
use Chwnam\Akahoshi\Scrap\Scraper;
use Exception;
use WP_CLI;

class Akahoshi
{
    public Admin $admin;

    public function __construct()
    {
        register_activation_hook(AKAHOSHI_MAIN, [$this, 'activation']);
        register_deactivation_hook(AKAHOSHI_MAIN, [$this, 'deactivation']);

        add_action('init', [$this, 'init']);
        add_action('akahoshi_scrap', [$this, 'scrap']);
        add_action('akahoshi_notify', [$this, 'notify']);
        add_action('akahoshi_limit', [$this, 'limit']);

        add_action('wp_head', [$this, 'outputHeadScripts']);
        add_filter('jetpack_photon_skip_for_url', [$this, 'filterJetpackPhoton'], 10, 4);

        // 삭제 링크
        add_filter('the_content', [$this, 'appendTrashLink']);

        $this->admin = new Admin();

        if (defined('WP_CLI') && WP_CLI) {
            try {
                WP_CLI::add_command('akahoshi', AkahoshiCLI::class);
            } catch (Exception $e) {
                wp_die($e->getMessage());
            }
        }
    }

    public function activation(): void
    {
        if (!wp_next_scheduled('akahoshi_scrap')) {
            wp_schedule_event(time(), 'hourly', 'akahoshi_scrap');
        }

        if (!wp_next_scheduled('akahoshi_notify')) {
            wp_schedule_event(getNextHour(), 'hourly', 'akahoshi_notify');
        }

        if (!wp_next_scheduled('akahoshi_limit')) {
            wp_schedule_event(getNextHour(), 'weekly', 'akahoshi_limit');
        }
    }

    public function deactivation(): void
    {
        wp_unschedule_hook('akahoshi_scrap');
        wp_unschedule_hook('akahoshi_notify');
        wp_unschedule_hook('akahoshi_limit');
    }

    /**
     * Disable Jetpack image cache for chosun.com external images
     *
     * @param bool   $value
     * @param string $url
     *
     * @return bool
     */
    public function filterJetpackPhoton(bool $value, string $url): bool
    {
        if (str_starts_with($url, 'https://www.chosun.com/')) {
            $value = true;
        }

        return $value;
    }

    public function init(): void
    {
        Settings::register();
    }

    public function outputHeadScripts(): void
    {
        template('head-script.php');
    }

    public function scrap(): void
    {
        (new Scraper())->scrap();
    }

    /**
     * @param bool $forced
     *
     * @return void
     */
    public function notify(bool $forced = false): void
    {
        (new Scraper())->notifyQueued($forced);
    }

    public function limit(): void
    {
        (new Scraper())->limitPosts();
    }

    /**
     * 본문에 기사 삭제 링크를 동적으로 추가한다
     */
    public function appendTrashLink(string $content): string
    {
        $id = get_the_ID();

        if ($id && '1' == get_post_meta($id, '_akahoshi_scrap', true)) {
            $url = get_delete_post_link($id);
            if ($url) {
                $content .= PHP_EOL .
                    '<p class="akahoshi-delete-link"><a href="' . esc_url($url) .
                    '" onclick="return confirm(\'정말로 삭제하시겠습니가\')">기사 삭제하기</a></p>';
            }
        }

        return $content;
    }
}
