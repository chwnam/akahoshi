<?php

namespace Chwnam\Akahoshi\Admin;

use Chwnam\Akahoshi\Admin\FieldRenderer as FR;
use Chwnam\Akahoshi\Scrap\LastGuidMarker;
use Chwnam\Akahoshi\Scrap\PostInserter;
use JetBrains\PhpStorm\NoReturn;

use function Chwnam\Akahoshi\template;

class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addSettingsMenu']);

        add_action('admin_post_akahoshi_do_it_now', [$this, 'doItNow']);
        add_action('admin_post_akahoshi_reset_all', [$this, 'resetAll']);
        add_action('admin_post_akahoshi_preview', [$this, 'preview']);
        add_action('admin_post_akahoshi_chktmpl', [$this, 'chktmpl']);
    }

    public function addSettingsMenu(): void
    {
        add_options_page(
            '아카호시 설정 페이지',
            '아카호시',
            'manage_options',
            'akahoshi',
            [$this, 'outputSettingsPage'],
        );
    }

    public function outputSettingsPage(): void
    {
        $this->prepareSettings();

        template('settings.php');
    }

    private function prepareSettings(): void
    {
        $this->prepareSectionNihongo();
        $this->prepareSectionHealth();
        $this->prepareSectionMisc();
    }

    private function prepareSectionNihongo(): void
    {
        $settings = get_option('akahoshi_settings');

        add_settings_section(
            'akahoshi-nihongo',
            '일본어 스크랩',
            [FR::class, 'sectionNihongo'],
            'akahoshi'
        );

        add_settings_field(
            'akahoshi-nihongo-enable',
            '활성화',
            [FR::class, 'enable'],
            'akahoshi',
            'akahoshi-nihongo',
            [
                'attrs'       => [
                    'id'      => 'akahoshi-nihongo-enable',
                    'name'    => 'akahoshi_settings[nihongo][enable]',
                    'checked' => $settings['nihongo']['enable'] ?? false,
                ],
                'label_for'   => 'akahoshi-nihongo-enable',
                'instruction' => '일본어 기사 스크랩 활성화',
            ]
        );

        add_settings_field(
            'akahoshi-nihongo-keywords',
            '키워드',
            [FR::class, 'keywords'],
            'akahoshi',
            'akahoshi-nihongo',
            [
                'attrs'       => [
                    'id'    => 'akahoshi-nihongo-keyword',
                    'name'  => 'akahoshi_settings[nihongo][keywords]',
                    'value' => $settings['nihongo']['keywords'] ?? '',
                ],
                'label_for'   => 'akahoshi-nihongo-keywords',
                'description' => '쉽표로 여러 항목 구분',
            ]
        );

        add_settings_field(
            'akahoshi-nihongo-term_id',
            '카테고리',
            [FR::class, 'termId'],
            'akahoshi',
            'akahoshi-nihongo',
            [
                'attrs' => [
                    'id'         => 'akahoshi-nihongo-term_id',
                    'hide_empty' => 0,
                    'name'       => 'akahoshi_settings[nihongo][term_id]',
                    'selected'   => $settings['nihongo']['term_id'] ?? 0,
                ],

            ]
        );

        add_settings_field(
            'akahoshi-nihongo-user_id',
            '사용자',
            [FR::class, 'userId'],
            'akahoshi',
            'akahoshi-nihongo',
            [
                'attrs'       => [
                    'id'                => 'akahoshi-nihongo-user_id',
                    'name'              => 'akahoshi_settings[nihongo][user_id]',
                    'selected'          => $settings['nihongo']['user_id'] ?? 0,
                    'show_option_none'  => '(설정 안함)',
                    'option_none_value' => 0,
                ],
                'description' => '스크랩한 기사의 작성자를 선택한 사용자로 기록합니다.',
            ]
        );

        add_settings_field(
            'akahoshi-nihongo-notify',
            '이메일',
            [FR::class, 'notify'],
            'akahoshi',
            'akahoshi-nihongo',
            [
                'attrs'       => [
                    'id'    => 'akahoshi-nihongo-notify',
                    'name'  => 'akahoshi_settings[nihongo][notify]',
                    'value' => $settings['nihongo']['notify'] ?? '',
                ],
                'description' => '이메일 주소. 비워두면 보내지 않습니다.',
            ]
        );
    }

    private function prepareSectionHealth(): void
    {
        $settings = get_option('akahoshi_settings');

        add_settings_section(
            'akahoshi-health',
            '건강 스크랩',
            [FR::class, 'sectionHealth'],
            'akahoshi'
        );

        add_settings_field(
            'akahoshi-health-enable',
            '활성화',
            [FR::class, 'enable'],
            'akahoshi',
            'akahoshi-health',
            [
                'attrs'       => [
                    'id'      => 'akahoshi-health-enable',
                    'name'    => 'akahoshi_settings[health][enable]',
                    'checked' => $settings['health']['enable'] ?? false,
                ],
                'label_for'   => 'akahoshi-health-enable',
                'instruction' => '건강 기사 스크랩 활성화',
            ]
        );

        add_settings_field(
            'akahoshi-health-keywords',
            '키워드',
            [FR::class, 'keywords'],
            'akahoshi',
            'akahoshi-health',
            [
                'attrs'       => [
                    'id'    => 'akahoshi-health-keyword',
                    'name'  => 'akahoshi_settings[health][keywords]',
                    'value' => $settings['health']['keywords'] ?? '',
                ],
                'label_for'   => 'akahoshi-health-keywords',
                'description' => '쉽표로 여러 항목 구분',
            ]
        );

        add_settings_field(
            'akahoshi-health-term_id',
            '카테고리',
            [FR::class, 'termId'],
            'akahoshi',
            'akahoshi-health',
            [
                'attrs' => [
                    'id'         => 'akahoshi-health-term_id',
                    'hide_empty' => false,
                    'name'       => 'akahoshi_settings[health][term_id]',
                    'selected'   => $settings['health']['term_id'] ?? 0,
                ],
            ]
        );

        add_settings_field(
            'akahoshi-health-user_id',
            '사용자',
            [FR::class, 'userId'],
            'akahoshi',
            'akahoshi-health',
            [
                'attrs'       => [
                    'id'                => 'akahoshi-health-user_id',
                    'name'              => 'akahoshi_settings[health][user_id]',
                    'selected'          => $settings['health']['user_id'] ?? 0,
                    'show_option_none'  => '(설정 안함)',
                    'option_none_value' => 0,
                ],
                'description' => '스크랩한 기사의 작성자를 선택한 사용자로 기록합니다.',
            ]
        );

        add_settings_field(
            'akahoshi-health-notify',
            '이메일',
            [FR::class, 'notify'],
            'akahoshi',
            'akahoshi-health',
            [
                'attrs'       => [
                    'id'    => 'akahoshi-health-notify',
                    'name'  => 'akahoshi_settings[health][notify]',
                    'value' => $settings['health']['notify'] ?? '',
                ],
                'description' => '이메일 주소. 비워두면 보내지 않습니다.',
            ]
        );
    }

    private function prepareSectionMisc(): void
    {
        add_settings_section(
            'akahoshi-misc',
            '기타',
            '__return_empty_string',
            'akahoshi'
        );

        add_settings_field(
            'akahoshi-misc-css',
            'CSS 코드 조각',
            [FR::class, 'cssCodeSnippet'],
            'akahoshi',
            'akahoshi-misc',
        );

        add_settings_field(
            'akahoshi-misc-do-it',
            '지금 바로 실행',
            [FR::class, 'miscDoItNow'],
            'akahoshi',
            'akahoshi-misc',
        );

        add_settings_field(
            'akahoshi-misc-reset',
            '리셋',
            [FR::class, 'miscReset'],
            'akahoshi',
            'akahoshi-misc',
        );

        add_settings_field(
            'akahoshi-misc-email-tmpl',
            '이메일 템플릿',
            [FR::class, 'miscEmailTmpl'],
            'akahoshi',
            'akahoshi-misc',
        );
    }

    #[NoReturn]
    public function doItNow(): void
    {
        check_admin_referer('akahoshi_do_it_now', '_akahoshi_nonce');

        do_action('akahoshi_scrap');

        wp_redirect(wp_get_referer());
        exit;
    }

    #[NoReturn]
    public function resetAll(): void
    {
        check_admin_referer('akahoshi_reset_all', '_akahoshi_nonce');

        PostInserter::purge();
        LastGuidMarker::destroy();

        wp_redirect(wp_get_referer());
        exit;
    }

    #[NoReturn]
    public function preview(): void
    {
        check_admin_referer('akahoshi_preview', '_akahoshi_nonce');

        template('email-tmpl.php', self::getPreviewEmailContext());

        exit;
    }

    #[NoReturn]
    public function chktmpl(): void
    {
        check_admin_referer('akahoshi_chktmpl', '_akahoshi_nonce');

        $title = sprintf("[%s/아카호시] 템플릿 예제", get_bloginfo('name'));
        $body  = template('email-tmpl.php', self::getPreviewEmailContext(), true);
        $func  = fn() => 'text/html';

        add_filter('wp_mail_content_type', $func);
        wp_mail(get_bloginfo('admin_email'), $title, $body);
        remove_filter('wp_mail_content_type', $func);

        wp_redirect(wp_get_referer());
        exit;
    }

    private static function getPreviewEmailContext(): array
    {
        $items = [
            [
                'url'   => 'https://chosun.com/',
                'title' => '조선일보 홈페이지'
            ],
            [
                'url'   => 'https://naver.com/',
                'title' => '네이버 홈페이지'
            ],
            [
                'url'   => 'https://google.com/',
                'title' => '구글 홈페이지'
            ],
        ];

        return [
            'head_title'    => '아카호시 기사 스크랩 이메일',
            'blog_name'     => get_bloginfo('name'),
            'field_name'    => '예제',
            'article_count' => count($items),
            'archive_url'   => 'https://nate.com/',
            'items'         => $items,
        ];
    }
}
