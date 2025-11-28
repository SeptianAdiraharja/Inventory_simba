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
        Schema::table('guest_carts', function (Blueprint $table) {
            // Hapus unique index pada session_id
            $table->dropUnique('guest_carts_session_id_unique');

            // Jadikan nullable untuk fleksibilitas
            $table->string('session_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
