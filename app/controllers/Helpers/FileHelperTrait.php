<?php

declare(strict_types=1);

namespace App\Controllers\Helpers;

use Psr\Http\Message\UploadedFileInterface;

trait FileHelperTrait
{
    /**
     * Moves the uploaded file to the target directory and returns the new filename.
     */
    protected function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile): string
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    /**
     * Validates if the uploaded file is an image.
     */
    protected function isImage(UploadedFileInterface $uploadedFile): bool
    {
        $mimeType = $uploadedFile->getClientMediaType();
        if ($mimeType && str_starts_with($mimeType, 'image/')) {
            return true;
        }

        // Fallback to extension check if mime type is missing or potentially weird
        $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
        return in_array($extension, $allowedExtensions);
    }
}
