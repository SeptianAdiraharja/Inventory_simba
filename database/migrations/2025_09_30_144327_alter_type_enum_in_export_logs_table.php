<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE export_logs
            MODIFY COLUMN type ENUM('weekly','monthly','yearly','custom') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE export_logs
            MODIFY COLUMN type ENUM('weekly','monthly','yearly') NOT NULL");
    }
};
