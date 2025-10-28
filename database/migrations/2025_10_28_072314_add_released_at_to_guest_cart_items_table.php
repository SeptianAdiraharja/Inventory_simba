<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_cart_items', function (Blueprint $table) {
            $table->timestamp('released_at')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('guest_cart_items', function (Blueprint $table) {
            $table->dropColumn('released_at');
        });
    }
};
