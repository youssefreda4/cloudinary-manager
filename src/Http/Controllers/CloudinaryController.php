<?php

namespace CloudinaryManager\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use CloudinaryManager\CloudinaryService;
use CloudinaryManager\Exceptions\CloudinaryException;
use CloudinaryManager\Http\Requests\UploadImageRequest;
use CloudinaryManager\Http\Requests\UploadVideoRequest;
use CloudinaryManager\Http\Resources\UploadResource;

class CloudinaryController extends Controller
{
    public function __construct(
        protected CloudinaryService $cloudinaryService
    ) {}

    /**
     * Upload an image to Cloudinary
     *
     * @param UploadImageRequest $request
     * @return JsonResponse
     */
    public function uploadImage(UploadImageRequest $request): JsonResponse
    {
        try {
            $result = $this->cloudinaryService->uploadImage(
                file: $request->file('image'),
                folder: $request->input('folder'),
                options: $request->input('options', [])
            );

            return $this->successResponse(
                data: new UploadResource($result),
                message: 'Image uploaded successfully',
                statusCode: 201
            );

        } catch (CloudinaryException $e) {
            Log::error('Cloudinary image upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                message: 'Failed to upload image',
                errors: ['upload' => $e->getMessage()],
                statusCode: 500
            );
        }
    }

    /**
     * Upload a video to Cloudinary
     *
     * @param UploadVideoRequest $request
     * @return JsonResponse
     */
    public function uploadVideo(UploadVideoRequest $request): JsonResponse
    {
        try {
            $result = $this->cloudinaryService->uploadVideo(
                file: $request->file('video'),
                folder: $request->input('folder'),
                options: $request->input('options', [])
            );

            return $this->successResponse(
                data: new UploadResource($result),
                message: 'Video uploaded successfully',
                statusCode: 201
            );

        } catch (CloudinaryException $e) {
            Log::error('Cloudinary video upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                message: 'Failed to upload video',
                errors: ['upload' => $e->getMessage()],
                statusCode: 500
            );
        }
    }

