<?php

namespace Chwnam\Akahoshi\Tests;

use Chwnam\Akahoshi\Object\ScrapTarget;
use Chwnam\Akahoshi\Scrap\CountLimit;
use Chwnam\Akahoshi\Scrap\Scraper;
use DateInterval;
use WP_Query;
use WP_UnitTestCase;

class TestCountLimit extends WP_UnitTestCase
{
    private CountLimit $limiter;

    private ScrapTarget $target;

    public function setUp(): void
    {
        parent::setUp();

        if ( ! did_action('init')) {
            do_action('init');
        }

        update_option('timezone_string', 'Asia/Seoul');

        if ( ! term_exists('Health', 'category')) {
            $term = wp_insert_term('Health', 'category');

            // Get the default settings
            $settings = get_option('akahoshi_settings');

            // Prepare the settings
            $settings['health']['enabled']     = 'yes';
            $settings['health']['term_id']     = $term['term_id'];
            $settings['health']['count_limit'] = 2;

            update_option('akahoshi_settings', $settings);
        }

        $targets = Scraper::getScrapTargets();

        foreach ($targets as $target) {
            if ('health' == $target->id) {
                $this->target  = $target;
                $this->limiter = new CountLimit($target);
            }
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Delete all posts
        $query = new WP_Query("post_type=post&post_status=publish&nopaging=1&no_found_rows=1&fields=ids");
        foreach ($query->posts as $post) {
            wp_delete_post($post, true);
        }
    }

    public function test_getOutdated(): void
    {
        // Create 12 fake articles.
        $posts = $this->factory()->post->create_many(12, [
            'post_type'   => 'post',
            'post_status' => 'publish',
        ]);
        foreach ($posts as $post) {
            wp_set_object_terms($post, $this->target->termId, 'category');
        }

        // Update 7 articles datetime
        $time = date_create('yesterday midnight', wp_timezone());
        for ($i = 0; $i < 7; ++$i) {
            wp_update_post([
                'ID'            => $posts[$i],
                'post_date'     => $time->format('Y-m-d H:i:s'),
                'post_date_gmt' => $time->format('Y-m-d H:i:s'),
            ]);
            $time = $time->modify('+1 hour');
        }

        // Update last 5 articles as outdated
        $time = $time->sub(new DateInterval('P60D'));
        $time = $time->setTime(0, 0);
        for ($i = 7; $i < 12; ++$i) {
            wp_update_post([
                'ID'            => $posts[$i],
                'post_date'     => $time->format('Y-m-d H:i:s'),
                'post_date_gmt' => $time->format('Y-m-d H:i:s'),
            ]);
            $time = $time->modify('+1 hour');
        }

        // Add a comment to the last article
        $this->factory()->comment->create([
            'comment_post_ID' => $posts[11]
        ]);

        $outdated = $this->limiter->getOutdated($this->target->countLimit);

        // Should be 4 because the last outdated article has a comment.
        $this->assertCount(4, $outdated);
        $expected = array_slice($posts, 7, 4);
        for ($i = 0; $i < count($outdated); ++$i) {
            $this->assertEquals($expected[$i], $outdated[$i]);
        }
    }
}
