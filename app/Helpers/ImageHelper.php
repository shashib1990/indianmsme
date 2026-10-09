<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

/**
 * ImageHelper — Centralised image compression and upload utility.
 *
 * Uses PHP GD (when available) to compress images while preserving quality.
 * Falls back to direct file copy if GD is not installed.
 *
 * Target: keep file size under 300 KB while maintaining visual quality.
 */
class ImageHelper
{
    /**
     * Target maximum file size in bytes (300 KB).
     */
    const TARGET_BYTES = 300 * 1024;

    /**
     * Quality steps to try (descending). JPEG/WebP use these directly.
     * PNG compression level is derived as: floor((100 - quality) / 10).
     */
    const QUALITY_STEPS = [85, 72, 60, 48, 38];

    /**
     * Compress and save an uploaded image.
     *
     * @param  UploadedFile  $file       The uploaded file instance.
     * @param  string        $directory  Relative directory under public_path() (e.g. 'uploads/categories').
     * @param  string|null   $oldPath    Optional old relative path to delete (e.g. 'uploads/categories/old.jpg').
     * @return string                    Relative path stored in DB (e.g. 'uploads/categories/xxxx.jpg').
     */
    public static function compressAndSave(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        // Ensure directory exists
        $fullDir = public_path($directory);
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        // Delete old image if provided
        if ($oldPath && file_exists(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }

        $ext       = strtolower($file->getClientOriginalExtension());
        $filename  = time() . '_' . uniqid() . '.' . $ext;
        $destPath  = $fullDir . '/' . $filename;
        $relPath   = $directory . '/' . $filename;

        // If GD is not available, just move the file as-is
        if (!extension_loaded('gd')) {
            $file->move($fullDir, $filename);
            return $relPath;
        }

        // Read source image into GD resource
        $sourcePath = $file->getPathname();
        $image      = self::createImageFromFile($sourcePath, $ext);

        if (!$image) {
            // Fallback: move file directly
            $file->move($fullDir, $filename);
            return $relPath;
        }

        // Auto-rotate based on EXIF orientation (JPEG only)
        if (in_array($ext, ['jpg', 'jpeg']) && function_exists('exif_read_data')) {
            $image = self::autoRotate($image, $sourcePath);
        }

        // Try each quality step until file size is under TARGET_BYTES
        foreach (self::QUALITY_STEPS as $quality) {
            self::saveWithQuality($image, $destPath, $ext, $quality);

            if (file_exists($destPath) && filesize($destPath) <= self::TARGET_BYTES) {
                break;
            }
        }

        // Final fallback: if still too large, save at lowest quality
        if (!file_exists($destPath)) {
            self::saveWithQuality($image, $destPath, $ext, self::QUALITY_STEPS[count(self::QUALITY_STEPS) - 1]);
        }

        imagedestroy($image);

        return $relPath;
    }

    /**
     * Create a GD image resource from file path based on extension.
     */
    private static function createImageFromFile(string $path, string $ext): \GdImage|bool|null
    {
        $image = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png'         => @imagecreatefrompng($path),
            'webp'        => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            'gif'         => @imagecreatefromgif($path),
            default       => null,
        };

        if ($image && in_array($ext, ['png', 'webp'])) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        return $image;
    }

    /**
     * Save GD image to destination with given quality.
     *
     * JPEG/WebP: quality 0-100 (higher = better).
     * PNG: compression 0-9 (higher = more compressed). Derived as floor((100-q)/10).
     * GIF: no quality parameter.
     */
    private static function saveWithQuality(\GdImage $image, string $destPath, string $ext, int $quality): void
    {
        if (in_array($ext, ['png', 'webp'])) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        match ($ext) {
            'jpg', 'jpeg' => imagejpeg($image, $destPath, $quality),
            'png'         => imagepng($image, $destPath, (int) floor((100 - $quality) / 10)),
            'webp'        => function_exists('imagewebp') ? imagewebp($image, $destPath, $quality) : imagejpeg($image, $destPath, $quality),
            'gif'         => imagegif($image, $destPath),
            default       => imagejpeg($image, $destPath, $quality),
        };
    }

    /**
     * Auto-rotate image based on EXIF orientation data.
     */
    private static function autoRotate(\GdImage $image, string $path): \GdImage
    {
        try {
            $exif = @exif_read_data($path);
            $orientation = $exif['Orientation'] ?? 1;

            return match ((int) $orientation) {
                3 => imagerotate($image, 180, 0),
                6 => imagerotate($image, -90, 0),
                8 => imagerotate($image, 90, 0),
                default => $image,
            };
        } catch (\Throwable) {
            return $image;
        }
    }

    /**
     * Delete an image file safely.
     */
    public static function delete(?string $relativePath): void
    {
        if ($relativePath && file_exists(public_path($relativePath))) {
            @unlink(public_path($relativePath));
        }
    }
}
