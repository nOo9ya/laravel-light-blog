<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Page;
use App\Models\Category;
use App\Models\Tag;
use App\Services\SeoService;
use Illuminate\Http\Response;
use Carbon\Carbon;

class SitemapController extends Controller
{
    /**
     * XML 사이트맵 생성
     */
    public function index(): Response
    {
        $urls = SeoService::getSitemapUrls();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['url']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
    
    /**
     * robots.txt 생성
     */
    public function robots(): Response
    {
        $robotsTxt = "User-agent: *\n";
        $robotsTxt .= "Allow: /\n";
        $robotsTxt .= "Disallow: /admin/\n";
        $robotsTxt .= "Disallow: /api/\n";
        $robotsTxt .= "Disallow: /storage/\n";
        $robotsTxt .= "\n";
        $robotsTxt .= "Sitemap: " . url('/sitemap.xml') . "\n";
        
        return response($robotsTxt, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * RSS 2.0 피드 생성
     */
    public function rss(): Response
    {
        $posts = Post::published()
            ->with(['category', 'user'])
            ->latest('published_at')
            ->limit(20)
            ->get();

        $siteTitle = config('app.name', 'Laravel Blog');
        $siteUrl = config('app.url');
        $siteDescription = 'Latest posts from ' . $siteTitle;
        $lastBuildDate = $posts->first()?->published_at ?? now();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '  <title>' . htmlspecialchars($siteTitle) . '</title>' . "\n";
        $xml .= '  <link>' . htmlspecialchars($siteUrl) . '</link>' . "\n";
        $xml .= '  <description>' . htmlspecialchars($siteDescription) . '</description>' . "\n";
        $xml .= '  <language>ko-KR</language>' . "\n";
        $xml .= '  <lastBuildDate>' . $lastBuildDate->toRfc2822String() . '</lastBuildDate>' . "\n";
        $xml .= '  <atom:link href="' . url('/feed.rss') . '" rel="self" type="application/rss+xml" />' . "\n";
        $xml .= '  <generator>Laravel Blog System</generator>' . "\n";

        foreach ($posts as $post) {
            $xml .= '  <item>' . "\n";
            $xml .= '    <title>' . htmlspecialchars($post->title) . '</title>' . "\n";
            $xml .= '    <link>' . htmlspecialchars(route('posts.show', $post->slug)) . '</link>' . "\n";
            $xml .= '    <description>' . htmlspecialchars($post->summary ?: strip_tags(substr($post->content, 0, 200)) . '...') . '</description>' . "\n";
            $xml .= '    <content:encoded><![CDATA[' . $post->content . ']]></content:encoded>' . "\n";
            $xml .= '    <pubDate>' . $post->published_at->toRfc2822String() . '</pubDate>' . "\n";
            $xml .= '    <guid isPermaLink="true">' . htmlspecialchars(route('posts.show', $post->slug)) . '</guid>' . "\n";
            $xml .= '    <author>' . htmlspecialchars($post->user->email . ' (' . $post->user->name . ')') . '</author>' . "\n";
            
            if ($post->category) {
                $xml .= '    <category>' . htmlspecialchars($post->category->name) . '</category>' . "\n";
            }
            
            $xml .= '  </item>' . "\n";
        }

        $xml .= '</channel>' . "\n";
        $xml .= '</rss>';

        return response($xml, 200)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /**
     * Atom 피드 생성
     */
    public function feed(): Response
    {
        $posts = Post::published()
            ->with(['category', 'user', 'tags'])
            ->latest('published_at')
            ->limit(20)
            ->get();

        $siteTitle = config('app.name', 'Laravel Blog');
        $siteUrl = config('app.url');
        $siteDescription = 'Latest posts from ' . $siteTitle;
        $updated = $posts->first()?->published_at ?? now();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '  <title>' . htmlspecialchars($siteTitle) . '</title>' . "\n";
        $xml .= '  <link href="' . htmlspecialchars($siteUrl) . '" />' . "\n";
        $xml .= '  <link href="' . url('/feed') . '" rel="self" type="application/atom+xml" />' . "\n";
        $xml .= '  <id>' . htmlspecialchars($siteUrl) . '</id>' . "\n";
        $xml .= '  <subtitle>' . htmlspecialchars($siteDescription) . '</subtitle>' . "\n";
        $xml .= '  <updated>' . $updated->toAtomString() . '</updated>' . "\n";
        $xml .= '  <generator uri="https://laravel.com/" version="11.0">Laravel</generator>' . "\n";

        foreach ($posts as $post) {
            $xml .= '  <entry>' . "\n";
            $xml .= '    <title>' . htmlspecialchars($post->title) . '</title>' . "\n";
            $xml .= '    <link href="' . htmlspecialchars(route('posts.show', $post->slug)) . '" />' . "\n";
            $xml .= '    <id>' . htmlspecialchars(route('posts.show', $post->slug)) . '</id>' . "\n";
            $xml .= '    <updated>' . $post->published_at->toAtomString() . '</updated>' . "\n";
            $xml .= '    <published>' . $post->published_at->toAtomString() . '</published>' . "\n";
            $xml .= '    <summary type="text">' . htmlspecialchars($post->summary ?: strip_tags(substr($post->content, 0, 200)) . '...') . '</summary>' . "\n";
            $xml .= '    <content type="html"><![CDATA[' . $post->content . ']]></content>' . "\n";
            
            $xml .= '    <author>' . "\n";
            $xml .= '      <name>' . htmlspecialchars($post->user->name) . '</name>' . "\n";
            $xml .= '      <email>' . htmlspecialchars($post->user->email) . '</email>' . "\n";
            $xml .= '    </author>' . "\n";
            
            if ($post->category) {
                $xml .= '    <category term="' . htmlspecialchars($post->category->slug) . '" label="' . htmlspecialchars($post->category->name) . '" />' . "\n";
            }
            
            foreach ($post->tags as $tag) {
                $xml .= '    <category term="' . htmlspecialchars($tag->slug) . '" label="' . htmlspecialchars($tag->name) . '" />' . "\n";
            }
            
            $xml .= '  </entry>' . "\n";
        }

        $xml .= '</feed>';

        return response($xml, 200)
            ->header('Content-Type', 'application/atom+xml; charset=UTF-8');
    }
}
