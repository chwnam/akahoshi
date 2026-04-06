<?php

namespace Chwnam\Akahoshi;

use Chwnam\Akahoshi\Admin\Admin;
use Chwnam\Akahoshi\Admin\Settings;
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
        add_action('akahoshi_notify', [$this, 'notify']);
        add_action('akahoshi_limit', [$this, 'limit']);

        add_action('wp_head', [$this, 'outputHeadScripts']);
        add_filter('jetpack_photon_skip_for_url', [$this, 'filterJetpackPhoton'], 10, 4);

        $this->admin = new Admin();
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
}
