<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add file_path column if it doesn't exist
        if (!Schema::hasColumn('reports', 'file_path')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->string('file_path')->nullable()->after('description');
            });
        }

        // 2. Migrate data from directory_path and main_jrxml_name to file_path
        DB::table('reports')->get()->each(function ($report) {
            // Some records might already have file_path if manual fix was attempted
            if (empty($report->file_path)) {
                $directoryPath = $report->directory_path ?? '';
                $mainJrxmlName = $report->main_jrxml_name ?? '';

                if (!empty($directoryPath) && !empty($mainJrxmlName)) {
                    $filePath = rtrim($directoryPath, '/') . '/' . ltrim($mainJrxmlName, '/');
                    DB::table('reports')->where('id', $report->id)->update(['file_path' => $filePath]);
                }
            }
        });

        // 3. Make file_path non-nullable as per original migration intent, and drop old columns
        Schema::table('reports', function (Blueprint $table) {
            // Note: SQLite doesn't support changing nullability easily via table() or dropColumn in some versions/drivers
            // but Laravel's SQLite driver handles it fairly well with temporary tables.

            // Re-enforce not null if possible (or keep nullable if risk of missing data is high)
            // For now, let's keep it nullable if there's any chance of empty records, 
            // but the original migration had it non-nullable.
            // $table->string('file_path')->nullable(false)->change();

            if (Schema::hasColumn('reports', 'directory_path')) {
                $table->dropColumn('directory_path');
            }
            if (Schema::hasColumn('reports', 'main_jrxml_name')) {
                $table->dropColumn('main_jrxml_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'directory_path')) {
                $table->string('directory_path')->nullable();
            }
            if (!Schema::hasColumn('reports', 'main_jrxml_name')) {
                $table->string('main_jrxml_name')->nullable();
            }
        });

        // Optional: Re-migrate data back if possible

        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });
    }
};
