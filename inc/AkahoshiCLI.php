<?php

namespace Chwnam\Akahoshi;

use Chwnam\Akahoshi\Object\ScrapTarget;
use Chwnam\Akahoshi\Scrap\Scraper;
use WP_CLI;
use WP_CLI\ExitException;
use WP_Query;

/**
 * 아카호시 임시 커맨드
 */
class AkahoshiCLI
{
    /**
     * 수집한 신문 기사 HTML에서 첫부분 기사 대표 이미지에 걸려 있는 width, height 속성을 지웁니다.
     *
     * ## EXAMPLES
     *
     *     wp akahoshi trim-article-img-dimension 2732
     *     wp akahoshi trim-article-img-dimension 2502 --dry-run
     *
     * ## OPTIONS
     *
     * [<IDs>...]
     * : 포스트 ID
     *
     * [--all]
     * : 모든 '건강' 포스트에 대해 진행합니다.
     *
     * [--dry-run]
     * : 실제로 변경하지는 않고, 변경 결과만 출력합니다.
     *
     * @param array $args
     * @param array $assoc_args
     *
     * @return void
     * @throws ExitException
     *
     * @subcommand trim-article-img-dimension
     */
    public function trimArticleImgDimension(array $args, array $assoc_args): void
    {
        $all    = $assoc_args['all'] ?? false;
        $dryRun = $assoc_args['dry-run'] ?? false;

        $target = array_filter(Scraper::getScrapTargets(), fn($t) => 'health' === $t->id);
        if (!$target) {
            WP_CLI::error("'health' target not found!");
        }

        $target = array_shift($target);
        if (!$target->termId) {
            WP_CLI::error("Category is not set!");
        }

        $queryArgs = [
            'post_type'     => 'post',
            'post_status'   => 'private',
            'nopaging'      => true,
            'no_found_rows' => true,
            'tax_query'     => [
                [
                    'taxonomy' => 'category',
                    'field'    => 'term_id',
                    'terms'    => $target->termId,
                ]
            ],
        ];

        if ($args) {
            $query = new WP_Query([
                ...$queryArgs,
                'post__in' => $args,
            ]);
        } elseif ($all) {
            $query = new WP_Query($queryArgs);
        } else {
            WP_CLI::error("What's wrong?");
        }

        foreach ($query->posts as $post) {
            $replaced = preg_replace(
                '/<div class="news_imgbox([ a-z0-9\-_]*)"><figure><img src="([^"]+)" alt="([^"]+)" width="\d+" height="\d+">/',
                '<div class="news_imgbox$1"><figure><img src="$2" alt="$3">',
                $post->post_content,
            );

            if ($replaced !== $post->post_content) {
                if ($dryRun) {
                    WP_CLI::log("Dry-run [$post->ID]: " . $replaced . "\n");
                    continue;
                }

                wp_update_post([
                    'ID'           => $post->ID,
                    'post_content' => $replaced,
                ]);
            }
        }

        WP_CLI::success('Completed!');
    }
}
