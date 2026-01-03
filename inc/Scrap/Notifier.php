<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;
use WP_Term;

use function Chwnam\Akahoshi\template;

class Notifier
{
    private string $scrapTitle;
    private string $archiveUrl;

    /**
     * @param Article[]   $items
     * @param ScrapTarget $target
     */
    public function __construct(
        private readonly array $items,
        private readonly ScrapTarget $target
    ) {
        $this->scrapTitle = '스크랩 대상';
        if ($this->target->termId) {
            $term = get_term($this->target->termId, 'category');
            if ($term instanceof WP_Term) {
                $this->scrapTitle = $term->name;
                $this->archiveUrl = get_term_link($term);
            }
        }
    }

    public function notify(): void
    {
        if (empty($this->items) || empty($this->target->notify)) {
            return;
        }

        $to      = $this->target->notify;
        $subject = $this->createSubject();
        $body    = $this->createBody();
        $func    = fn() => 'text/html';

        add_filter('wp_mail_content_type', $func);
        wp_mail($to, $subject, $body);
        remove_filter('wp_mail_content_type', $func);
    }

    private function createSubject(): string
    {
        return sprintf("[%s/아카호시] %s 새 기사 스크랩", get_bloginfo('name'), $this->scrapTitle);
    }

    private function createBody(): string
    {
        $items = [];

        foreach ($this->items as $item) {
            $items[] = [
                'url'   => $item->link,
                'title' => $item->title,
            ];
        }

        return template(
            'email-tmpl.php',
            [
                'head_title'    => '아카호시 기사 스크랩 이메일',
                'blog_name'     => get_bloginfo('name'),
                'field_name'    => $this->target->label,
                'article_count' => count($items),
                'archive_url'   => get_term_link($this->target->termId, 'category'),
                'items'         => $items,
            ],
            true
        );
    }
}
