<?php

namespace Chwnam\Akahoshi\Tests;

use WP_UnitTestCase;
use WP_User;

class TestAkahoshi extends WP_UnitTestCase
{
    public function test_sanitizer(): void
    {
        $expected = [
            'nihongo' => [
                'id'       => 'nihongo',
                'enable'   => 'yes',
                'label'    => '일본어',
                'keywords' => 'nihongo-keyword',
                'term_id'  => 22,
                'user_id'  => 3,
                'notify'   => 'test_nihongo@email.com',
            ],
            'health'  => [
                'id'       => 'health',
                'enable'   => 'no',
                'label'    => '건강',
                'keywords' => 'health-keyword',
                'term_id'  => 23,
                'user_id'  => 4,
                'notify'   => 'test_health@email.com',
            ],
        ];

        update_option('akahoshi_settings', $expected);

        $actual = get_option('akahoshi_settings');

        $this->assertEquals($expected['nihongo']['user_id'], $actual['nihongo']['user_id']);
        $this->assertEquals($expected['health']['user_id'], $actual['health']['user_id']);
    }
}
