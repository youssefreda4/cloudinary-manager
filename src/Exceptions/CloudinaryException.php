<?php

namespace CloudinaryManager\Exceptions;

use Exception;

class CloudinaryException extends Exception
{
    public static function uploadFailed(string $reason): self
    {
        return new self("Upload failed: {$reason}");
    }

    public static function deleteFailed(string $reason): self
    {
        return new self("Delete failed: {$reason}");
    }

    public static function invalidConfiguration(string $field): self
    {
        return new self("Invalid Cloudinary configuration: {$field} is missing");
    }
}