<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;

use function Chwnam\Akahoshi\getRssUrl;

class Scraper
{
    public function scrap(): void
    {
        foreach ($this->getScrapTargets() as $t) {
            if (!$t->enable) {
                return;
            }

            $items    = $this->fetchArticles($t);
            $limited  = $this->limitRecentArticles($items, $t);
            $filtered = $this->filterArticlesByKeywords($limited, $t);
            $inserted = $this->addToPosts($filtered, $t);

            $this->notify($inserted, $t);
        }
    }

    /**
     * @param ScrapTarget $target
     *
     * @return Article[]
     */
    private function fetchArticles(ScrapTarget $target): array
    {
        return (new RssFetcher())->fetch($target->url);
    }

    /**
     * Limit to only recent items.
     *
     * @param Article[]   $items
     * @param ScrapTarget $target
     *
     * @return array
     */
    private function limitRecentArticles(array $items, ScrapTarget $target): array
    {
        return (new RecentArticleFilter())->filter($items, $target);
    }

    /**
     * Filter items by keywords
     *
     * Returns new array of article, filtered by keywords in the target.
     * Target should have at least one keyword, otherwise it will return only empty array.
     * Article that matches at least one keyword can pass the filter.
     *
     * @param Article[]   $items
     * @param ScrapTarget $target
     *
     * @return Article[]
     */
    private function filterArticlesByKeywords(array $items, ScrapTarget $target): array
    {
        return (new KeywordArticleFilter())->filter($items, $target);
    }

    /**
     * @param Article[]   $items
     * @param ScrapTarget $target
     *
     * @return Article[]
     */
    private function addToPosts(array $items, ScrapTarget $target): array
    {
        return (new PostInserter())->insert($items, $target);
    }

    private function notify(array $items, ScrapTarget $target): void
    {
        (new Notifier($items, $target))->notify();
    }

    /**
     * @return ScrapTarget[]
     */
    public static function getScrapTargets(): array
    {
        $value = get_option('akahoshi_settings');

        return [
            new ScrapTarget(
                [
                    'id'       => 'nihongo',
                    'enable'   => $value['nihongo']['enable'] ?? 'no',
                    'label'    => '일본어',
                    'url'      => getRssUrl('national'),
                    'keywords' => $value['nihongo']['keywords'] ?? '',
                    'term_id'  => (int)($value['nihongo']['term_id'] ?? '0'),
                    'notify'   => $value['nihongo']['notify'] ?? '',
                ]
            ),
            new ScrapTarget(
                [
                    'id'       => 'health',
                    'enable'   => $value['health']['enable'] ?? 'no',
                    'label'    => '건강',
                    'url'      => getRssUrl('medical'),
                    'keywords' => $value['health']['keywords'] ?? '',
                    'term_id'  => (int)($value['health']['term_id'] ?? '0'),
                    'notify'   => $value['health']['notify'] ?? '',
                ]
            ),
        ];
    }
}
