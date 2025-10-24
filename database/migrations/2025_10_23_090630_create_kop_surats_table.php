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
        Schema::create('kop_surats', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable(); 
        $table->string('logo')->nullable(); 
        $table->string('nama_instansi');
        $table->string('nama_unit')->nullable();
        $table->string('alamat')->nullable();
        $table->string('telepon')->nullable();
        $table->string('email')->nullable();
        $table->string('website')->nullable();
        $table->string('kota')->nullable();
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kop_surats');
    }
};
