<?php

namespace CloudinaryManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = config('cloudinary.validation.video.max_size', 20000);

        return [
            'video' => "required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo|max:{$maxSize}",
            'folder' => 'sometimes|string|max:255',
            'options' => 'sometimes|array',
        ];
    }

    public function messages(): array
    {
        return [
            'video.required' => 'Please select a video to upload',
            'video.mimetypes' => 'Only MP4, MOV and AVI videos are allowed',
            'video.max' => 'Video size must not exceed :max KB',
        ];
    }
}