<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rejects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->string('name'); // nama barang rusak
            $table->integer('quantity')->default(1);
            $table->text('description')->nullable(); // catatan kerusakan
            $table->enum('condition', ['rusak ringan', 'rusak berat', 'tidak bisa digunakan'])->default('rusak ringan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rejects');
    }
};
