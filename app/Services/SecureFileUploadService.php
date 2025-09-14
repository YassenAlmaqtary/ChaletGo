<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SecureFileUploadService
{
    /**
     * Allowed image MIME types
     */
    protected array $allowedImageTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Allowed file extensions
     */
    protected array $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp'
    ];

    /**
     * Maximum file size in bytes (5MB)
     */
    protected int $maxFileSize = 5 * 1024 * 1024;

    /**
     * Maximum image dimensions
     */
    protected int $maxWidth = 2048;
    protected int $maxHeight = 2048;

    /**
     * Upload and secure an image file
     */
    public function uploadImage(UploadedFile $file, string $directory = 'images'): array
    {
        // Validate file
        $this->validateFile($file);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);

        // Process and optimize image
        $processedImage = $this->processImage($file);

        // Store the processed image
        $path = Storage::disk('public')->put(
            $directory . '/' . $filename,
            $processedImage
        );

        if (!$path) {
            throw new \Exception('فشل في رفع الملف');
        }

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'filename' => $filename,
            'size' => strlen($processedImage),
        ];
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check if file is valid
        if (!$file->isValid()) {
            throw new \Exception('الملف غير صالح');
        }

        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت');
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedImageTypes)) {
            throw new \Exception('نوع الملف غير مدعوم. يُسمح فقط بـ: ' . implode(', ', $this->allowedImageTypes));
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new \Exception('امتداد الملف غير مدعوم');
        }

        // Check for malicious content
        $this->scanForMaliciousContent($file);
    }

    /**
     * Generate secure filename
     */
    protected function generateSecureFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return Str::random(40) . '.' . $extension;
    }

    /**
     * Process and optimize image
     */
    protected function processImage(UploadedFile $file): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getPathname());

        // Get original dimensions
        $width = $image->width();
        $height = $image->height();

        // Resize if too large
        if ($width > $this->maxWidth || $height > $this->maxHeight) {
            $image->scale(
                width: min($width, $this->maxWidth),
                height: min($height, $this->maxHeight)
            );
        }

        // Remove EXIF data for privacy
        $image->exif();

        // Optimize quality
        return $image->toJpeg(85)->toString();
    }

    /**
     * Scan file for malicious content
     */
    protected function scanForMaliciousContent(UploadedFile $file): void
    {
        $content = file_get_contents($file->getPathname());

        // Check for PHP code
        if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
            throw new \Exception('الملف يحتوي على كود غير آمن');
        }

        // Check for script tags
        if (preg_match('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi', $content)) {
            throw new \Exception('الملف يحتوي على كود JavaScript غير آمن');
        }

        // Check for executable signatures
        $signatures = [
            "\x4D\x5A", // PE executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xCA\xFE\xBA\xBE", // Java class file
            "\xFE\xED\xFA\xCE", // Mach-O executable
        ];

        foreach ($signatures as $signature) {
            if (strpos($content, $signature) === 0) {
                throw new \Exception('الملف يحتوي على كود قابل للتنفيذ');
            }
        }
    }

    /**
     * Delete file securely
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Get file info securely
     */
    public function getFileInfo(string $path): ?array
    {
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return [
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'size' => Storage::disk('public')->size($path),
            'last_modified' => Storage::disk('public')->lastModified($path),
        ];
    }
}
