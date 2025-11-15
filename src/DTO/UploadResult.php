<?php

namespace CloudinaryManager\DTO;

class UploadResult
{
    public function __construct(
        public readonly string $publicId,
        public readonly string $secureUrl,
        public readonly string $url,
        public readonly string $resourceType,
        public readonly int $bytes,
        public readonly int $width,
        public readonly int $height,
        public readonly string $format,
        public readonly string $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            publicId: $data['public_id'],
            secureUrl: $data['secure_url'],
            url: $data['url'],
            resourceType: $data['resource_type'],
            bytes: $data['bytes'],
            width: $data['width'] ?? 0,
            height: $data['height'] ?? 0,
            format: $data['format'],
            createdAt: $data['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'public_id' => $this->publicId,
            'secure_url' => $this->secureUrl,
            'url' => $this->url,
            'resource_type' => $this->resourceType,
            'bytes' => $this->bytes,
            'width' => $this->width,
            'height' => $this->height,
            'format' => $this->format,
            'created_at' => $this->createdAt,
        ];
    }
}
