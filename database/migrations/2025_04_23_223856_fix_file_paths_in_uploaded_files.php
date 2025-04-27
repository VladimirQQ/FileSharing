<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class FixFilePathsInUploadedFiles extends Migration
{
    public function up()
    {
        // Для записей без 'private/uploads/' в начале
        DB::table('uploaded_files')
            ->where('path', 'NOT LIKE', 'private/uploads/%')
            ->update([
                'path' => DB::raw("CONCAT('private/uploads/', path)")
            ]);
            
        // Для записей с 'uploads/' вместо 'private/uploads/'
        DB::table('uploaded_files')
            ->where('path', 'LIKE', 'uploads/%')
            ->update([
                'path' => DB::raw("REPLACE(path, 'uploads/', 'private/uploads/')")
            ]);
    }

    public function down()
    {
        // Откат изменений (опционально)
        DB::table('uploaded_files')
            ->where('path', 'LIKE', 'private/uploads/%')
            ->update([
                'path' => DB::raw("REPLACE(path, 'private/uploads/', '')")
            ]);
    }
}