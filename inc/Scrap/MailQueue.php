<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;

use WP_Query;

use function Chwnam\Akahoshi\linkToSlug;

class MailQueue
{
    private ScrapTarget $target;
    private string      $name;

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
            $queued[] = $item;
        }

        set_transient($this->name, $queued);
    }

    /**
     * @return Article[]
     */
    public function getStatus(): array
    {
        return get_transient($this->name) ?: [];
    }

    /**
     * @param bool $forced  강제로 진행합니다. true 이면 '이메일 발송 시각'을 무시하고 큐에 있는 내용을 무조건 메일로 보냅니다.
     *                      단, 큐에 내용이 없는 경우는 메일이 보내지지 않습니다.
     *
     * @return void
     */
    public function send(bool $forced): void
    {
        if (!$this->target->enable) {
            return;
        }

        $hour        = $this->target->notifyAt;
        $currentHour = (int)wp_date('H', null, wp_timezone());

        if (!$forced && $currentHour !== $hour) {
            return;
        }

        $queued   = get_transient($this->name) ?: [];
        $articles = [];

        foreach ($queued as $item) {
            if ($item instanceof Article) {
                $articles[] = $item;
            }
        }

        // 실제 테이블에 있는 요소만 필터하여 메일 목록에 넣는다.
        // 메일이 발송되기 전에 너무 엉터릭 같은 기사는 미리 제거되어 있을 수도 있기 때문이다.
        $articles   = array_reverse($articles);
        $slugs      = array_map(fn(Article $a) => linkToSlug($a->link), $articles);
        $slugExists = [];
        if ($this->target->termId && $slugs) {
            $query = new WP_Query([
                'post_type'     => 'post',
                'post_status'   => 'private',
                'no_found_rows' => true,
                'nopaging'      => true,
                'post_name__in' => $slugs,
                'cat'           => $this->target->termId,
            ]);
            foreach ($query->posts as $post) {
                $found = in_array($post->post_name, $slugs);
                if ($found) {
                    $slugExists[] = $post->post_name;
                }
            }
        }
        if ($slugExists) {
            $articles = array_filter($articles, fn(Article $a) => in_array(linkToSlug($a->link), $slugExists));
        }

        (new Notifier($articles, $this->target))->notify();

        set_transient($this->name, []);
    }
}
