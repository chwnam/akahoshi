<?php

namespace Chwnam\Akahoshi\Tests;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Scrap\RssFetcher;
use WP_UnitTestCase;

class TestRssFetcher extends WP_UnitTestCase
{
    public function test_fetch(): void
    {
        $items = (new RssFetcher())->fetch(getTestsPath('res/rss-sample-01.xml'));

        $this->assertCount(5, $items);

        $item = $items[0];

        $this->assertInstanceOf(Article::class, $item);
        $this->assertEquals('매일 16㎞ 자전거, 주말엔 테니스… 92세 괴력의 비결', $item->title);
        $this->assertEquals('https://www.chosun.com/medical/2025/10/17/4X64VQPLOVD4PJS6GGKO4VKWPY/', $item->link);
        $this->assertEquals('https://www.chosun.com/medical/2025/10/17/4X64VQPLOVD4PJS6GGKO4VKWPY/', $item->guid);
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
}
