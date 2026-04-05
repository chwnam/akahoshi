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
            if ( ! $t->enable) {
                return;
            }

            $items    = $this->fetchArticles($t);
            $limited  = $this->limitRecentArticles($items, $t);
            $filtered = $this->filterArticlesByKeywords($limited, $t);
            $inserted = $this->addToPosts($filtered, $t);

            if (-1 === $t->notifyAt) {
                $this->notify($inserted, $t);
            } else {
                $this->addToQueue($inserted, $t);
            }
        }
    }

    public function notifyQueued(): void
    {
        foreach ($this->getScrapTargets() as $t) {
            (new MailQueue($t))->send();
        }
    }

    public function limitPosts(): void
    {
        foreach ($this->getScrapTargets() as $t) {
            (new CountLimit($t))->limitPosts();
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
     * @param Article[] $items
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
     * Returns a new array of articles, filtered by keywords in the target.
     * Target should have at least one keyword, otherwise it will return only empty array.
     * Article that matches at least one keyword can pass the filter.
     *
     * @param Article[] $items
     * @param ScrapTarget $target
     *
     * @return Article[]
     */
    private function filterArticlesByKeywords(array $items, ScrapTarget $target): array
    {
        return (new KeywordArticleFilter())->filter($items, $target);
    }

    /**
     * @param Article[] $items
     * @param ScrapTarget $target
     *
     * @return Article[]
     */
    private function addToPosts(array $items, ScrapTarget $target): array
    {
        return (new PostInserter())->insert($items, $target);
    }

    /**
     * @param Article[] $items
     * @param ScrapTarget $target
     *
     * @return void
     */
    private function notify(array $items, ScrapTarget $target): void
    {
        (new Notifier($items, $target))->notify();
    }

    /**
     * @param Article[] $items
     * @param ScrapTarget $target
     */
    private function addToQueue(array $items, ScrapTarget $target): void
    {
        (new MailQueue($target))->queue($items);
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
                    'id'          => 'nihongo',
                    'enable'      => $value['nihongo']['enable'] ?? 'no',
                    'label'       => '일본어',
                    'url'         => getRssUrl('nihongo'),
                    'keywords'    => $value['nihongo']['keywords'] ?? '',
                    'term_id'     => (int)($value['nihongo']['term_id'] ?? '0'),
                    'user_id'     => (int)($value['nihongo']['user_id'] ?? '0'),
                    'notify'      => $value['nihongo']['notify'] ?? '',
                    'notify_at'   => $value['nihongo']['notify_at'] ?? '-1',
                    'count_limit' => $value['nihongo']['count_limit'] ?? '0',
                ]
            ),
            new ScrapTarget(
                [
                    'id'          => 'health',
                    'enable'      => $value['health']['enable'] ?? 'no',
                    'label'       => '건강',
                    'url'         => getRssUrl('health'),
                    'keywords'    => $value['health']['keywords'] ?? '',
                    'term_id'     => (int)($value['health']['term_id'] ?? '0'),
                    'user_id'     => (int)($value['health']['user_id'] ?? '0'),
                    'notify'      => $value['health']['notify'] ?? '',
                    'notify_at'   => $value['health']['notify_at'] ?? '-1',
                    'count_limit' => $value['health']['count_limit'] ?? '0',
                ]
            ),
        ];
    }
}
