<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Page;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Analytics;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SearchController extends Controller
{
    /**
     * 통합 검색 페이지
     */
    public function index(Request $request): View|RedirectResponse
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all');
        
        // 빈 검색어 처리
        if (empty($query)) {
            return redirect()->back()->with('error', '검색어를 입력해주세요.');
        }
        
        $results = [];
        $total = 0;
        
        // 검색 타입에 따른 결과 조회
        switch ($type) {
            case 'post':
                $results['posts'] = $this->searchPosts($query);
                $total = $results['posts']->total();
                break;
                
            case 'page':
                $results['pages'] = $this->searchPages($query);
                $total = $results['pages']->total();
                break;
                
            case 'category':
                $results['posts'] = $this->searchCategories($query);
                $total = $results['posts']->total();
                break;
                
            case 'tag':
                $results['posts'] = $this->searchTags($query);
                $total = $results['posts']->total();
                break;
                
            default: // 통합 검색
                $results['posts'] = $this->searchPosts($query);
                $results['pages'] = $this->searchPages($query);
                $results['category_posts'] = $this->searchCategories($query);
                $results['tag_posts'] = $this->searchTags($query);
                
                $total = $results['posts']->total() + 
                        $results['pages']->total() + 
                        $results['category_posts']->total() + 
                        $results['tag_posts']->total();
                break;
        }
        
        // 검색 통계 기록
        Analytics::recordSearch($query, $total, $type, $request, auth()->user());
        
        return view(themed('search.index'), compact('results', 'query', 'type', 'total'));
    }
    
    /**
     * 포스트 검색
     */
    protected function searchPosts(string $query)
    {
        return Post::published()
            ->with(['category', 'tags', 'user'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'posts_page');
    }
    
    /**
     * 페이지 검색
     */
    protected function searchPages(string $query)
    {
        return Page::published()
            ->with(['category', 'user'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pages_page');
    }
    
    /**
     * 카테고리 검색 (카테고리에 속한 포스트 반환)
     */
    protected function searchCategories(string $query)
    {
        // 카테고리 이름으로 검색하여 해당 카테고리의 포스트들을 반환
        $categoryIds = Category::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->pluck('id');
            
        return Post::published()
            ->with(['category', 'tags', 'user'])
            ->whereIn('category_id', $categoryIds)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'categories_page');
    }
    
    /**
     * 태그 검색 (태그가 달린 포스트 반환)
     */
    protected function searchTags(string $query)
    {
        // 태그 이름으로 검색하여 해당 태그가 달린 포스트들을 반환
        $tagIds = Tag::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->pluck('id');
            
        return Post::published()
            ->with(['category', 'tags', 'user'])
            ->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'tags_page');
    }
    
    /**
     * AJAX 자동완성 검색
     */
    public function autocomplete(Request $request)
    {
        $query = $request->input('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $suggestions = [];
        
        // 포스트 제목에서 검색
        $posts = Post::published()
            ->where('title', 'like', "%{$query}%")
            ->select('title', 'slug')
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
        $categories = Category::where('name', 'like', "%{$query}%")
            ->select('name', 'slug')
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
        $tags = Tag::where('name', 'like', "%{$query}%")
            ->select('name', 'slug')
            ->limit(3)
            ->get();
            
        foreach ($tags as $tag) {
            $suggestions[] = [
                'type' => 'tag',
                'title' => $tag->name,
                'url' => route('tags.show', $tag->slug)
            ];
        }
        
        return response()->json($suggestions);
    }
}