    /**
     * Upload multiple images in batch
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadBatchImages(Request $request): JsonResponse
    {
        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'folder' => 'sometimes|string|max:255'
        ]);

        try {
            $results = [];
            $errors = [];

            foreach ($request->file('images') as $index => $image) {
                try {
                    $result = $this->cloudinaryService->uploadImage(
                        file: $image,
                        folder: $request->input('folder')
                    );
                    $results[] = new UploadResource($result);
                } catch (CloudinaryException $e) {
                    $errors[] = [
                        'index' => $index,
                        'filename' => $image->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                }
            }

            if (empty($results) && !empty($errors)) {
                return $this->errorResponse(
                    message: 'All uploads failed',
                    errors: $errors,
                    statusCode: 500
                );
            }

            return $this->successResponse(
                data: [
                    'uploaded' => $results,
                    'failed' => $errors,
                    'summary' => [
                        'total' => count($request->file('images')),
                        'successful' => count($results),
                        'failed' => count($errors)
                    ]
                ],
                message: 'Batch upload completed',
                statusCode: 201
            );

        } catch (\Exception $e) {
            Log::error('Batch image upload failed', [
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                message: 'Batch upload failed',
                errors: ['batch' => $e->getMessage()],
                statusCode: 500
            );
        }
    }

    /**
     * Delete an image from Cloudinary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteImage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'public_id' => 'required|string|max:255'
            ]);

            $deleted = $this->cloudinaryService->deleteImage(
                publicId: $validated['public_id']
            );

            if (!$deleted) {
                return $this->errorResponse(
                    message: 'Image not found or already deleted',
                    statusCode: 404
                );
            }

            return $this->successResponse(
                data: ['public_id' => $validated['public_id']],
                message: 'Image deleted successfully'
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                message: 'Validation failed',
                errors: $e->errors(),
                statusCode: 422
            );
        } catch (CloudinaryException $e) {
            Log::error('Cloudinary image delete failed', [
                'public_id' => $request->input('public_id'),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                message: 'Failed to delete image',
                errors: ['delete' => $e->getMessage()],
                statusCode: 500
            );
        }
    }

    /**
     * Delete a video from Cloudinary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteVideo(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'public_id' => 'required|string|max:255'
            ]);

            $deleted = $this->cloudinaryService->deleteVideo(
                publicId: $validated['public_id']
            );

            if (!$deleted) {
                return $this->errorResponse(
                    message: 'Video not found or already deleted',
                    statusCode: 404
                );
            }

            return $this->successResponse(
                data: ['public_id' => $validated['public_id']],
                message: 'Video deleted successfully'
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                message: 'Validation failed',
                errors: $e->errors(),
                statusCode: 422
            );
        } catch (CloudinaryException $e) {
            Log::error('Cloudinary video delete failed', [
                'public_id' => $request->input('public_id'),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                message: 'Failed to delete video',
                errors: ['delete' => $e->getMessage()],
                statusCode: 500
            );
        }
    }

    /**
     * Delete multiple resources by public IDs
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteBatch(Request $request): JsonResponse
    {
        $request->validate([
            'public_ids' => 'required|array|min:1|max:50',
            'public_ids.*' => 'required|string|max:255',
            'resource_type' => 'required|in:image,video'
        ]);

        try {
            $results = [];
            $errors = [];

            $deleteMethod = 'delete' . ucfirst($request->input('resource_type'));

            foreach ($request->input('public_ids') as $publicId) {
                try {
                    $deleted = $this->cloudinaryService->$deleteMethod($publicId);
                    $results[] = [
                        'public_id' => $publicId,
                        'deleted' => $deleted
                    ];
                } catch (CloudinaryException $e) {
                    $errors[] = [
                        'public_id' => $publicId,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $this->successResponse(
                data: [
                    'deleted' => $results,
                    'failed' => $errors,
                    'summary' => [
                        'total' => count($request->input('public_ids')),
                        'successful' => count($results),
                        'failed' => count($errors)
                    ]
                ],
                message: 'Batch delete completed'
            );

        } catch (\Exception $e) {
            Log::error('Batch delete failed', [
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                message: 'Batch delete failed',
                errors: ['batch' => $e->getMessage()],
                statusCode: 500
            );
        }
    }

    /**
     * Delete resources by folder prefix
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteByPrefix(Request $request): JsonResponse
    {
        $request->validate([
            'prefix' => 'required|string|max:255',
            'resource_type' => 'required|in:image,video'
        ]);

        try {
            $result = $this->cloudinaryService->deleteByPrefix(
                prefix: $request->input('prefix'),
                resourceType: $request->input('resource_type')
            );

            return $this->successResponse(
                data: $result,
                message: 'Resources deleted successfully'
            );

        } catch (CloudinaryException $e) {
            Log::error('Delete by prefix failed', [
                'prefix' => $request->input('prefix'),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                message: 'Failed to delete resources',
                errors: ['delete' => $e->getMessage()],
                statusCode: 500
            );
        }
    }

    /**
     * Get resource information from Cloudinary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getResourceInfo(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'public_id' => 'required|string|max:255',
                'resource_type' => 'sometimes|in:image,video,raw'
            ]);

            $info = $this->cloudinaryService->getResourceInfo(
                publicId: $validated['public_id'],
                resourceType: $validated['resource_type'] ?? 'image'
            );

            return $this->successResponse(
                data: $info,
                message: 'Resource information retrieved successfully'
            );

        } catch (ValidationException $e) {
            return $this->errorResponse(
                message: 'Validation failed',
                errors: $e->errors(),
                statusCode: 422
            );
        } catch (CloudinaryException $e) {
            Log::error('Get resource info failed', [
                'public_id' => $request->input('public_id'),
                'error' => $e->getMessage()
            ]);

            return $this->errorResponse(
                message: 'Failed to retrieve resource information',
                errors: ['info' => $e->getMessage()],
                statusCode: 404
            );
        }
    }

    /**
     * Generate a transformed URL for a resource
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateUrl(Request $request): JsonResponse
    {
        $request->validate([
            'public_id' => 'required|string|max:255',
            'transformations' => 'sometimes|array',
            'resource_type' => 'sometimes|in:image,video,raw'
        ]);

        try {
            $url = $this->cloudinaryService->generateUrl(
                publicId: $request->input('public_id'),
                transformations: $request->input('transformations', []),
                resourceType: $request->input('resource_type', 'image')
            );

            return $this->successResponse(
                data: [
                    'public_id' => $request->input('public_id'),
                    'url' => $url,
                    'transformations' => $request->input('transformations', [])
                ],
                message: 'URL generated successfully'
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Failed to generate URL',
                errors: ['url' => $e->getMessage()],
                statusCode: 500
            );
        }
    }

    /**
     * List resources in a folder
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listResources(Request $request): JsonResponse
    {
        $request->validate([
            'folder' => 'sometimes|string|max:255',
            'resource_type' => 'sometimes|in:image,video,raw',
            'max_results' => 'sometimes|integer|min:1|max:500'
        ]);

        try {
            // This would require implementing listResources in CloudinaryService
            return $this->successResponse(
                data: [],
                message: 'Feature to be implemented'
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                message: 'Failed to list resources',
                errors: ['list' => $e->getMessage()],
                statusCode: 500
            );
        }
    }

    /**
     * Success response helper
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Error response helper
     *
     * @param string $message
     * @param array|null $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message = 'Error',
        ?array $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}