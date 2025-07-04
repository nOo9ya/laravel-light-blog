<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Posts 테이블 인덱스 최적화
        Schema::table('posts', function (Blueprint $table) {
            // 성능 최적화를 위한 복합 인덱스
            $table->index(['status', 'published_at'], 'idx_posts_status_published');
            $table->index(['category_id', 'status', 'published_at'], 'idx_posts_category_status_published');
            $table->index(['user_id', 'status', 'created_at'], 'idx_posts_user_status_created');
            $table->index(['slug'], 'idx_posts_slug');
            $table->index(['views'], 'idx_posts_views');
            
            // 검색 최적화를 위한 인덱스
            $table->index(['title'], 'idx_posts_title');
        });

        // Categories 테이블 인덱스 최적화
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['parent_id', 'type', 'active'], 'idx_categories_parent_type_active');
            $table->index(['slug'], 'idx_categories_slug');
            $table->index(['type', 'active', 'order'], 'idx_categories_type_active_order');
        });

        // Tags 테이블 인덱스 최적화
        Schema::table('tags', function (Blueprint $table) {
            $table->index(['slug'], 'idx_tags_slug');
            $table->index(['post_count'], 'idx_tags_post_count');
        });

        // Post_tag 피벗 테이블 인덱스 최적화
        Schema::table('post_tag', function (Blueprint $table) {
            $table->index(['tag_id', 'post_id'], 'idx_post_tag_tag_post');
        });

        // Comments 테이블 인덱스 최적화
        Schema::table('comments', function (Blueprint $table) {
            $table->index(['post_id', 'status', 'created_at'], 'idx_comments_post_status_created');
            $table->index(['parent_id'], 'idx_comments_parent');
            $table->index(['status', 'created_at'], 'idx_comments_status_created');
            $table->index(['email'], 'idx_comments_email');
        });

        // Pages 테이블 인덱스 최적화
        Schema::table('pages', function (Blueprint $table) {
            $table->index(['slug'], 'idx_pages_slug');
            $table->index(['status', 'created_at'], 'idx_pages_status_created');
        });

        // Seo_metas 테이블 인덱스 최적화
        Schema::table('seo_metas', function (Blueprint $table) {
            $table->index(['post_id'], 'idx_seo_metas_post');
        });

        // Users 테이블 인덱스 최적화 (이미 있을 수 있음)
        Schema::table('users', function (Blueprint $table) {
            // email은 이미 unique 인덱스가 있음
            $table->index(['role'], 'idx_users_role');
            $table->index(['created_at'], 'idx_users_created');
        });

        // Cache 테이블 생성 (파일 캐시가 아닌 DB 캐시 사용시)
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
                
                $table->index(['expiration'], 'idx_cache_expiration');
            });
        }

        // Cache locks 테이블 생성
        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
                
                $table->index(['expiration'], 'idx_cache_locks_expiration');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Posts 테이블 인덱스 제거
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('idx_posts_status_published');
            $table->dropIndex('idx_posts_category_status_published');
            $table->dropIndex('idx_posts_user_status_created');
            $table->dropIndex('idx_posts_slug');
            $table->dropIndex('idx_posts_views');
            $table->dropIndex('idx_posts_title');
        });

        // Categories 테이블 인덱스 제거
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_parent_type_active');
            $table->dropIndex('idx_categories_slug');
            $table->dropIndex('idx_categories_type_active_order');
        });

        // Tags 테이블 인덱스 제거
        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex('idx_tags_slug');
            $table->dropIndex('idx_tags_post_count');
        });

        // Post_tag 테이블 인덱스 제거
        Schema::table('post_tag', function (Blueprint $table) {
            $table->dropIndex('idx_post_tag_tag_post');
        });

        // Comments 테이블 인덱스 제거
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('idx_comments_post_status_created');
            $table->dropIndex('idx_comments_parent');
            $table->dropIndex('idx_comments_status_created');
            $table->dropIndex('idx_comments_email');
        });

        // Pages 테이블 인덱스 제거
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex('idx_pages_slug');
            $table->dropIndex('idx_pages_status_created');
        });

        // Seo_metas 테이블 인덱스 제거
        Schema::table('seo_metas', function (Blueprint $table) {
            $table->dropIndex('idx_seo_metas_post');
        });

        // Users 테이블 인덱스 제거
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_created');
        });

        // Cache 테이블 제거
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
