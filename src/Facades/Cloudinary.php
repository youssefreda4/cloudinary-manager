<?php

namespace CloudinaryManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \CloudinaryManager\DTO\UploadResult uploadImage(\Illuminate\Http\UploadedFile $file, ?string $folder = null, array $options = [])
 * @method static \CloudinaryManager\DTO\UploadResult uploadVideo(\Illuminate\Http\UploadedFile $file, ?string $folder = null, array $options = [])
 * @method static bool deleteImage(string $publicId)
 * @method static bool deleteVideo(string $publicId)
 * @method static string generateUrl(string $publicId, array $transformations = [], string $resourceType = 'image')
 *
 * @see \CloudinaryManager\CloudinaryService
 */
class Cloudinary extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'cloudinary';
    }
}
