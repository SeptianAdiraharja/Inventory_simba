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
        Schema::table('export_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('export_logs', 'kop_surat_id')) {
                $table->unsignedBigInteger('kop_surat_id')->nullable();
                $table->foreign('kop_surat_id')
                    ->references('id')
                    ->on('kop_surats')
                    ->onDelete('set null');
            }
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('export_logs', function (Blueprint $table) {
            $table->dropForeign(['kop_surat_id']);
            $table->dropColumn('kop_surat_id');
        });
    }

};
