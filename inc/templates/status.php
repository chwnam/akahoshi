<?php

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;

/**
 * 콘텍스트
 *
 * @var array{datetime: string, display: string}               $scrap
 * @var array{datetime: string, display: string}               $notify
 * @var array{datetime: string, display: string}               $limit
 * @var array{array{target: ScrapTarget, articles: Article[]}} $queues
 */
?>
<div class="wrap">
    <h1>상태 보고</h1>
    <hr class="wp-header-end"/>

    <h2>크론 상태</h2>
    <table class="form-table">
        <tr>
            <th scope="row">스크랩</th>
            <td>
                <ul style="margin:0;padding:0;">
                    <li>다음 스케쥴: <?php echo esc_html($scrap['datetime']); ?></li>
                    <li>스케쥴 간굑: <?php echo esc_html($scrap['display']); ?></li>
                </ul>
                <p class="description">RSS로부터 새 기사를 수집하는 주기입니다.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">알림</th>
            <td>
                <ul style="margin:0;padding:0;">
                    <li>다음 스케쥴: <?php echo esc_html($notify['datetime']); ?></li>
                    <li>스케쥴 간굑: <?php echo esc_html($notify['display']); ?></li>
                </ul>
                <p class="description">메일을 통지 점검 주기입니다.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">소거</th>
            <td>
                <ul style="margin:0;padding:0;">
                    <li>다음 스케쥴: <?php echo esc_html($limit['datetime']); ?></li>
                    <li>스케쥴 간굑: <?php echo esc_html($limit['display']); ?></li>
                </ul>
                <p class="description">시간이 지난 포스트를 소거하는 주기입니다.</p>
            </td>
        </tr>
    </table>

    <h2>메일 큐 상태</h2>
    <table class="form-table">
        <?php foreach ($queues as $queue): ?>
            <tr>
                <th scope="row">
                    <?php echo esc_html($queue['target']->label); ?>
                </th>
                <td>
                    <?php echo count($queue['articles']); ?>개 기사 대기 중.
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
