<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\Http\Response;

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
}
