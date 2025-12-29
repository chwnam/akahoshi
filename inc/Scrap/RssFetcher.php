<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;

class RssFetcher
{
    /**
     * @param string $url
     *
     * @return Article[]
     */
    public function fetch(string $url): array
    {
        $xml = simplexml_load_file($url);
        if (!$xml) {
            return [];
        }

        $articles = [];
        $ns       = $xml->getNamespaces(true);

        foreach ($xml->channel->item as $item) {
            /**
             * title
             * link
             * guid
             * dc:creator
             * description
             * pubDate
             * content:encoded
             * media:content [url, type, height, width]
             * - media:description
             */
            $article = new Article();
            $title   = trim($item->title ?? '');

            // Object properties
            $article->title       = $title;
            $article->link        = trim($item->link ?? '');
            $article->guid        = trim($item->guid ?? '');
            $article->author      = trim($item->children($ns['dc'])->creator ?? '');
            $article->description = trim($item->description ?? '');
            $article->datetime    = trim($item->pubDate ?? '');
            $article->content     = trim($item->children($ns['content'])->encoded ?? '');

            // ... and media properties
            $mediaContent = $item->children($ns['media'])->content;

            if ($mediaContent) {
                $mcAttrs = $mediaContent->attributes();

                $article->media->url    = trim($mcAttrs->url ?? '');
                $article->media->type   = trim($mcAttrs->type ?? '');
                $article->media->width  = (int)trim($mcAttrs->width ?? '0');
                $article->media->height = (int)trim($mcAttrs->height ?? '0');
            }

            if ($mediaContent->description) {
                $mcdAttrs = $mediaContent->description->attributes();

                $article->media->description     = $mediaContent->description;
                $article->media->descriptionType = $mcdAttrs->type ?? '';
            }

            $articles[] = $article;
        }

        return $articles;
    }
}
