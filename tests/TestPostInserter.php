<?php

namespace Chwnam\Akahoshi\Tests;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;
use Chwnam\Akahoshi\Scrap\PostInserter;
use WP_Post;
use WP_UnitTestCase;

use WP_User;

use function Chwnam\Akahoshi\getPostByGuid;
use function Chwnam\Akahoshi\guidToSlug;

class TestPostInserter extends WP_UnitTestCase
{
    protected PostInserter $inserter;

    public function setUp(): void
    {
        parent::setUp();
        $this->inserter = new PostInserter();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Destroy all posts
        global $wpdb;

        $wpdb->query("TRUNCATE TABLE $wpdb->posts");
        $wpdb->query("TRUNCATE TABLE $wpdb->postmeta");
    }

    /**
     * @param string[]    $expected
     * @param Article[]   $items
     * @param ScrapTarget $target
     * @param string[]    $slugs
     *
     * @return void
     *
     * @dataProvider provider
     */
    public function test_filter(array $expected, array $items, ScrapTarget $target, array $slugs): void
    {
        $postGen = function (string $slug) {
            static $count = 1;

            return wp_insert_post(
                [
                    'post_title'  => 'Test ' . $count++,
                    'post_name'   => $slug,
                    'post_type'   => 'post',
                    'post_status' => 'publish',
                    'meta_input'  => ['akahoshi_scrap' => '1'],
                ]
            );
        };

        // Assure $inserted
        foreach ($slugs as $slug) {
            $p    = $postGen($slug);
            $post = get_post($p);
            $this->assertInstanceOf(WP_Post::class, $post);
            $this->assertEquals($p, $post->ID);
            $this->assertEquals($slug, $post->post_name);
        }

        $inserted = $this->inserter->insert($items, $target);
        $newSlugs = array_map(fn($x) => guidToSlug($x->guid), $inserted);

        $this->assertEquals($expected, $newSlugs);
    }

    public function test_author(): void
    {
        $article        = new Article();
        $article->title = 'Test Article';
        $article->guid  = 'test-guid';

        $user = $this->factory()->user->create_and_get();

        $target         = new ScrapTarget();
        $target->userId = $user->ID;

        $inserted = $this->inserter->insert([$article], $target);

        $this->assertCount(1, $inserted);
        $this->assertEquals($article->guid, $inserted[0]->guid);

        $post = get_post(getPostByGuid($inserted[0]->guid));
        $this->assertInstanceOf(WP_Post::class, $post);
        $this->assertEquals($target->userId, $post->post_author, '$target->user_id was not inserted');
    }

    protected function provider(): array
    {
        $itemGen = function (string $title, string $guid) {
            $article        = new Article();
            $article->title = $title;
            $article->guid  = $guid;

            return $article;
        };

        return [
            '#1: 0 insertions' => [
                'expected' => [],
                'items'    => [
                    $itemGen('Test 1', 'x'),
                    $itemGen('Test 2', 'y'),
                    $itemGen('Test 3', 'z'),
                ],
                'target'   => new ScrapTarget(),
                'posts'    => ['x', 'y', 'z'],
            ],
            '#2: 1 insertion'  => [
                'expected' => ['x'],
                'items'    => [
                    $itemGen('Test 1', 'x'),
                    $itemGen('Test 2', 'y'),
                    $itemGen('Test 3', 'z'),
                ],
                'target'   => new ScrapTarget(),
                'posts'    => ['y', 'z'],
            ],
            '#3: 2 insertions' => [
                'expected' => ['x', 'y'],
                'items'    => [
                    $itemGen('Test 1', 'x'),
                    $itemGen('Test 2', 'y'),
                    $itemGen('Test 3', 'z'),
                ],
                'target'   => new ScrapTarget(),
                'posts'    => ['z'],
            ],
            '#4: 3 insertions' => [
                'expected' => ['x', 'y', 'z'],
                'items'    => [
                    $itemGen('Test 1', 'x'),
                    $itemGen('Test 2', 'y'),
                    $itemGen('Test 3', 'z'),
                ],
                'target'   => new ScrapTarget(),
                'posts'    => [],
            ],
        ];
    }
}
