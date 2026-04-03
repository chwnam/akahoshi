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
        add_settings_section(
            'akahoshi-nihongo',
            '일본어 스크랩',
            [FR::class, 'sectionNihongo'],
            'akahoshi'
        );

        self::prepareCommonSection('nihongo');
    }

    private function prepareSectionHealth(): void
    {
        add_settings_section(
            'akahoshi-health',
            '건강 스크랩',
            [FR::class, 'sectionHealth'],
            'akahoshi'
        );

        static::prepareCommonSection('health');
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
                'url'       => 'https://chosun.com/',
                'title'     => '조선일보 홈페이지',
                'permalink' => 'https://chosun.com',
            ],
            [
                'url'       => 'https://naver.com/',
                'title'     => '네이버 홈페이지',
                'permalink' => 'https://naver.com',
            ],
            [
                'url'       => 'https://google.com/',
                'title'     => '구글 홈페이지',
                'permalink' => 'https://google.com',
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

    private static function prepareCommonSection(string $section): void
    {
        $settings = get_option('akahoshi_settings');

        add_settings_field(
            "akahoshi-$section-enable",
            '활성화',
            [FR::class, 'enable'],
            'akahoshi',
            "akahoshi-$section",
            [
                'attrs'       => [
                    'id'      => "akahoshi-$section-enable",
                    'name'    => "akahoshi_settings[$section][enable]",
                    'checked' => $settings[$section]['enable'] ?? false,
                ],
                'label_for'   => 'akahoshi-health-enable',
                'instruction' => '건강 기사 스크랩 활성화',
            ]
        );

        add_settings_field(
            "akahoshi-$section-keywords",
            '키워드',
            [FR::class, 'keywords'],
            'akahoshi',
            "akahoshi-$section",
            [
                'attrs'       => [
                    'id'    => "akahoshi-$section-keyword",
                    'name'  => "akahoshi_settings[$section][keywords]",
                    'value' => $settings[$section]['keywords'] ?? '',
                ],
                'label_for'   => 'akahoshi-health-keywords',
                'description' => '쉽표로 여러 항목 구분',
            ]
        );

        add_settings_field(
            "akahoshi-$section-term_id",
            '카테고리',
            [FR::class, 'termId'],
            'akahoshi',
            "akahoshi-$section",
            [
                'attrs' => [
                    'id'         => "akahoshi-$section-term_id",
                    'hide_empty' => false,
                    'name'       => "akahoshi_settings[$section][term_id]",
                    'selected'   => $settings[$section]['term_id'] ?? 0,
                ],
            ]
        );

        add_settings_field(
            "akahoshi-$section-user_id",
            '사용자',
            [FR::class, 'userId'],
            'akahoshi',
            "akahoshi-$section",
            [
                'attrs'       => [
                    'id'                => "akahoshi-$section-user_id",
                    'name'              => "akahoshi_settings[$section][user_id]",
                    'selected'          => $settings[$section]['user_id'] ?? 0,
                    'show_option_none'  => '(설정 안함)',
                    'option_none_value' => 0,
                ],
                'description' => '스크랩한 기사의 작성자를 선택한 사용자로 기록합니다.',
            ]
        );

        add_settings_field(
            "akahoshi-$section-notify",
            '이메일',
            [FR::class, 'notify'],
            'akahoshi',
            "akahoshi-$section",
            [
                'attrs'       => [
                    'id'    => "akahoshi-$section-notify",
                    'name'  => "akahoshi_settings[$section][notify]",
                    'value' => $settings[$section]['notify'] ?? '',
                ],
                'description' => '이메일 주소. 비워두면 보내지 않습니다.',
            ]
        );

        add_settings_field(
            "akahoshi-$section-notify_at",
            '이메일 발송 시각',
            [FR::class, 'notifyAt'],
            'akahoshi',
            "akahoshi-$section",
            [
                'attrs'       => [
                    'id'    => "akahoshi-$section-notify-at",
                    'name'  => "akahoshi_settings[$section][notify_at]",
                    'value' => $settings[$section]['notify_at'] ?? '-1',
                ],
                'description' => '이메일 발송 시간을 지정합니다. 기사는 우선 저장된 다음, 매일 해당 시각에 한번에 보내집니다. -1은 받을 때마다 바로바로 보내는 것을 의미합니다.',
            ]
        );

        add_settings_field(
            "akahoshi-$section-count_limit",
            '기사 갯수 제한',
            [FR::class, 'countLimit'],
            'akahoshi',
            "akahoshi-$section",
            [
                'attrs'       => [
                    'id'    => "akahoshi-$section-count-limit",
                    'name'  => "akahoshi_settings[$section][count_limit]",
                    'value' => $settings[$section]['count_limit'] ?? '0',
                ],
                'description' => '스크랩된 포스트가 댓글이 달리지 않은 채로 지정된 주(週)를 넘기면 삭제됩니다. 0이면 지우지 않습니다.',
            ]
        );
    }
}
