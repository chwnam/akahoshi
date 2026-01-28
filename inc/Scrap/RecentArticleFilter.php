<?php

namespace Chwnam\Akahoshi\Scrap;

use Chwnam\Akahoshi\Object\Article;
use Chwnam\Akahoshi\Object\ScrapTarget;

class RecentArticleFilter
{
    /**
     * @param Article[]   $items
     * @param ScrapTarget $target
     *
     * @return Article[]
     */
    public function filter(array $items, ScrapTarget $target): array
    {
        // get lastGuid and filter latest items.
        $lastGuid = LastGuidMarker::get($target->id);

        if ($lastGuid) {
            $found = false;
            for ($i = 0; $i < count($items); ++$i) {
                if ($items[$i]->link === $lastGuid) {
                    $found = true;
                    break;
                }
            }
            $output = $found ? array_slice($items, 0, $i) : $items;
        } else {
            $output = $items;
        }

        // update lastGuid
        if (count($output) && $output[0]->link) {
            LastGuidMarker::set($target->id, $output[0]->link);
        }

        return $output;
    }
}
