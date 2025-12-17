<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Report;
use App\Models\DataSource;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        Schema::table('data_sources', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Update existing records
        $reports = Report::all();
        foreach ($reports as $report) {
            $report->slug = Str::slug($report->name) . '-' . $report->id;
            $report->save();
        }

        $dataSources = DataSource::all();
        foreach ($dataSources as $dataSource) {
            $dataSource->slug = Str::slug($dataSource->name) . '-' . $dataSource->id;
            $dataSource->save();
        }

        // Now make them required and unique
        Schema::table('reports', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });

        Schema::table('data_sources', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('data_sources', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
