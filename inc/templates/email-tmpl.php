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
 *     title: string,
 * }           $items
 */

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>
        <?php echo esc_html($head_title); ?>
    </title>
    <style>
        #akahoshi-email-body {
            background-color: #fff;
            font-family: 'Noto Sans', 'sans-serif';
            line-height: 1.0;
            margin: 0;
            padding: 15px;
            width: 98%;
        }

        #akahoshi-email-body a {
            color: #1f539a;
            text-decoration: none;
        }

        #akahoshi-email-body-inner {
            background-color: #f8f8f8;
            border-radius: 6px;
            padding: 15px 30px;
        }

        #akahoshi-greetings {
            font-size: 20px;
            font-weight: 400;
            margin: 0;
            padding: 15px 0;
        }

        #akahoshi-content {
            font-size: 16px;
            line-height: 1.0;
        }

        #akahoshi-field {
            background-color: #c4c4c4;
            border-radius: 4px;
            color: #07582b;
            font-weight: 600;
            margin: 0;
            padding: 0 4px;
        }

        #akahoshi-scrap-count {
            background-color: #c4c4c4;
            border-radius: 4px;
            color: #e80039;
            font-weight: 600;
            margin: 0;
            padding: 0 4px;
        }

        #akahoshi-article-list-title {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
            padding: 12px 0 2px;
        }

        #akahoshi-article-list {
            margin: 0;
            padding: 6px 18px;
            list-style: none;
        }

        #akahoshi-article-list li {
            margin: 0;
            padding: 4px 0;
        }

        #akahoshi-sender {
            font-size: 16px;
            font-weight: 400;
            margin: 0;
            padding: 20px 0 2px 2px;
        }
    </style>
</head>
<body id="akahoshi-email-body">
<div id="akahoshi-email-body-inner">
    <section id="akahoshi-header">
        <h1 id="akahoshi-greetings">
            안녕하세요, <?php echo esc_html($blog_name); ?>입니다.
        </h1>
    </section>
    <section id="akahoshi-content">
        <p>
            <span id="akahoshi-field"><?php echo esc_html($field_name); ?></span>영역의 새 기사
            <span id="akahoshi-scrap-count"><?php echo esc_html($article_count); ?>개</span>가 수집되었습니다.
        </p>
        <p>
            자세한 내용은 [<a href="<?php echo esc_url($archive_url); ?>" target="_blank" rel="external">블로그</a>]에서 확인하세요.
        </p>
        <h3 id="akahoshi-article-list-title">수집된 기사 항목:</h3>
        <ul id="akahoshi-article-list">
            <?php foreach ($items as $it): ?>
                <li>
                    <a href="<?php echo esc_url($it['url']); ?>" target="_blank" rel="external nofollow noreferrer">
                        <?php echo esc_html($it['title']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section id="akahoshi-footer">
        <h3 id="akahoshi-sender">당신의 아카호시 플러그인이 드림.</h3>
    </section>
</div>
</body>
</html>
