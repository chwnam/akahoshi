<?php

namespace Chwnam\Akahoshi\Object;

class Article
{
    public string $title = '';
    public string $link = '';
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
