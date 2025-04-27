<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('file_share_links', function (Blueprint $table) {
            $table->string('token')->unique()->change(); //токен уникальный
            $table->boolean('is_used')->default(false); //отслеживание использования
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('file_share_links', function (Blueprint $table) {
            //
        });
    }
};
