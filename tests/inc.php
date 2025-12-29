<?php

namespace Chwnam\Akahoshi\Tests;

function getTestsPath(string $relpath = ''): string
{
    if ($relpath) {
        $relpath = '/' . ltrim($relpath, '/\\');
    }

    return dirname(AKAHOSHI_MAIN) . "/tests$relpath";
}
