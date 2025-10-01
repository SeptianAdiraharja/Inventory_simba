<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('export_logs', function (Blueprint $table) {
            $table->enum('data_type', ['masuk', 'keluar'])->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('export_logs', function (Blueprint $table) {
            $table->dropColumn('data_type');
        });
    }
};
