<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('item_outs', function (Blueprint $table) {
            $table->unsignedBigInteger('guest_id')->nullable()->after('cart_id');

            // tambahkan foreign key ke tabel guests
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('item_outs', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropColumn('guest_id');
        });
    }
};

