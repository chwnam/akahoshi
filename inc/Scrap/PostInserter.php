<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;
use stdClass;
use WP_Query;

use function Chwnam\Akahoshi\convertRssDate;
use function Chwnam\Akahoshi\modifyArticleContent;
use function Chwnam\Akahoshi\linkToSlug;

class PostInserter
{
    /**
     * @param Article[]   $items
     * @param ScrapTarget $target
     *
     * @return Article[]
     */
    public function insert(array $items, ScrapTarget $target): array
    {
        $output = [];

        // array of guid => slug
        $mappings = array_combine(
            array_map(fn(Article $item): string => $item->link, $items),
            array_map(fn(Article $item) => linkToSlug($item->link), $items),
        );

        // post.slug => post.ID
        $inserted = $this->getAlreadyInsertedItems($items);

        foreach ($items as $item) {
            $slug = $mappings[$item->link];

            if (isset($inserted[$slug])) {
                continue;
            }

            $p = wp_insert_post(
                [
                    'post_author'  => $target->userId,
                    'post_date'    => convertRssDate($item->datetime),
                    'post_title'   => $item->title,
                    'post_content' => wp_kses_post(modifyArticleContent($item->content ?: $item->description)) .
                        PHP_EOL .
                        '<div class="akahoshi-guid"><p>' .
                        '<a href="' . esc_url($item->link) .
                        '" class="akahoshi-external-link" target="blank" rel="external nofollow noreferrer">' .
                        '원본 기사 보기' .
                        '</a></div>',
                    'post_name'    => $slug,
                    'post_type'    => $target->postType,
                    'post_status'  => 'private',
                    'meta_input'   => ['akahoshi_scrap' => '1'],
                ]
            );

            if (is_wp_error($p)) {
                wp_die($p);
            }

            if ($target->termId > 0) {
                wp_set_object_terms($p, $target->termId, 'category');
            }

            $output[] = $item;
        }

        return $output;
    }

    /**
     * @param array $items
     *
     * @return array<string, stdClass>
     */
    public function getAlreadyInsertedItems(array $items): array
    {
        global $wpdb;

        if (empty($items)) {
            return [];
        }

        // placeholder
        $ph = implode(',', array_pad([], count($items), '%s'));

        // slugs
        $slugs = array_map(fn(Article $item) => linkToSlug($item->link), $items);

        // Query and get status
        $status = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_name, ID FROM $wpdb->posts " .
                "WHERE post_type='post' AND post_status='publish' AND post_name IN ($ph)",
                $slugs
            ),
            OBJECT_K
        );

        return $status ?: [];
    }

    public static function purge(): void
    {
        $query = new WP_Query(
            [
                'fields'           => 'ids',
                'no_found_rows'    => true,
                'nopaging'         => true,
                'post_type'        => 'post',
                'posts_per_page'   => -1,
                'suppress_filters' => true,
                'meta_key'         => 'akahoshi_scrap',
                'meta_value'       => '1',
            ]
        );

        foreach ($query->posts as $id) {
            wp_delete_post($id, true);
        }
    }
}
