<?php

namespace Chwnam\Akahoshi\Scrap;

use DOMDocument;
use DOMXPath;

class ChosunHealthCrawler
{
    public static function crawl(string $url): string
    {
        $r = wp_remote_get($url);
        if (is_wp_error($r)) {
            return '';
        }

        $code = wp_remote_retrieve_response_code($r);
        if ($code !== 200) {
            return '';
        }

        $output = "";

        $dom  = new DOMDocument();
        $html = wp_remote_retrieve_body($r);
        $dom->loadHTML($html, LIBXML_NOERROR);

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query("//div[@id='news_body_id']/div[@class='par']/div");

        foreach ($nodes as $node) {
            if ('page' === $node->getAttribute('id')) {
                continue;
            }
            $output .= $node->ownerDocument->saveHTML($node) . PHP_EOL;
        }

        if ($output) {
            $output = html_entity_decode($output);
            $output = preg_replace('/\x{00a0}/u', ' ', $output);
            $output = preg_replace('/>\s+</', '><', $output);
            $output = preg_replace(';\s+</;', '><', $output);
            $output = trim($output);
        }

        return $output;
    }

    public static function escape(string $input): string
    {
        return wp_kses(
            $input,
            [
                'b'          => [],
                'br'         => [],
                'div'        => [
                    'class' => true,
                ],
                'figure'     => [],
                'figcaption' => [],
                'img'        => [
                    'alt'    => true,
                    'src'    => true,
                    'height' => true,
                    'width'  => true,
                ],
                'span'       => [
                    'class' => true,
                ],
            ]
        );
    }
}