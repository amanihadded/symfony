<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploadService
{
    private SluggerInterface $slugger;
    private string $uploadDirectory;

    public function __construct(
        SluggerInterface $slugger,
        string $uploadDirectory = 'uploads'
    ) {
        $this->slugger = $slugger;
        $this->uploadDirectory = $uploadDirectory;
    }

    /**
     * Upload an image file and return the filename.
     */
    public function upload(UploadedFile $file, string $subdirectory = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDir = $this->getTargetDirectory($subdirectory);

        $file->move($targetDir, $newFilename);

        return ($subdirectory ? $subdirectory . '/' : '') . $newFilename;
    }

    /**
     * Remove an uploaded image file.
     */
    public function remove(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $filepath = $this->getUploadRootDir() . '/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    private function getTargetDirectory(string $subdirectory): string
    {
        $dir = $this->getUploadRootDir();
        if ($subdirectory) {
            $dir .= '/' . $subdirectory;
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    private function getUploadRootDir(): string
    {
        // Points to public/uploads
        return dirname(__DIR__, 2) . '/public/' . $this->uploadDirectory;
    }
}