<?php

// app/Traits/HasSEO.php

namespace App\Traits;

trait HasSEO
{
    public function getSeoTitle(): string
    {
        return $this->seo_meta['title'] ?? $this->name ?? '';
    }

    public function getSeoDescription(): string
    {
        return $this->seo_meta['description'] ?? $this->short_description ?? '';
    }

    public function getSeoKeywords(): array
    {
        return $this->seo_meta['keywords'] ?? $this->tags ?? [];
    }
}
