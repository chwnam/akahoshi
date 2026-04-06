<?php

namespace Chwnam\Akahoshi\Tests;

use DOMDocument;
use DOMXPath;
use WP_UnitTestCase;

class TestHealthCrawl extends WP_UnitTestCase
{
    public function test_crawl()
    {
        $html = file_get_contents(getTestsPath('res/health-chosun-sample-01.html'));
        $dom  = new DOMDocument();
        $dom->loadHTML($html, LIBXML_NOERROR);

        $xpath = new DOMXPath($dom);

        $nodes  = $xpath->query("//div[@id='news_body_id']/div[@class='par']/div");
        $output = "";

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
        }

        $this->expectNotToPerformAssertions();
    }
}