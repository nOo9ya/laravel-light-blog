<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'image' => $this->image ? [
                'url' => asset('storage/' . $this->image),
                'path' => $this->image,
            ] : null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            
            // 계층 구조
            'parent_id' => $this->parent_id,
            'parent' => new CategoryResource($this->whenLoaded('parent')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'ancestors' => CategoryResource::collection($this->whenLoaded('ancestors')),
            
            // 통계 정보
            'posts_count' => $this->when(
                $this->relationLoaded('posts') || isset($this->posts_count),
                $this->posts_count ?? $this->posts()->where('status', 'published')->count()
            ),
            'pages_count' => $this->when(
                isset($this->pages_count),
                $this->pages_count
            ),
            
            // 관계 데이터
            'posts' => $this->when(
                $request->get('include_posts') && $this->relationLoaded('posts'),
                PostResource::collection($this->posts)
            ),
            
            // URL
            'urls' => [
                'self' => route('api.categories.show', $this->slug),
                'web' => route('categories.show', $this->slug),
                'posts' => route('categories.posts', $this->slug),
                'edit' => $this->when(
                    auth()->check() && auth()->user()->hasRole('admin'),
                    route('admin.categories.edit', $this->id)
                ),
            ],
            
            // SEO 메타
            'meta' => [
                'seo_title' => $this->seo_title ?: $this->name,
                'seo_description' => $this->seo_description ?: $this->description,
                'seo_keywords' => $this->seo_keywords,
                'breadcrumb_trail' => $this->when(
                    $this->relationLoaded('ancestors'),
                    $this->ancestors->pluck('name')->push($this->name)->implode(' > ')
                ),
            ],
            
            // 타임스탬프
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'links' => [
                'subcategories' => route('api.categories.children', $this->id),
                'recent_posts' => route('api.categories.recent-posts', $this->id),
            ],
        ];
    }
}