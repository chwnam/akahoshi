<?php

namespace Chwnam\Akahoshi\Object;

class ScrapTarget
{
    public string $id;
    public bool   $enable;
    public string $url;
    public string $label;
    public array  $keywords;
    public string $postType = 'post';
    public int    $termId;
    public int    $userId;
    public string $notify;
    public int    $notifyAt;
    public int    $countLimit;
    public bool   $crawling;

    public function __construct(array $arrayForm = [])
    {
        $this->id         = $arrayForm['id'] ?? '';
        $this->url        = $arrayForm['url'] ?? '';
        $this->label      = $arrayForm['label'] ?? $arrayForm['id'] ?? '';
        $this->keywords   = array_unique(array_filter(array_map(
            fn($k) => strtolower(trim($k)),
            explode(',', $arrayForm['keywords'] ?? ''))));
        $this->postType   = 'post';
        $this->termId     = (int)($arrayForm['term_id'] ?? '0');
        $this->userId     = (int)($arrayForm['user_id'] ?? '0');
        $this->enable     = filter_var($arrayForm['enable'] ?? 'no', FILTER_VALIDATE_BOOLEAN);
        $this->notify     = sanitize_email($arrayForm['notify'] ?? '');
        $this->notifyAt   = (int)($arrayForm['notify_at'] ?? '-1');
        $this->countLimit = (int)($arrayForm['count_limit'] ?? '0');
        $this->crawling   = filter_var($arrayForm['crawling'] ?? 'no', FILTER_VALIDATE_BOOLEAN);
    }
}
