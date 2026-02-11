<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;

use function Chwnam\Akahoshi\trimAuthors;

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
        $links    = [];
        $ns       = $xml->getNamespaces(true);

        foreach ($xml->channel->item as $item) {
            /**
             * Pattern 1
             * ---------
             * title
             * link
             * dc:creator
             * description
             * pubDate
             * content:encoded
             * media:content [url, type, height, width]
             * - media:description
             *
             *
             * Pattern 2
             * ---------
             * title
             * link
             * description
             * dc:date
             * author
             * category
             */
            $link = trim($item->link ?? '');

            // Do not accept duplicated items, if any.
            if (isset($links[$link])) {
                continue;
            }
            $links[$link] = true;

            $article = new Article();
            // Object properties
            $article->title = trim($item->title ?? '');
            $article->link  = $link;

            if (isset($ns['dc'], $item->children($ns['dc'])->creator)) {
                // from <dc:creator> ... </dc:creator>
                $article->author = trimAuthors($item->children($ns['dc'])->creator);
            } else {
                $article->author = trimAuthors($item->author ?? '');
            }

            if (isset($ns['dc'], $item->children($ns['dc'])->date)) {
                // from <dc:date> ... </dc:date>
                $article->datetime = trim($item->children($ns['dc'])->date);
            } else {
                $article->datetime = trim($item->pubDate ?? '');
            }

            if (isset($ns['content'], $item->children($ns['content'])->encoded)) {
                // from <content:encoded> ... </content:encoded>
                $article->content = trim($item->children($ns['content'])->encoded);
            } else {
                $article->content = trim($item->description ?? '');
            }

            $article->description = trim($item->description ?? '');

            // ... and media properties
            $mediaContent = isset($ns['media']) ? $item->children($ns['media'])->content : null;

            if ($mediaContent) {
                $mcAttrs = $mediaContent->attributes();

                $article->media->url    = trim($mcAttrs->url ?? '');
                $article->media->type   = trim($mcAttrs->type ?? '');
                $article->media->width  = (int)trim($mcAttrs->width ?? '0');
                $article->media->height = (int)trim($mcAttrs->height ?? '0');

                if ($mediaContent->description) {
                    $mcdAttrs = $mediaContent->description->attributes();

                    $article->media->description     = $mediaContent->description;
                    $article->media->descriptionType = $mcdAttrs->type ?? '';
                }
            }

            $articles[] = $article;
        }

        return $articles;
    }
}
