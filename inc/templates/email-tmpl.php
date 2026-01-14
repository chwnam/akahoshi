<?php
/**
 * Template 이메일
 *
 * @var string $head_title
 * @var string $blog_name
 * @var int    $field_name
 * @var int    $article_count
 * @var string $archive_url
 * @var array{
 *     url: string,
 *     permalink: string,
 *     title: string,
 * }           $items
 */

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo esc_html($head_title); ?>
    </title>
    <style>
        /* 이메일 클라이언트 호환성을 위해 최대한 기본 태그 위주로 설정 */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7f9; /* 배경을 약간 푸른빛 도는 회색으로 변경 */
            font-family: 'Apple SD Gothic Neo', 'Malgun Gothic', sans-serif;
        }

        #akahoshi-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px; /* 라운드 증가로 더 부드럽게 */
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); /* 은은한 그림자 */
        }

        #akahoshi-email-inner {
            padding: 40px 30px; /* 여백 대폭 증가 */
        }

        #akahoshi-greetings {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin: 0 0 20px 0;
            letter-spacing: -0.5px;
        }

        #akahoshi-content {
            font-size: 16px;
            line-height: 1.8; /* 줄간격 확보 */
            color: #555;
        }

        .akahoshi-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            margin: 0 2px;
        }

        #akahoshi-field {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        #akahoshi-scrap-count {
            background-color: #ffebee;
            color: #d32f2f;
        }

        #akahoshi-article-list-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 25px 0 8px 0;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 5px;
        }

        #akahoshi-article-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        #akahoshi-article-list li {
            padding: 6px 0;
            line-height: 1.4;
        }

        #akahoshi-article-list a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
        }

        .akahoshi-permalink {
            font-size: 12px;
            color: #999 !important;
            margin-left: 6px;
            background: #f3f3f3;
            padding: 1px 5px;
            border-radius: 4px;
            white-space: nowrap;
        }

        #akahoshi-sender {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
<div id="akahoshi-container">
    <div id="akahoshi-email-inner">
        <section id="akahoshi-header">
            <h1 id="akahoshi-greetings">
                안녕하세요, <?php echo esc_html($blog_name); ?>입니다.
            </h1>
        </section>
        <section id="akahoshi-content">
            <p>
                <span id="akahoshi-field"
                      class="akahoshi-badge"><?php echo esc_html($field_name); ?></span>
                영역의 새 기사
                <span id="akahoshi-scrap-count"
                      class="akahoshi-badge"><?php echo esc_html($article_count); ?>개</span>가
                수집되었습니다.
            </p>
            <p>
                자세한 내용은 <a href="<?php echo esc_url($archive_url); ?>"
                           style="color: #1a73e8; text-decoration: underline; font-weight: bold;">블로그</a>에서 확인하세요.
            </p>
            <h3 id="akahoshi-article-list-title">수집된 기사 항목</h3>
            <ul id="akahoshi-article-list">
                <?php foreach ($items as $it): ?>
                    <li>
                        <a href="<?php echo esc_url($it['url']); ?>" target="_blank" rel="external nofollow noreferrer">
                            <?php echo esc_html($it['title']); ?>
                        </a> <a class="akahoshi-permalink" href="<?php echo esc_url($it['permalink']); ?>"
                                target="_blank" rel="external">
                            &#x2B50; 블로그
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <section id="akahoshi-footer">
            <h3 id="akahoshi-sender"
                style="margin-top:40px;padding-top:20px;border-top:1px solid #eee;font-size:14px;color: #888;">
                당신의 아카호시 플러그인이 드림.
            </h3>
        </section>
    </div>
</div>
</body>
</html>
