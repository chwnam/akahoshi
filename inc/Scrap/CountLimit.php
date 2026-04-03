<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\ScrapTarget;
use DateInterval;
use Exception;

class CountLimit
{
    private bool $enable;

    private int $termId;

    private int $limit;

    public function __construct(ScrapTarget $target)
    {
        $this->enable = $target->enable;
        $this->termId = $target->termId;
        $this->limit  = $target->countLimit;
    }

    public function limitPosts(): void
    {
        if ($this->enable && $this->termId > 0 && $this->limit > 0) {
            foreach ($this->getOutdated($this->limit) as $postId) {
                wp_delete_post($postId, true);
            }
        }
    }

    /**
     * @param int $threshold Week
     *
     * @return array
     */
    public function getOutdated(int $threshold): array
    {
        global $wpdb;

        $output = [];

        if ( ! $threshold) {
            return $output;
        }

        try {
            $days     = $threshold * 7;
            $datetime = date_create('today midnight', wp_timezone())
                ->sub(new DateInterval("P{$days}D"))
                ->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return $output;
        }

        $query = $wpdb->prepare(
            "SELECT p.ID FROM `$wpdb->posts` p " .
            " INNER JOIN `$wpdb->term_relationships` tr ON tr.object_id = p.ID" .
            " INNER JOIN `$wpdb->term_taxonomy` tt ON tt.term_taxonomy_id = tr.term_taxonomy_id" .
            " INNER JOIN `$wpdb->terms` t ON t.term_id = tt.term_id" .
            " WHERE p.post_status='publish'" .
            " AND p.post_type='post'" .
            " AND p.comment_count=0" .
            " AND tt.taxonomy='category'" .
            " AND t.term_id=%d" . # 1: term_id
            " AND p.post_date < %s", #2 thresh
            $this->termId,
            $datetime
        );

        $results = $wpdb->get_col($query);
        if (is_array($results)) {
            $output = array_map('intval', $results);
        }

        return $output;
    }
}
