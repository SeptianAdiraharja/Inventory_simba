<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan perubahan.
     */
    public function up(): void
    {
        Schema::table('item_out_guests', function (Blueprint $table) {
            // Hapus kolom baru jika ada
            if (Schema::hasColumn('item_out_guests', 'item_id')) {
                $table->dropForeign(['item_id']);
                $table->dropColumn('item_id');
            }

            if (Schema::hasColumn('item_out_guests', 'quantity')) {
                $table->dropColumn('quantity');
            }

            // Pastikan kolom items tersedia lagi
            if (!Schema::hasColumn('item_out_guests', 'items')) {
                $table->longText('items')->nullable()->after('guest_id');
            }
        });
    }

    /**
     * Rollback perubahan.
     */
    public function down(): void
    {
        Schema::table('item_out_guests', function (Blueprint $table) {
            if (Schema::hasColumn('item_out_guests', 'items')) {
                $table->dropColumn('items');
            }

            if (!Schema::hasColumn('item_out_guests', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->after('guest_id');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            }

            if (!Schema::hasColumn('item_out_guests', 'quantity')) {
                $table->integer('quantity')->default(1)->after('item_id');
            }
        });
    }
};
