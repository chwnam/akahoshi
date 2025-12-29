<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;
use WP_Term;

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

        wp_mail($to, $subject, $body);
    }

    private function createSubject(): string
    {
        return sprintf("[%s/Akahoshi] %s 새 기사 스크랩", get_bloginfo('name'), $this->scrapTitle);
    }

    private function createBody(): string
    {
        return sprintf(
            "안녕하세요, %s입니다.\r\n" .
            "\r\n" .
            "'%s' 영역의 새 기사 %d개가 수집되었습니다.\r\n" .
            "자세한 내용은 블로그 {%s}에서 확인하세요.\r\n" .
            "\r\n%s\r\n" .
            "- 당신의 Akahoshi 플러그인이 드림.",
            get_bloginfo('name'),
            $this->scrapTitle,
            count($this->items),
            $this->archiveUrl,
            implode("\r\n", array_map(fn($item) => " \t- $item->title", $this->items))
        );
    }
}
