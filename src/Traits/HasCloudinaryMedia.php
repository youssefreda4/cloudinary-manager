<?php

namespace CloudinaryManager\Traits;

use Illuminate\Http\UploadedFile;
use CloudinaryManager\Facades\Cloudinary;
use CloudinaryManager\DTO\UploadResult;

trait HasCloudinaryMedia
{
    public function uploadToCloudinary(
        UploadedFile $file,
        string $type = 'image',
        ?string $folder = null
    ): UploadResult {
        $method = 'upload' . ucfirst($type);
        return Cloudinary::$method($file, $folder);
    }

    public function deleteFromCloudinary(string $publicId, string $type = 'image'): bool
    {
        $method = 'delete' . ucfirst($type);
        return Cloudinary::$method($publicId);
    }

    public function getCloudinaryUrl(
        string $publicId,
        array $transformations = [],
        string $resourceType = 'image'
    ): string {
        return Cloudinary::generateUrl($publicId, $transformations, $resourceType);
    }
}
