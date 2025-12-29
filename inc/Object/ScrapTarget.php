<?php

namespace Chwnam\Akahoshi\Object;

class ScrapTarget
{
    public string $id;
    public bool   $enable;
    public string $url;
    public array  $keywords;
    public string $postType = 'post';
    public int    $termId;
    public string $notify;

    public function __construct(array $arrayForm = [])
    {
        $this->id       = $arrayForm['id'] ?? '';
        $this->url      = $arrayForm['url'] ?? '';
        $this->keywords = array_unique(array_filter(array_map(
            fn($k) => strtolower(trim($k)), explode(',', $arrayForm['keywords'] ?? ''))));
        $this->postType = 'post';
        $this->termId   = (int)($arrayForm['term_id'] ?? '0');
        $this->enable   = filter_var($arrayForm['enable'] ?? 'no', FILTER_VALIDATE_BOOLEAN);
        $this->notify   = sanitize_email($arrayForm['notify'] ?? '');
    }
}
