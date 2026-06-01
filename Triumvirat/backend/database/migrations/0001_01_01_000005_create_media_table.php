<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
     
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('url');
            $table->enum('type', ['image', 'video']);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            // Clé étrangère
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });
    }

     
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};