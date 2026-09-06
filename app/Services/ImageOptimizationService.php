<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class ImageOptimizationService
{
    /**
     * Available responsive sizes for product images
     */
    public const RESPONSIVE_SIZES = [
        'thumbnail' => ['width' => 300, 'height' => 300, 'suffix' => 'thumb'],
        'small'     => ['width' => 480, 'height' => 480, 'suffix' => 'sm'],
        'medium'    => ['width' => 768, 'height' => 768, 'suffix' => 'md'],
        'large'     => ['width' => 1200, 'height' => 1200, 'suffix' => 'lg'],
        'original'  => ['width' => null, 'height' => null, 'suffix' => ''],
    ];

    /**
     * WebP quality setting (1-100)
     */
    public const WEBP_QUALITY = 80;

    /**
     * JPEG quality for fallback
     */
    public const JPEG_QUALITY = 85;

    /**
     * Generate all responsive sizes + WebP for an uploaded image
     *
     * @param string $storedPath Path relative to storage disk (e.g., 'products/abc.jpg')
     * @param string $disk Storage disk name
     * @return array Generated paths keyed by size
     */
    public function generateResponsiveImages(string $storedPath, string $disk = 'public'): array
    {
        // Image conversion is optional. Keep uploads and storefront rendering
        // working when the server does not have the GD extension enabled.
        if (! extension_loaded('gd')) {
            \Log::warning('Skipped responsive image generation because the GD extension is not enabled.', [
                'path' => $storedPath,
            ]);

            return [];
        }

        if (!Storage::disk($disk)->exists($storedPath)) {
            return [];
        }

        $originalPath = Storage::disk($disk)->path($storedPath);
        $directory = dirname($storedPath);
        $filename = pathinfo($storedPath, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($storedPath, PATHINFO_EXTENSION));

        $generated = [];

        foreach (self::RESPONSIVE_SIZES as $sizeKey => $size) {
            $suffix = $size['suffix'];
            $outputFilename = $suffix ? "{$filename}_{$suffix}.webp" : "{$filename}.webp";
            $outputPath = "{$directory}/{$outputFilename}";
            $outputFullPath = Storage::disk($disk)->path($outputPath);

            try {
                $img = Image::make($originalPath);

                if ($size['width'] && $size['height']) {
                    // Resize maintaining aspect ratio, fit within bounds
                    $img->fit($size['width'], $size['height'], function ($constraint) {
                        $constraint->upsize();
                    });
                }

                // Save as WebP
                $img->encode('webp', self::WEBP_QUALITY)->save($outputFullPath);
                $generated[$sizeKey] = $outputPath;

                // Also generate JPEG fallback for original size
                if ($sizeKey === 'original') {
                    $jpegPath = "{$directory}/{$filename}_fallback.jpg";
                    $jpegFullPath = Storage::disk($disk)->path($jpegPath);
                    Image::make($originalPath)->encode('jpg', self::JPEG_QUALITY)->save($jpegFullPath);
                    $generated['fallback_jpg'] = $jpegPath;
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to generate responsive image {$sizeKey} for {$storedPath}: " . $e->getMessage());
            }
        }

        return $generated;
    }

    /**
     * Generate responsive images for a product (all its images)
     */
    public function generateProductImages(int $productId): void
    {
        $product = \App\Models\Product::with('images')->find($productId);
        if (!$product) {
            return;
        }

        foreach ($product->images as $image) {
            $this->generateResponsiveImages($image->image_url);
        }
    }

    /**
     * Get responsive image HTML with srcset/sizes and WebP fallback
     *
     * @param string $originalPath Original image path in storage
     * @param array $options Options: alt, class, sizes, loading
     * @return string HTML img tag with picture element
     */
    public function getResponsiveImageHtml(string $originalPath, array $options = []): string
    {
        if (!$originalPath) {
            return '';
        }

        $alt = $options['alt'] ?? '';
        $class = $options['class'] ?? '';
        $sizes = $options['sizes'] ?? '(max-width: 480px) 300px, (max-width: 768px) 480px, (max-width: 1200px) 768px, 1200px';
        $loading = $options['loading'] ?? 'lazy';

        // Build WebP srcset
        $webpSources = [];
        foreach (self::RESPONSIVE_SIZES as $sizeKey => $size) {
            if ($sizeKey === 'original') continue;
            
            $suffix = $size['suffix'];
            $webpPath = $this->getWebpPath($originalPath, $suffix);
            
            if ($webpPath && Storage::disk('public')->exists($webpPath)) {
                $webpSources[] = Storage::url($webpPath) . " {$size['width']}w";
            }
        }

        // Fallback JPEG for original
        $fallbackJpg = $this->getFallbackJpgPath($originalPath);
        $fallbackUrl = $fallbackJpg && Storage::disk('public')->exists($fallbackJpg) 
            ? Storage::url($fallbackJpg) 
            : Storage::url($originalPath);

        $srcset = implode(', ', $webpSources);
        $src = $fallbackUrl;

        // Build picture element with WebP sources and JPEG fallback
        $html = '<picture>';
        
        if ($srcset) {
            $html .= '<source type="image/webp" srcset="' . $srcset . '" sizes="' . $sizes . '">';
        }
        
        $html .= '<img src="' . $src . '" alt="' . e($alt) . '"';
        if ($class) {
            $html .= ' class="' . $class . '"';
        }
        if ($sizes) {
            $html .= ' sizes="' . $sizes . '"';
        }
        if ($loading) {
            $html .= ' loading="' . $loading . '"';
        }
        $html .= '>';
        $html .= '</picture>';

        return $html;
    }

    /**
     * Get WebP path for a given size
     */
    private function getWebpPath(string $originalPath, string $suffix): ?string
    {
        $directory = dirname($originalPath);
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);
        return "{$directory}/{$filename}_{$suffix}.webp";
    }

    /**
     * Get fallback JPEG path
     */
    private function getFallbackJpgPath(string $originalPath): ?string
    {
        $directory = dirname($originalPath);
        $filename = pathinfo($originalPath, PATHINFO_FILENAME);
        return "{$directory}/{$filename}_fallback.jpg";
    }

    /**
     * Simple helper for basic responsive image (used in cards, thumbnails)
     * Returns just the WebP URL for a specific size, with fallback
     */
    public function getResponsiveUrl(string $originalPath, string $sizeKey = 'small'): string
    {
        if (!$originalPath || !isset(self::RESPONSIVE_SIZES[$sizeKey])) {
            return $originalPath ? Storage::url($originalPath) : asset('default.jpg');
        }

        $suffix = self::RESPONSIVE_SIZES[$sizeKey]['suffix'];
        $webpPath = $this->getWebpPath($originalPath, $suffix);
        
        if ($webpPath && Storage::disk('public')->exists($webpPath)) {
            return Storage::url($webpPath);
        }

        // Fallback to original
        return Storage::url($originalPath);
    }

    /**
     * Get thumbnail URL (300x300 WebP)
     */
    public function getThumbnailUrl(string $originalPath): string
    {
        return $this->getResponsiveUrl($originalPath, 'thumbnail');
    }

    /**
     * Get small URL (480x480 WebP)
     */
    public function getSmallUrl(string $originalPath): string
    {
        return $this->getResponsiveUrl($originalPath, 'small');
    }

    /**
     * Get medium URL (768x768 WebP)
     */
    public function getMediumUrl(string $originalPath): string
    {
        return $this->getResponsiveUrl($originalPath, 'medium');
    }
}
