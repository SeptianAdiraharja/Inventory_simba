<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Update enum untuk menambahkan 'approved_partially'
        DB::statement("
            ALTER TABLE carts
            MODIFY status
            ENUM('active', 'pending', 'approved', 'rejected', 'approved_partially')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // 🔁 Rollback ke enum sebelumnya (tanpa approved_partially)
        DB::statement("
            ALTER TABLE carts
            MODIFY status
            ENUM('active', 'pending', 'approved', 'rejected')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
