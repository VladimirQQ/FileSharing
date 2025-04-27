<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileShareController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
     Route::post('/logout', [AuthController::class, 'logout']);

     Route::get('/files', [FileController::class, 'index']);
     Route::post('/files', [FileController::class, 'upload']);
     Route::get('/files/{id}/download', [FileController::class, 'download']);


     Route::get('/check-link/{token}', [FileController::class, 'checkLink']);
});

