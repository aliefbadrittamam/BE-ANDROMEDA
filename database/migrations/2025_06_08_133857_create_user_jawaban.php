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
        Schema::create('user_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kuis_id')->constrained('kuis')->onDelete('cascade');
            $table->foreignId('soal_id')->constrained('soal')->onDelete('cascade');
            $table->enum('jawaban_user', ['A', 'B', 'C', 'D']);
            $table->boolean('is_correct')->default(false);
            $table->dateTime('waktu_jawab')->useCurrent();
            
            $table->unique(['user_id', 'kuis_id', 'soal_id']);
            $table->index('user_id');
            $table->index('kuis_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_jawaban');
    }
};
