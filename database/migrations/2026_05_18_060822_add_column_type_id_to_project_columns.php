<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_columns', function (Blueprint $table) {
            $table->foreignId('column_type_id')
                ->nullable()
                ->after('project_id')
                ->constrained('column_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_columns', function (Blueprint $table) {
            $table->dropForeign(['column_type_id']);
            $table->dropColumn('column_type_id');
        });
    }
};
