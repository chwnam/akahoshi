<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;

class KeywordArticleFilter
{
    public function __construct()
    {
    }

    /**
     * @param Article[]   $items
     * @param ScrapTarget $target
     *
     * @return Article[]
     */
    public function filter(array $items, ScrapTarget $target): array
    {
        $callback = function (Article $item) use ($target): bool {
            return $item->title &&
                array_reduce(
                    array: $target->keywords,
                    callback: fn(bool $carry, string $keyword): bool => $carry ||
                        str_contains(strtolower($item->title), $keyword),
                    initial: false,
                );
        };

        return array_filter($items, $callback);
    }
}