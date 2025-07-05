<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Page;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Analytics;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchService
{
    /**
     * 통합 검색 수행
     */
    public function search(string $query, array $types = ['all'], int $limit = 10): array
    {
        // 검색어 전처리 및 보안 강화
        $searchTerm = $this->sanitizeSearchQuery($query);
        if (!$searchTerm) {
            return ['posts' => collect(), 'pages' => collect(), 'categories' => collect(), 'tags' => collect(), 'total' => 0, 'query' => $query];
        }

        $results = [];
        $totalResults = 0;

        // 검색어 기록
        $this->recordSearch($searchTerm, $types);

        if (in_array('all', $types) || in_array('posts', $types)) {
            $results['posts'] = $this->searchPosts($searchTerm, $limit);
            $totalResults += $results['posts']->count();
        }

        if (in_array('all', $types) || in_array('pages', $types)) {
            $results['pages'] = $this->searchPages($searchTerm, $limit);
            $totalResults += $results['pages']->count();
        }

        if (in_array('all', $types) || in_array('categories', $types)) {
            $results['categories'] = $this->searchCategories($searchTerm, $limit);
            $totalResults += $results['categories']->count();
        }

        if (in_array('all', $types) || in_array('tags', $types)) {
            $results['tags'] = $this->searchTags($searchTerm, $limit);
            $totalResults += $results['tags']->count();
        }

        $results['total'] = $totalResults;
        $results['query'] = $query;

        return $results;
    }

    /**
     * 페이지네이션을 지원하는 통합 검색
     */
    public function searchWithPagination(string $query, array $types = ['all'], int $perPage = 10, int $page = 1): array
    {
        $searchTerm = $this->sanitizeSearchQuery($query);
        if (!$searchTerm) {
            return ['posts' => collect(), 'pages' => collect(), 'categories' => collect(), 'tags' => collect(), 'query' => $query];
        }

        $results = [];
        
        // 검색어 기록
        $this->recordSearch($searchTerm, $types);

        if (in_array('all', $types) || in_array('posts', $types)) {
            $results['posts'] = $this->searchPostsWithPagination($searchTerm, $perPage, $page);
        }

        if (in_array('all', $types) || in_array('pages', $types)) {
            $results['pages'] = $this->searchPagesWithPagination($searchTerm, $perPage, $page);
        }

        if (in_array('all', $types) || in_array('categories', $types)) {
            $results['categories'] = $this->searchCategoriesWithPagination($searchTerm, $perPage, $page);
        }

        if (in_array('all', $types) || in_array('tags', $types)) {
            $results['tags'] = $this->searchTagsWithPagination($searchTerm, $perPage, $page);
        }

        $results['query'] = $query;

        return $results;
    }

    /**
     * 검색어 전처리 및 보안 강화
     */
    protected function sanitizeSearchQuery(string $query): ?string
    {
        $searchTerm = trim($query);
        
        // 최소 길이 검증
        if (strlen($searchTerm) < 2) {
            return null;
        }

        // 최대 길이 제한
        if (strlen($searchTerm) > 100) {
            $searchTerm = substr($searchTerm, 0, 100);
        }

        // 특수문자 제거 (기본적인 보안)
        $searchTerm = preg_replace('/[^\p{L}\p{N}\s\-_.]/u', '', $searchTerm);
        
        return $searchTerm;
    }

    /**
     * 포스트 검색
     */
    protected function searchPosts(string $query, int $limit): Collection
    {
        return Post::published()
            ->select(['id', 'title', 'slug', 'summary', 'published_at', 'views_count', 'user_id', 'category_id'])
            ->with([
                'category:id,name,slug',
                'user:id,name'
            ])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%");
            })
            ->orderByRaw(
                '(CASE WHEN title LIKE ? THEN 3 WHEN summary LIKE ? THEN 2 WHEN content LIKE ? THEN 1 ELSE 0 END) DESC',
                ["%{$query}%", "%{$query}%", "%{$query}%"]
            )
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 포스트 검색 (페이지네이션)
     */
    protected function searchPostsWithPagination(string $query, int $perPage, int $page): LengthAwarePaginator
    {
        return Post::published()
            ->select(['id', 'title', 'slug', 'summary', 'published_at', 'views_count', 'user_id', 'category_id'])
            ->with([
                'category:id,name,slug',
                'user:id,name'
            ])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%");
            })
            ->orderByRaw(
                '(CASE WHEN title LIKE ? THEN 3 WHEN summary LIKE ? THEN 2 WHEN content LIKE ? THEN 1 ELSE 0 END) DESC',
                ["%{$query}%", "%{$query}%", "%{$query}%"]
            )
            ->orderBy('published_at', 'desc')
            ->paginate($perPage, ['*'], 'posts_page', $page);
    }

    /**
     * 페이지 검색
     */
    protected function searchPages(string $query, int $limit): Collection
    {
        return Page::where('is_published', true)
            ->select(['id', 'title', 'slug', 'excerpt', 'created_at', 'views_count', 'category_id'])
            ->with('category:id,name,slug')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->orderByRaw(
                '(CASE WHEN title LIKE ? THEN 3 WHEN excerpt LIKE ? THEN 2 WHEN content LIKE ? THEN 1 ELSE 0 END) DESC',
                ["%{$query}%", "%{$query}%", "%{$query}%"]
            )
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 페이지 검색 (페이지네이션)
     */
    protected function searchPagesWithPagination(string $query, int $perPage, int $page): LengthAwarePaginator
    {
        return Page::where('is_published', true)
            ->select(['id', 'title', 'slug', 'excerpt', 'created_at', 'views_count', 'category_id'])
            ->with('category:id,name,slug')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->orderByRaw(
                '(CASE WHEN title LIKE ? THEN 3 WHEN excerpt LIKE ? THEN 2 WHEN content LIKE ? THEN 1 ELSE 0 END) DESC',
                ["%{$query}%", "%{$query}%", "%{$query}%"]
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'pages_page', $page);
    }

    /**
     * 카테고리 검색
     */
    protected function searchCategories(string $query, int $limit): Collection
    {
        return Category::where('is_active', true)
            ->select(['id', 'name', 'slug', 'description', 'sort_order'])
            ->withCount(['posts' => function ($q) {
                $q->where('status', 'published');
            }])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderByRaw(
                '(CASE WHEN name LIKE ? THEN 2 WHEN description LIKE ? THEN 1 ELSE 0 END) DESC',
                ["%{$query}%", "%{$query}%"]
            )
            ->orderBy('posts_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 카테고리 검색 (페이지네이션)
     */
    protected function searchCategoriesWithPagination(string $query, int $perPage, int $page): LengthAwarePaginator
    {
        return Category::where('is_active', true)
            ->select(['id', 'name', 'slug', 'description', 'sort_order'])
            ->withCount(['posts' => function ($q) {
                $q->where('status', 'published');
            }])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderByRaw(
                '(CASE WHEN name LIKE ? THEN 2 WHEN description LIKE ? THEN 1 ELSE 0 END) DESC',
                ["%{$query}%", "%{$query}%"]
            )
            ->orderBy('posts_count', 'desc')
            ->paginate($perPage, ['*'], 'categories_page', $page);
    }

    /**
     * 태그 검색
     */
    protected function searchTags(string $query, int $limit): Collection
    {
        return Tag::select(['id', 'name', 'slug', 'description', 'color'])
            ->withCount(['posts' => function ($q) {
                $q->where('status', 'published');
            }])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderByRaw(
                '(CASE WHEN name LIKE ? THEN 2 WHEN description LIKE ? THEN 1 ELSE 0 END) DESC',
                ["%{$query}%", "%{$query}%"]
            )
            ->orderBy('posts_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 태그 검색 (페이지네이션)
     */
    protected function searchTagsWithPagination(string $query, int $perPage, int $page): LengthAwarePaginator
    {
        return Tag::select(['id', 'name', 'slug', 'description', 'color'])
            ->withCount(['posts' => function ($q) {
                $q->where('status', 'published');
            }])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderByRaw(
                '(CASE WHEN name LIKE ? THEN 2 WHEN description LIKE ? THEN 1 ELSE 0 END) DESC',
                ["%{$query}%", "%{$query}%"]
            )
            ->orderBy('posts_count', 'desc')
            ->paginate($perPage, ['*'], 'tags_page', $page);
    }

    /**
     * 자동완성 검색 제안
     */
    public function getAutocompleteSuggestions(string $query, int $limit = 10): array
    {
        $searchTerm = $this->sanitizeSearchQuery($query);
        if (!$searchTerm) {
            return [];
        }

        $suggestions = [];

        try {
            // 포스트 제목에서 검색
            $posts = Post::published()
                ->select(['title', 'slug'])
                ->where('title', 'like', "%{$searchTerm}%")
                ->limit(5)
                ->get();

            foreach ($posts as $post) {
                $suggestions[] = [
                    'type' => 'post',
                    'title' => $post->title,
                    'url' => route('posts.show', $post->slug)
                ];
            }

            // 카테고리에서 검색
            $categories = Category::where('is_active', true)
                ->select(['name', 'slug'])
                ->where('name', 'like', "%{$searchTerm}%")
                ->limit(3)
                ->get();

            foreach ($categories as $category) {
                $suggestions[] = [
                    'type' => 'category',
                    'title' => $category->name,
                    'url' => route('categories.show', $category->slug)
                ];
            }

            // 태그에서 검색
            $tags = Tag::select(['name', 'slug'])
                ->where('name', 'like', "%{$searchTerm}%")
                ->limit(2)
                ->get();

            foreach ($tags as $tag) {
                $suggestions[] = [
                    'type' => 'tag',
                    'title' => $tag->name,
                    'url' => route('tags.show', $tag->slug)
                ];
            }

        } catch (\Exception $e) {
            Log::warning('자동완성 검색 오류', [
                'query' => $searchTerm,
                'error' => $e->getMessage()
            ]);
        }

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * 인기 검색어 조회
     */
    public function getPopularSearches(int $limit = 10): array
    {
        try {
            return Analytics::where('event_type', 'search')
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('event_data, COUNT(*) as search_count')
                ->groupBy('event_data')
                ->orderBy('search_count', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    $data = json_decode($item->event_data, true);
                    return [
                        'query' => $data['query'] ?? '',
                        'count' => $item->search_count
                    ];
                })
                ->filter(function ($item) {
                    return !empty($item['query']) && strlen($item['query']) >= 2;
                })
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            Log::warning('인기 검색어 조회 오류', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * 관련 검색어 조회
     */
    public function getRelatedSearches(string $query, int $limit = 5): array
    {
        $searchTerm = $this->sanitizeSearchQuery($query);
        if (!$searchTerm) {
            return [];
        }

        try {
            $words = explode(' ', $searchTerm);
            $relatedSearches = [];

            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $related = Analytics::where('event_type', 'search')
                        ->whereRaw('JSON_EXTRACT(event_data, "$.query") LIKE ?', ["%{$word}%"])
                        ->whereRaw('JSON_EXTRACT(event_data, "$.query") != ?', [$searchTerm])
                        ->selectRaw('JSON_EXTRACT(event_data, "$.query") as search_query, COUNT(*) as searches')
                        ->groupBy('search_query')
                        ->orderBy('searches', 'desc')
                        ->limit($limit)
                        ->pluck('search_query')
                        ->map(function ($item) {
                            return trim($item, '"');
                        })
                        ->filter(function ($item) {
                            return !empty($item) && strlen($item) >= 2;
                        })
                        ->toArray();

                    $relatedSearches = array_merge($relatedSearches, $related);
                }
            }

            return array_slice(array_unique($relatedSearches), 0, $limit);
        } catch (\Exception $e) {
            Log::warning('관련 검색어 조회 오류', [
                'query' => $searchTerm,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * 검색어 기록
     */
    protected function recordSearch(string $query, array $types): void
    {
        try {
            // Analytics 미들웨어에서 자동으로 기록되지만, 
            // 여기서 추가적인 검색 관련 데이터를 기록
            
            if (request()->route() && request()->route()->getName() === 'search.index') {
                $searchResultsCount = 0;
                
                // 각 타입별 결과 수 계산
                if (in_array('all', $types) || in_array('posts', $types)) {
                    $searchResultsCount += Post::published()
                        ->where(function ($q) use ($query) {
                            $q->where('title', 'like', "%{$query}%")
                              ->orWhere('content', 'like', "%{$query}%")
                              ->orWhere('summary', 'like', "%{$query}%");
                        })->count();
                }

                // 요청에 결과 수 추가 (Analytics 미들웨어에서 사용)
                request()->merge(['results_count' => $searchResultsCount]);
            }
        } catch (\Exception $e) {
            Log::warning('검색어 기록 오류', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
        }
    }
}