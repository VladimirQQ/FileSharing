<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('original_name');
            $table->string('path');
            $table->unsignedInteger('size');
            $table->string('mime_type');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
