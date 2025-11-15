<?php

namespace CloudinaryManager;

use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use CloudinaryManager\DTO\UploadResult;
use CloudinaryManager\Exceptions\CloudinaryException;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    protected UploadApi $uploadApi;
    protected AdminApi $adminApi;
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'cloud_name' => config('cloudinary.cloud_name'),
            'api_key' => config('cloudinary.api_key'),
            'api_secret' => config('cloudinary.api_secret'),
            'secure' => config('cloudinary.secure', true),
        ], $config);

        $this->configure();
        $this->uploadApi = new UploadApi();
        $this->adminApi = new AdminApi();
    }

    protected function configure(): void
    {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => $this->config['cloud_name'],
                'api_key' => $this->config['api_key'],
                'api_secret' => $this->config['api_secret'],
            ],
            'url' => [
                'secure' => $this->config['secure']
            ]
        ]);
    }

    public function uploadImage(
        UploadedFile $file,
        ?string $folder = null,
        array $options = []
    ): UploadResult {
        return $this->upload($file, 'image', $folder, $options);
    }

    public function uploadVideo(
        UploadedFile $file,
        ?string $folder = null,
        array $options = []
    ): UploadResult {
        return $this->upload($file, 'video', $folder, $options);
    }

    public function upload(
        UploadedFile $file,
        string $resourceType = 'auto',
        ?string $folder = null,
        array $options = []
    ): UploadResult {
        try {
            $uploadOptions = array_merge([
                'folder' => $folder ?? config("cloudinary.folders.{$resourceType}", $resourceType . 's'),
                'resource_type' => $resourceType,
                'use_filename' => $options['use_filename'] ?? false,
                'unique_filename' => $options['unique_filename'] ?? true,
                'overwrite' => $options['overwrite'] ?? false,
            ], $options);

            $result = $this->uploadApi->upload(
                $file->getRealPath(),
                $uploadOptions
            );

            return UploadResult::fromArray($result);
        } catch (\Exception $e) {
            throw new CloudinaryException(
                "Failed to upload {$resourceType}: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function delete(string $publicId, string $resourceType = 'image'): bool
    {
        try {
            $result = $this->uploadApi->destroy($publicId, [
                'resource_type' => $resourceType,
                'invalidate' => true,
            ]);

            return $result['result'] === 'ok';
        } catch (\Exception $e) {
            throw new CloudinaryException(
                "Failed to delete {$resourceType}: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function deleteImage(string $publicId): bool
    {
        return $this->delete($publicId, 'image');
    }

    public function deleteVideo(string $publicId): bool
    {
        return $this->delete($publicId, 'video');
    }

    public function deleteByPrefix(string $prefix, string $resourceType = 'image'): array
    {
        try {
            $result = $this->adminApi->deleteAssetsByPrefix($prefix, [
                'resource_type' => $resourceType,
            ]);

            return $result;
        } catch (\Exception $e) {
            throw new CloudinaryException(
                "Failed to delete by prefix: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function getResourceInfo(string $publicId, string $resourceType = 'image'): array
    {
        try {
            return $this->adminApi->asset($publicId, [
                'resource_type' => $resourceType,
            ]);
        } catch (\Exception $e) {
            throw new CloudinaryException(
                "Failed to get resource info: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function generateUrl(
        string $publicId,
        array $transformations = [],
        string $resourceType = 'image'
    ): string {
        $url = "https://res.cloudinary.com/{$this->config['cloud_name']}/{$resourceType}/upload/";

        if (!empty($transformations)) {
            $transformationString = $this->buildTransformationString($transformations);
            $url .= $transformationString . '/';
        }

        $url .= $publicId;

        return $url;
    }

    protected function buildTransformationString(array $transformations): string
    {
        $parts = [];

        foreach ($transformations as $key => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $parts[] = "{$key}_{$value}";
        }

        return implode(',', $parts);
    }
}
