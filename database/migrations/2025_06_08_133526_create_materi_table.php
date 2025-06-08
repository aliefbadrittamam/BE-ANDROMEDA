<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::create('materi', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 200);
            $table->text('konten_materi')->nullable();
            $table->text('gambar')->nullable();
            $table->text('video')->nullable();
            $table->foreignId('admin_id')->constrained('admin')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi');
    }
};
