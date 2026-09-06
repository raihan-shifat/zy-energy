<?php

use App\Services\ImageOptimizationService;

if (! function_exists('getResponsiveImageHtml')) {
    /**
     * Get responsive image HTML with WebP srcset and JPEG fallback
     *
     * @param string $originalPath Path in storage (e.g., 'products/image.jpg')
     * @param array $options Options: alt, class, sizes, loading
     * @return string HTML picture element
     */
    function getResponsiveImageHtml(string $originalPath, array $options = []): string
    {
        return app(ImageOptimizationService::class)->getResponsiveImageHtml($originalPath, $options);
    }
}

if (! function_exists('getResponsiveImageUrl')) {
    /**
     * Get responsive image URL for a specific size (WebP if available)
     *
     * @param string $originalPath Path in storage
     * @param string $sizeKey Size key: thumbnail, small, medium, large, original
     * @return string URL
     */
    function getResponsiveImageUrl(string $originalPath, string $sizeKey = 'small'): string
    {
        return app(ImageOptimizationService::class)->getResponsiveUrl($originalPath, $sizeKey);
    }
}

if (! function_exists('getThumbnailUrl')) {
    /**
     * Get thumbnail URL (300x300 WebP)
     */
    function getThumbnailUrl(string $originalPath): string
    {
        return app(ImageOptimizationService::class)->getThumbnailUrl($originalPath);
    }
}

if (! function_exists('getSmallImageUrl')) {
    /**
     * Get small image URL (480x480 WebP)
     */
    function getSmallImageUrl(string $originalPath): string
    {
        return app(ImageOptimizationService::class)->getSmallUrl($originalPath);
    }
}

if (! function_exists('getMediumImageUrl')) {
    /**
     * Get medium image URL (768x768 WebP)
     */
    function getMediumImageUrl(string $originalPath): string
    {
        return app(ImageOptimizationService::class)->getMediumUrl($originalPath);
    }
}

if (! function_exists('generateResponsiveImages')) {
    /**
     * Generate all responsive sizes + WebP for an image
     *
     * @param string $storedPath Path in storage
     * @return array Generated paths
     */
    function generateResponsiveImages(string $storedPath): array
    {
        return app(ImageOptimizationService::class)->generateResponsiveImages($storedPath);
    }
}