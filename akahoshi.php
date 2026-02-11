<?php
/**
 * Plugin Name:  아카호시
 * Plugin URI:   https://github.com/chwnam/akahoshi
 * Version:      1.1.2
 * Description:  내 블로그를 위한 뉴스 스크랩 플러그인
 * Author:       chwnam
 * Author URI:   https://github.com/chwnam
 * License:      GPL v2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 8.2
 */

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('ABSPATH')) {
    exit;
}

const AKAHOSHI_MAIN    = __FILE__;
const AKAHOSHI_VERSION = '1.1.2';

Chwnam\Akahoshi\getAkahoshi();
