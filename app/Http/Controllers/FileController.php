<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\FileShareLink;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Добавьте этот импорт
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|max:10240']);
        
        $file = $request->file('file');
        $path = $file->store('private/uploads', 'local'); // Сохраняем в private/uploads
        
        // Сохраняем полный путь в БД
        UploadedFile::create([
            'user_id' => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path, // "private/uploads/filename.ext"
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ]);
        
        return back()->with('success', 'Файл успешно загружен!');
    }

    public function generateLink(Request $request, $fileId)
    {
        try {
            $request->validate([
                'password' => 'nullable|string|min:4'
            ]);

            $file = UploadedFile::where('id', $fileId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            do {
                $token = Str::random(40);
            } while (FileShareLink::where('token', $token)->exists());

            $password = $request->password ?? Str::random(10);

            $link = FileShareLink::create([
                'file_id' => $file->id,
                'token' => $token,
                'password' => Hash::make($password),
                'is_used' => false
            ]);

            return response()->json([
                'success' => true,
                'token' => $token,
                'link' => route('files.download.link', $token),
                'password' => $password,
                'is_one_time' => true,
                'expires_at' => now()->addDays(30)->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            Log::error('Generate link error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка генерации ссылки'
            ], 500);
        }
    }

    public function showDownloadPage($token)
{
    try {
        $fileLink = FileShareLink::with('file')->where('token', $token)->firstOrFail();

        if ($fileLink->is_used) {
            throw new \Exception("Эта ссылка уже была использована и больше недействительна");
        }

        $filePath = storage_path('app/private/uploads/'.basename($fileLink->file->path));
        
        if (!file_exists($filePath)) {
            throw new \Exception("Файл не найден");
        }

        return view('files.download', [
            'token' => $token,
            'file' => $fileLink->file,
            'fileLink' => $fileLink
        ]);

    } catch (\Exception $e) {
        return view('files.download', [
            'error_message' => $e->getMessage()
        ]);
    }
}

public function processDownload($token, Request $request)
{
    $request->validate(['password' => 'required|string']);

    DB::beginTransaction();
    try {
        $fileLink = FileShareLink::with('file')
            ->where('token', $token)
            ->lockForUpdate()
            ->firstOrFail();

        if ($fileLink->is_used) {
            throw new \Exception("Эта ссылка уже была использована");
        }

        if (!Hash::check($request->password, $fileLink->password)) {
            throw new \Exception("Неверный пароль");
        }

        $filePath = storage_path('app/private/uploads/'.basename($fileLink->file->path));
        
        if (!file_exists($filePath)) {
            throw new \Exception("Файл не найден");
        }

        $fileLink->update(['is_used' => true]);
        DB::commit();

        return response()->download($filePath, $fileLink->file->original_name);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Download failed: '.$e->getMessage());
        return back()->with('error', $e->getMessage());
    }
}

public function download(UploadedFile $file)
{
    // Проверка прав доступа
    if ($file->user_id !== Auth::id()) {
        abort(403, 'Доступ запрещен');
    }

    // Формируем правильный путь к файлу
    $filePath = storage_path('app/private/uploads/' . basename($file->path));
    
    // Проверяем существование файла
    if (!file_exists($filePath)) {
        Log::error('File not found', [
            'expected_path' => $filePath,
            'db_path' => $file->path,
            'user_id' => Auth::id()
        ]);
        abort(404, 'Файл не найден в хранилище');
    }

    // Скачиваем файл
    return response()->download(
        $filePath,
        $file->original_name
    );
}
public function delete(UploadedFile $file)
{
    // Проверка прав доступа
    if ($file->user_id !== Auth::id()) {
        abort(403);
    }

    // Удаляем связанные ссылки
    $file->shareLinks()->delete();
    
    // Удаляем файл из хранилища
    $filePath = storage_path('app/private/uploads/'.basename($file->path));
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Удаляем запись из БД
    $file->delete();

    return redirect()->back()->with('success', 'Файл успешно удален');
}

    public function checkLink($token)
    {
        $link = FileShareLink::with('file')->where('token', $token)->first();
        
        if (!$link) {
            return response()->json(['valid' => false, 'message' => 'Ссылка не найдена']);
        }
        
        return response()->json([
            'valid' => !$link->is_used,
            'is_used' => $link->is_used,
            'expired' => $link->created_at->diffInDays(now()) > 30,
            'file_name' => $link->file->original_name ?? 'Неизвестный файл'
        ]);
    }
}