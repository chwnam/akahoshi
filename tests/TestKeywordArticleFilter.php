<?php

namespace Chwnam\Akahoshi\Tests;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;
use Chwnam\Akahoshi\Scrap\KeywordArticleFilter;
use WP_UnitTestCase;

class TestKeywordArticleFilter extends WP_UnitTestCase
{
    protected KeywordArticleFilter $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new KeywordArticleFilter();
    }

    /**
     * @param int         $expected
     * @param array       $items
     * @param ScrapTarget $target
     *
     * @return void
     *
     * @dataProvider provider
     */
    public function test_filter(int $expected, array $items, ScrapTarget $target): void
    {
        $filtered = $this->filter->filter($items, $target);

        $this->assertCount($expected, $filtered);
    }

    protected function provider(): array
    {
        $generator = function (string $title) {
            $article        = new Article();
            $article->title = $title;

            return $article;
        };

        return [
            '#1: empty keywords, no match' => [
                'expected' => 0,
                'items'    => [
                    $generator(title: 'Test article title'),
                    $generator(title: 'Fruits: apricot blackberry'),
                    $generator(title: 'Fruits: cranberry fig kiwi'),
                ],
                'target'   => new ScrapTarget(['id' => 'test', 'keywords' => '']),
            ],
            '#2: fruit keywords, no match' => [
                'expected' => 0,
                'items'    => [
                    $generator(title: 'Test article title'),
                    $generator(title: 'Fruits: apricot blackberry'),
                    $generator(title: 'Fruits: cranberry fig kiwi'),
                ],
                'target'   => new ScrapTarget(['id' => 'test', 'keywords' => 'apple, banana, citron']),
            ],
            '#2: fruit keywords, 1 match' => [
                'expected' => 1,
                'items'    => [
                    $generator(title: 'Test article title'),
                    $generator(title: 'Fruits: apricot blackberry'),
                    $generator(title: 'Fruits: cranberry fig *apple*'),
                ],
                'target'   => new ScrapTarget(['id' => 'test', 'keywords' => 'apple, banana, citron']),
            ],
            '#2: fruit keywords, 2 matches' => [
                'expected' => 2,
                'items'    => [
                    $generator(title: 'Test article title'),
                    $generator(title: 'Fruits: apricot banana blackberry citron'),
                    $generator(title: 'Fruits: cranberry fig kiwi Apple'),
                ],
                'target'   => new ScrapTarget(['id' => 'test', 'keywords' => 'apple, banana, citron']),
            ],
        ];
    }
}
