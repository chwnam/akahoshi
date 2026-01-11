<?php

use function Chwnam\Akahoshi\convertPubDate;
use function Chwnam\Akahoshi\getPostByGuid;
use function Chwnam\Akahoshi\guidToSlug;

class TestUtils extends WP_UnitTestCase
{
    public function test_convertPubDate(): void
    {
        $input    = 'Fri, 05 Dec 2025 18:00:00 +0000';
        $expected = '2025-12-06 03:00:00';
        $timezone = 'Asia/Seoul';

        $this->assertEquals($expected, convertPubDate($input, $timezone));
    }

    public function test_guidToSlug(): void
    {
        $input    = 'https://www.chosun.com/national/people/2025/12/06/ZWMXPNAVIBBC7L6WTBBZK65KJA/';
        $expected = 'national-people-2025-12-06-zwmxpnavibbc7l6wtbbzk65kja';

        $this->assertEquals($expected, guidToSlug($input));
    }

    public function test_getPostByGuid(): void
    {
        $guid = 'https://www.chosun.com/national/people/2025/12/06/ZWMXPNAVIBBC7L6WTBBZK65KJA/';
        $slug = guidToSlug($guid);

        $expected = $this->factory()->post->create_and_get(['post_name' => $slug]);
        $actual   = getPostByGuid($guid);

        $this->assertEquals($expected->ID, $actual);
    }
}
