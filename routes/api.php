<?php

use Illuminate\Support\Facades\Route;
use CloudinaryManager\Http\Controllers\CloudinaryController;

Route::prefix('api/cloudinary')
    ->middleware(['api'])
    ->group(function () {
        Route::post('/upload/image', [CloudinaryController::class, 'uploadImage']);
        Route::post('/upload/video', [CloudinaryController::class, 'uploadVideo']);
        Route::delete('/delete/image', [CloudinaryController::class, 'deleteImage']);
        Route::delete('/delete/video', [CloudinaryController::class, 'deleteVideo']);
        Route::get('/resource/info', [CloudinaryController::class, 'getResourceInfo']);
    });