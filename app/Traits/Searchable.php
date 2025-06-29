<?php

// app/Traits/Searchable.php

namespace App\Traits;

use Laravel\Scout\Searchable as ScoutSearchable;

trait Searchable
{
    use ScoutSearchable;

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'sku' => $this->sku,
            'tags' => $this->tags,
            'price' => $this->price,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published' && $this->published_at <= now();
    }
}
