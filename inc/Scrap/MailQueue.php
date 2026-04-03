<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;

class MailQueue
{
    private ScrapTarget $target;
    private string $name;

    public function __construct(ScrapTarget $target)
    {
        $this->target = $target;
        $this->name   = 'akahoshi_mail_queue_' . $target->id;
    }

    /**
     * @param Article[] $items
     */
    public function queue(array $items): void
    {
        $queued = get_transient($this->name);

        if (false === $queued) {
            $queued = [];
        }

        foreach ($items as $item) {
            $queued[] = (array)$item;
        }

        set_transient($this->name, $queued);
    }

    public function send(): void
    {
        $hour        = $this->target->notifyAt;
        $currentHour = (int)date('H', wp_timezone());
        $queued      = get_transient($this->name);

        if ( ! $this->target->enable || $currentHour !== $hour || empty($queued)) {
            return;
        }

        $articles = [];

        foreach ($queued as $item) {
            $article = new Article();

            foreach ($item as $key => $value) {
                if (property_exists($this, $key)) {
                    $article->{$key} = $value;
                }
                $articles[] = $article;
            }
        }

        $articles = array_reverse($articles);

        (new Notifier($articles, $this->target))->notify();

        set_transient($this->name, []);
    }
}
