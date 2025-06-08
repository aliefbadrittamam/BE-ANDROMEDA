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
        Schema::create('kuis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kuis', 200);
            $table->text('deskripsi')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->integer('durasi_menit')->default(60);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->foreignId('admin_id')->constrained('admin')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('admin_id');
            $table->index('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuis');
    }
};
