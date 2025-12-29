<?php

namespace Chwnam\Akahoshi\Tests;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;
use Chwnam\Akahoshi\Scrap\LastGuidMarker;
use Chwnam\Akahoshi\Scrap\RecentArticleFilter;
use WP_UnitTestCase;

class TestRecentArticleFilter extends WP_UnitTestCase
{
    protected RecentArticleFilter $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new RecentArticleFilter();
    }

    /**
     * @param int         $expected
     * @param Article[]   $items
     * @param ScrapTarget $target
     * @param string      $lastGuid
     *
     * @return void
     *
     * @dataProvider provider
     */
    public function test_filter(int $expected, array $items, ScrapTarget $target, string $lastGuid): void
    {
        LastGuidMarker::set($target->id, $lastGuid);

        $filtered = $this->filter->filter($items, $target);

        $this->assertCount($expected, $filtered);
    }

    protected function provider(): array
    {
        $generator = function (string $guid) {
            $article        = new Article();
            $article->title = 'Test Article';
            $article->guid  = $guid;

            return $article;
        };

        $items = [
            $generator(guid: '1'),
            $generator(guid: '2'),
            $generator(guid: '3'),
        ];

        return [
            'Test #1: empty guid' => [
                'expected'  => 3,
                'items'     => $items,
                'target'    => new ScrapTarget(['id' => 'test']),
                'last_guid' => '',
            ],

            'Test #2: filter out all' => [
                'expected'  => 0,
                'items'     => $items,
                'target'    => new ScrapTarget(['id' => 'test']),
                'last_guid' => '1',
            ],

            'Test #3: get 1' => [
                'expected'  => 1,
                'items'     => $items,
                'target'    => new ScrapTarget(['id' => 'test']),
                'last_guid' => '2',
            ],

            'Test #4: get 1, 2' => [
                'expected'  => 2,
                'items'     => $items,
                'target'    => new ScrapTarget(['id' => 'test']),
                'last_guid' => '3',
            ],

            'Test #5: get 1, 2, 3' => [
                'expected'  => 3,
                'items'     => $items,
                'target'    => new ScrapTarget(['id' => 'test']),
                'last_guid' => '4',
            ],
        ];
    }
}