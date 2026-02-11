<?php

namespace Chwnam\Akahoshi\Tests;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Scrap\RssFetcher;
use WP_UnitTestCase;

class TestRssFetcher extends WP_UnitTestCase
{
    public function test_fetch_01(): void
    {
        $items = (new RssFetcher())->fetch(getTestsPath('res/rss-sample-01.xml'));

        $this->assertCount(5, $items);

        $item = $items[0];

        $this->assertInstanceOf(Article::class, $item);
        $this->assertEquals('매일 16㎞ 자전거, 주말엔 테니스… 92세 괴력의 비결', $item->title);
        $this->assertEquals('https://www.chosun.com/medical/2025/10/17/4X64VQPLOVD4PJS6GGKO4VKWPY/', $item->link);
        $this->assertEquals('김포=이태동 기자', $item->author);
        $this->assertEquals('TestDescription', $item->description);
        $this->assertEquals('Thu, 16 Oct 2025 18:00:00 +0000', $item->datetime);
        $this->assertStringContainsString("‘수퍼스트롱' 기사 보기", $item->content);

        $this->assertEquals(
            'https://www.chosun.com/resizer/v2/NW3WSOOODFF6BOOVDYXIFXLYYM.gif?auth=13418ceaadbe7347544509b8f86a21105748852ed42ae15565f699bce205415f&smart=true&width=1200&height=900',
            $item->media->url
        );
        $this->assertEquals('image/jpeg', $item->media->type);
        $this->assertEquals(1200, $item->media->width);
        $this->assertEquals(900, $item->media->height);
        $this->assertEquals('TEST_DESCRIPTION', $item->media->description);
    }

    public function test_fetch_02(): void
    {
        // sample-02.xml
        // I made a little change to the original RSS feed, so that the feed has a duplicated item on purpose.
        // My fetcher should filter out the duplication.
        $items = (new RssFetcher())->fetch(getTestsPath('res/rss-sample-02.xml'));

        $this->assertCount(3, $items); // Should be 3, not 4.

        // Everything else is the same as sample-01.xml because it has the same source.
    }

    public function test_fetch_03(): void
    {
        // sample-03.xml
        // This is a new RSS feed since 2026. This feed has a little different structure than the old one.
        $items = (new RssFetcher())->fetch(getTestsPath('res/rss-sample-03.xml'));

        $this->assertCount(3, $items);

        $item = $items[0];

        $this->assertInstanceOf(Article::class, $item);
        $this->assertEquals('AI가 줄글 의료기록을 표준 데이터로… 관상동맥조영술 자동 분석 길 열렸다', $item->title);
        $this->assertEquals('https://health.chosun.com/site/data/html_dir/2026/02/11/2026021103073.html', $item->link);
        $this->assertEquals('신소영 기자 (ssy@chosun.com)', $item->author);
        $this->assertEquals('대부분 비정형적인 서술 방식으로 작성돼 대규모 연구나 정책 분석에 활...', $item->description);
        $this->assertEquals('2026-02-11T15:05:43+09:00', $item->datetime);
        $this->assertStringContainsString("비정형적인 서술 방식으로 작성돼", $item->content);
    }
}
