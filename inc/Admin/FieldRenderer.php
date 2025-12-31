<?php

namespace Chwnam\Akahoshi\Admin;

use function Chwnam\Akahoshi\getRssUrl;

class FieldRenderer
{
    public static function enable(array $args): void
    {
        $attrs = $args['attrs'] ?? [];

        printf(
            '<input id="%s" name="%s" type="checkbox" value="yes" %s/>',
            esc_attr($attrs['id']),
            esc_attr($attrs['name']),
            checked($attrs['checked'], 'yes', false),
        );

        printf(
            '<label for="%s">%s</label>',
            esc_attr($attrs['id']),
            esc_html($args['instruction'])
        );
    }

    public static function keywords(array $args): void
    {
        $attrs = $args['attrs'] ?? [];

        printf(
            '<input id="%s" name="%s" type="text" class="text regular-text" value="%s" />',
            esc_attr($attrs['id'] ?? ''),
            esc_attr($attrs['name'] ?? ''),
            esc_attr($attrs['value'] ?? ''),
        );

        printf('<p class="description">%s</p>', esc_html($args['description'] ?? ''));
    }

    public static function termId(array $args): void
    {
        $attrs = $args['attrs'] ?? [];

        wp_dropdown_categories($attrs);

        printf('<p class="description">%s</p>', esc_html($args['description'] ?? ''));
    }

    public static function notify(array $args): void
    {
        $attrs = $args['attrs'] ?? [];

        printf(
            '<input id="%s" name="%s" type="email" class="text regular-text" value="%s" />',
            esc_attr($attrs['id'] ?? ''),
            esc_attr($attrs['name'] ?? ''),
            esc_attr($attrs['value'] ?? ''),
        );

        printf('<p class="description">%s</p>', esc_html($args['description'] ?? ''));
    }

    public static function sectionNihongo(): void
    {
        echo '<p class="description">RSS 주소는 <a href="' .
            esc_url(getRssUrl('national')) .
            '" target="_blank">여기를 눌러</a> 참고하세요</p>';
    }

    public static function sectionHealth(): void
    {
        echo '<p class="description">RSS 주소는 <a href="' .
            esc_url(getRssUrl('medical')) .
            '" target="_blank">여기를 눌러</a> 참고하세요</p>';
    }

    public static function cssCodeSnippet(): void
    {
        echo '<p class="description">조선일보 RSS로 공개하는 기사 본문은 중 HTML은 아래와 같은 공통 CSS 코드를 포함합니다. ' .
            '테마의 레이아웃이 이미지 때문에 틀어지는 경우, 아래 CSS 코드 조각을 참고하세요</p>';
        echo '<pre>';
        echo esc_html(<<< PHP_EOT
<style>
.pre_wrap {display: flex; flex-wrap: wrap;}
.pre_img {width: 100%;}
.pre_img {display: block !important;}
.pre_img_mobile {display: none !important;}
@media only screen and (max-width: 689px) {
  .pre_img_pc {display: none !important;}
  .pre_img_mobile {display: block !important;}
}
</style>
PHP_EOT
        );
        echo '</pre>';
    }

    public static function miscDoItNow(): void
    {
        echo '<input id="akahoshi-do-it" type="submit" class="button button-primary" value="지금 바로 스크랩" form="akahoshi-do-it-now" onclick="return confirm(\'정말로 실행하려고?\')">';
        echo '<p class="description">지금 바로 스크랩을 실행해 봅니다.</p>';
    }

    public static function miscReset(): void
    {
        echo '<input id="akahoshi-reset" type="submit" class="button button-primary" value="리셋하기!" form="akahoshi-reset-all" onclick="return confirm(\'정말로 실행하려고?\')">';
        echo '<p class="description">현재 기록된 기사 및 스크랩 관련 데이터베이스 기록을 모두 삭제합니다.</p>';
    }
}
