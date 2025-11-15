<?php

namespace CloudinaryManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = config('cloudinary.validation.image.max_size', 2048);
        $mimeTypes = implode(',', config('cloudinary.validation.image.mime_types', []));

        return [
            'image' => "required|file|mimes:jpeg,png,jpg,gif,webp|max:{$maxSize}",
            'folder' => 'sometimes|string|max:255',
            'options' => 'sometimes|array',
            'options.use_filename' => 'sometimes|boolean',
            'options.unique_filename' => 'sometimes|boolean',
            'options.overwrite' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Please select an image to upload',
            'image.file' => 'The uploaded file must be a valid image',
            'image.mimes' => 'Only JPEG, PNG, JPG, GIF and WebP images are allowed',
            'image.max' => 'Image size must not exceed :max KB',
        ];
    }
}