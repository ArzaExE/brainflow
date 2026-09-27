<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_columns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('column_type_id')
                ->nullable()
                ->constrained('column_types')
                ->nullOnDelete();

            $table->string('name');

            $table->unsignedSmallInteger('position')
                ->default(0);

            $table->boolean('is_done')
                ->default(false);

            $table->timestamps();

            $table->index(['project_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_columns');
    }
};
