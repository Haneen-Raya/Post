<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * 
     * Defines how the Post model attributes are mapped to the JSON response keys.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->body,
            'status' => $this->is_published ? 'Published' : 'Draft',
            'publication_date' => $this->publish_date ? $this->publish_date->toIso8601String() : null,
            'meta_description' => $this->meta_description,
            'tags' => $this->tags,
            'keywords' => $this->keywords,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
