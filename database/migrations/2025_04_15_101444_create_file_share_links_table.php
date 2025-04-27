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
        Schema::create('file_share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('uploaded_files')->onDelete('cascade');
            $table->string('token')->unique();
            $table->string('password');
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_share_links');
    }
};
