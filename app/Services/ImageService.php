<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    private ImageManager $imageManager;
    private array $allowedMimeTypes;
    private int $maxFileSize;
    private int $defaultQuality;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
        $this->defaultQuality = config('app.image_quality', 85);
    }

    public function uploadMainImage(UploadedFile $file, string $directory = 'posts/main'): array
    {
        $this->validateImage($file);
        
        $filename = $this->generateFilename($file, 'webp');
        $path = $directory . '/' . $filename;
        
        $image = $this->imageManager->read($file->getPathname());
        
        // 대표 이미지는 최대 1200px 너비로 리사이즈
        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }
        
        $webpData = $image->toWebp($this->defaultQuality);
        Storage::put($path, $webpData);
        
        // 썸네일도 생성 (400px)
        $thumbnailPath = $directory . '/thumbs/' . $filename;
        $thumbnailImage = $image->scale(width: 400);
        $thumbnailWebpData = $thumbnailImage->toWebp($this->defaultQuality);
        Storage::put($thumbnailPath, $thumbnailWebpData);
        
        return [
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'original_name' => $file->getClientOriginalName(),
            'size' => strlen($webpData),
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }

    public function uploadOgImage(UploadedFile $file, string $directory = 'posts/og'): array
    {
        $this->validateImage($file);
        
        // OG 이미지는 1200x630 이상이어야 함
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo[0] < 1200 || $imageInfo[1] < 630) {
            throw new \InvalidArgumentException('OG 이미지는 최소 1200x630 크기여야 합니다.');
        }
        
        $filename = $this->generateFilename($file, $file->getClientOriginalExtension());
        $path = $directory . '/' . $filename;
        
        $image = $this->imageManager->read($file->getPathname());
        
        // OG 이미지는 WebP 변환하지 않고 90% 품질로 압축
        $compressedData = match($file->getMimeType()) {
            'image/jpeg' => $image->toJpeg(90),
            'image/png' => $image->toPng(),
            'image/gif' => $image->toGif(),
            default => $image->toJpeg(90)
        };
        
        Storage::put($path, $compressedData);
        
        return [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'size' => strlen($compressedData),
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }

    public function uploadContentImage(UploadedFile $file, string $directory = 'posts/content'): array
    {
        $this->validateImage($file);
        
        $filename = $this->generateFilename($file, 'webp');
        $path = $directory . '/' . $filename;
        
        $image = $this->imageManager->read($file->getPathname());
        
        // 본문 이미지는 최대 800px 너비로 리사이즈
        if ($image->width() > 800) {
            $image->scale(width: 800);
        }
        
        $webpData = $image->toWebp($this->defaultQuality);
        Storage::put($path, $webpData);
        
        return [
            'path' => $path,
            'url' => Storage::url($path),
            'original_name' => $file->getClientOriginalName(),
            'size' => strlen($webpData),
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }

    public function deleteImage(string $path): bool
    {
        if (Storage::exists($path)) {
            return Storage::delete($path);
        }
        
        return false;
    }

    public function deleteImageWithThumbnail(string $path): bool
    {
        $deleted = $this->deleteImage($path);
        
        // 썸네일도 삭제 시도
        $thumbnailPath = str_replace('/', '/thumbs/', $path);
        $this->deleteImage($thumbnailPath);
        
        return $deleted;
    }

    private function validateImage(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('유효하지 않은 파일입니다.');
        }
        
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException('지원하지 않는 이미지 형식입니다. (JPG, PNG, GIF, WebP만 지원)');
        }
        
        if ($file->getSize() > $this->maxFileSize) {
            throw new \InvalidArgumentException('파일 크기가 너무 큽니다. (최대 10MB)');
        }
    }

    private function generateFilename(UploadedFile $file, string $extension): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $name = preg_replace('/[^가-힣a-zA-Z0-9\-_]/', '', $name);
        $name = $name ?: 'image';
        
        return $name . '_' . time() . '_' . uniqid() . '.' . $extension;
    }

    public function getImageDimensions(string $path): array
    {
        if (!Storage::exists($path)) {
            return ['width' => 0, 'height' => 0];
        }
        
        $fullPath = Storage::path($path);
        $imageInfo = getimagesize($fullPath);
        
        return [
            'width' => $imageInfo[0] ?? 0,
            'height' => $imageInfo[1] ?? 0,
        ];
    }

    public function resizeImage(string $path, int $width, int $height = null): string
    {
        if (!Storage::exists($path)) {
            throw new \InvalidArgumentException('이미지 파일을 찾을 수 없습니다.');
        }
        
        $image = $this->imageManager->read(Storage::path($path));
        
        if ($height) {
            $image->resize($width, $height);
        } else {
            $image->scale(width: $width);
        }
        
        $resizedPath = str_replace('.', "_resized_{$width}x{$height}.", $path);
        $webpData = $image->toWebp($this->defaultQuality);
        Storage::put($resizedPath, $webpData);
        
        return $resizedPath;
    }
}
