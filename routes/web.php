<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
     return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
     // Загрузка файлов
     Route::post('/upload', [FileController::class, 'upload'])->name('files.upload');

     // Генерация ссылки
     Route::post('/files/{file}/generate-link', [FileController::class, 'generateLink'])
          ->name('files.generate-link');

     // Скачивание по ссылке (GET - форма, POST - обработка)
     Route::get('/download/{token}', [FileController::class, 'showDownloadPage'])
          ->name('files.download.link');

     Route::post('/download/{token}', [FileController::class, 'processDownload'])
          ->name('files.download.process');

     // Прямое скачивание (для авторизованных пользователей)
     Route::get('/files/{file}/download', [FileController::class, 'download'])
          ->name('files.download');

     Route::post('/files/{file}/generate-link', [FileController::class, 'generateLink'])
          ->name('files.generate-link');

     Route::delete('/files/{file}', [FileController::class, 'delete'])
          ->name('files.delete')
          ->middleware('auth');
});
