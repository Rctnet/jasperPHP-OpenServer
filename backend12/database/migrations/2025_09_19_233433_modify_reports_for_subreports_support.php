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
        Schema::table('reports', function (Blueprint $table) {
            $table->renameColumn('file_path', 'directory_path');
            $table->string('directory_path')->nullable()->change(); // Make it nullable
            $table->string('main_jrxml_name')->after('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('main_jrxml_name');
            $table->string('directory_path')->nullable(false)->change(); // Revert nullable
            $table->renameColumn('directory_path', 'file_path');
        });
    }
};