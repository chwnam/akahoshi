<?php

namespace Chwnam\Akahoshi;

function getAkahoshi(): Akahoshi
{
    static $akahoshi = null;

    if (is_null($akahoshi)) {
        $akahoshi = new Akahoshi();
    }

    return $akahoshi;
}

function convertPubDate(string $input, string $timezone = ''): string
{
    // e.g. Thu, 27 Nov 2025 18:00:00 +0000
    $datetime = date_create_immutable_from_format('D, d M Y H:i:s O', $input);

    if (!$datetime) {
        return '';
    }

    $tz = null;

    if ($timezone) {
        $tz = timezone_open($timezone);
    }

    if (!$tz) {
        $tz = wp_timezone();
    }

    return $datetime->setTimezone($tz)->format('Y-m-d H:i:s');
}

function getRssUrl(string $directory): string
{
    return "https://www.chosun.com/arc/outboundfeeds/rss/category/$directory/?outputType=xml";
}

function getSectionUrl(string $section): string
{
    return match ($section) {
        'health'  => 'https://www.chosun.com/medical/healthchosun/',
        'nihongo' => 'https://www.chosun.com/national/nie/japanese/',
    };
}

function guidToSlug(string $guid): string
{
    if (str_starts_with($guid, 'https://www.chosun.com/')) {
        $guid = untrailingslashit(substr($guid, strlen('https://www.chosun.com/')));
    }
    return strtolower(str_replace('/', '-', $guid));
}

/**
 * The style tag may be added automatically, by a batch-job.
 *
 * @param string $input
 *
 * @return string
 */
function removeCommonLocalStyle(string $input): string
{
    return trim(
        str_replace(
            '<style>.pre_wrap{ display: flex; flex-wrap: wrap; } .pre_img {  width: 100%; } .pre_img { display: block !important; } .pre_img_mobile { display: none !important;} @media only screen and (max-width: 689px) { .pre_img_pc {display: none !important;} .pre_img_mobile { display: block !important;} } </style>',
            '',
            $input
        )
    );
}

function template(string $template, array $context = [], bool $return = false): string
{
    $output = '';
    $path   = dirname(AKAHOSHI_MAIN) . '/inc/templates/' . $template;

    if (file_exists($path) && is_readable($path)) {
        if ($return) {
            ob_start();
        }

        (function (string $___akaTmplPath__, array $__akaTmplCtx__) {
            if ($__akaTmplCtx__) {
                extract($__akaTmplCtx__, EXTR_SKIP);
            }
            include $___akaTmplPath__;
        })($path, $context);

        if ($return) {
            $output = ob_get_clean();
        }
    }

    return $output;
}
