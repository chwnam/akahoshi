<?php

use function Chwnam\Akahoshi\convertRssDate;
use function Chwnam\Akahoshi\getPostByLink;
use function Chwnam\Akahoshi\linkToSlug;
use function Chwnam\Akahoshi\removeImageDimension;
use function Chwnam\Akahoshi\trimAuthors;

class TestUtils extends WP_UnitTestCase
{
    public function test_convertRssDate(): void
    {
        $input    = 'Fri, 05 Dec 2025 18:00:00 +0000';
        $expected = '2025-12-06 03:00:00';
        $timezone = 'Asia/Seoul';
        $this->assertEquals($expected, convertRssDate($input, $timezone));

        $input    = '2026-02-11T15:04:01+09:00';
        $expected = '2026-02-11 06:04:01';
        $timezone = 'UTC';
        $this->assertEquals($expected, convertRssDate($input, $timezone));
    }

    public function test_guidToSlug(): void
    {
        $input    = 'https://www.chosun.com/national/people/2025/12/06/ZWMXPNAVIBBC7L6WTBBZK65KJA/';
        $expected = 'national-people-2025-12-06-zwmxpnavibbc7l6wtbbzk65kja';

        $this->assertEquals($expected, linkToSlug($input));
    }

    public function test_getPostByGuid(): void
    {
        $guid = 'https://www.chosun.com/national/people/2025/12/06/ZWMXPNAVIBBC7L6WTBBZK65KJA/';
        $slug = linkToSlug($guid);

        $expected = $this->factory()->post->create_and_get(['post_name' => $slug]);
        $actual   = getPostByLink($guid);

        $this->assertEquals($expected->ID, $actual);
    }

    public function test_removeImageDimension(): void
    {
        $input    = '<img src="https://testurl.com/" alt="" height="1449" width="650" />';
        $expected = '<img src="https://testurl.com/" alt="" />';

        $this->assertEquals($expected, removeImageDimension($input));
    }

    public function test_trimAuthors(): void
    {
        $input    = '오상훈 기자 ( osh@chosun.com   ),이윤주 인턴기자(     )';
        $expected = '오상훈 기자 (osh@chosun.com), 이윤주 인턴기자 ()';

        $this->assertEquals($expected, trimAuthors($input));
    }
}
