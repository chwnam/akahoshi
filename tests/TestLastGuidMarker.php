<?php

namespace Chwnam\Akahoshi\Tests;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Scrap\LastGuidMarker;
use WP_UnitTestCase;

class TestLastGuidMarker extends WP_UnitTestCase
{
    public function test_get_set(): void
    {
        LastGuidMarker::destroy();

        $this->assertEquals('', LastGuidMarker::get('key'));

        LastGuidMarker::set('key', 'test_value');

        $this->assertEquals('test_value', LastGuidMarker::get('key'));
    }

    public function test_destroy(): void
    {
        LastGuidMarker::destroy();

        $this->assertFalse(get_option('akahoshi_last_guid'));
    }
}
