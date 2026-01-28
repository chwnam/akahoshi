<?php

namespace Chwnam\Akahoshi\Object;

class Article
{
    public string $title = '';
    public string $link = '';
    /**
     * @deprecated
     * The RSS of health.chosun.com does not contain the guid field. Do not use it.
     */
    public string $guid = '';
    public string $author = '';
    public string $description = '';
    public string $datetime = '';
    public string $content = '';

    public ArticleMedia $media;

    public function __construct()
    {
        $this->media = new ArticleMedia();
    }
}
