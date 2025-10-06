<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_outs', function (Blueprint $table) {
            // Hapus foreign key jika ada
            if (Schema::hasColumn('item_outs', 'guest_id')) {
                $table->dropForeign(['guest_id']); // <--- Tambahkan baris ini
                $table->dropColumn('guest_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_outs', function (Blueprint $table) {
            $table->unsignedBigInteger('guest_id')->nullable()->after('cart_id');

            // Tambahkan kembali foreign key jika perlu
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
        });
    }
};
