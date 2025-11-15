<?php

namespace CloudinaryManager\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use CloudinaryManager\DTO\UploadResult;

class UploadResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var UploadResult $this->resource */
        return [
            'public_id' => $this->resource->publicId,
            'url' => $this->resource->secureUrl,
            'secure_url' => $this->resource->secureUrl,
            'resource_type' => $this->resource->resourceType,
            'format' => $this->resource->format,
            'width' => $this->resource->width,
            'height' => $this->resource->height,
            'bytes' => $this->resource->bytes,
            'size_kb' => round($this->resource->bytes / 1024, 2),
            'created_at' => $this->resource->createdAt,
        ];
    }
}