<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostAttachment extends Model
{
    /*
    |--------------------------------------------------------------------------
    | 모델 속성 (Attributes & Properties)
    |--------------------------------------------------------------------------
    */
    // region --- 모델 속성 ---
    protected $fillable = [
        'post_id',
        'filename',
        'original_name',
        'path',
        'mime_type',
        'size',
        'type',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];
    // endregion

    /*
    |--------------------------------------------------------------------------
    | 관계 (Relationships)
    |--------------------------------------------------------------------------
    */
    // region --- 관계 ---
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
    // endregion

    /*
    |--------------------------------------------------------------------------
    | 스코프 (Scopes)
    |--------------------------------------------------------------------------
    */
    // region --- 스코프 ---
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDocuments($query)
    {
        return $query->where('type', 'document');
    }

    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }
    // endregion

    /*
    |--------------------------------------------------------------------------
    | 접근자/변경자 (Accessors & Mutators)
    |--------------------------------------------------------------------------
    */
    // region --- 접근자/변경자 ---
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getIsImageAttribute(): bool
    {
        return $this->type === 'image';
    }

    public function getIsDocumentAttribute(): bool
    {
        return $this->type === 'document';
    }
    // endregion

    /*
    |--------------------------------------------------------------------------
    | 기타 메서드 (Additional Methods)
    |--------------------------------------------------------------------------
    */
    // region --- 기타 메서드 ---
    public function delete(): ?bool
    {
        if (Storage::exists($this->path)) {
            Storage::delete($this->path);
        }
        
        return parent::delete();
    }

    public static function determineTypeFromMimeType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ];
        
        if (in_array($mimeType, $documentTypes)) {
            return 'document';
        }
        
        return 'other';
    }
    // endregion
}
