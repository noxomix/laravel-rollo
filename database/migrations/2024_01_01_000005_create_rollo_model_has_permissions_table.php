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
        Schema::create('rollo_model_has_permissions', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained('rollo_permissions')->onDelete('cascade');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->foreignId('context_id')->nullable()->constrained('rollo_contexts')->onDelete('cascade');
            
            $table->primary(['permission_id', 'model_type', 'model_id', 'context_id'], 'rollo_model_has_permissions_primary');
            $table->index(['model_type', 'model_id']);
            $table->index('context_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollo_model_has_permissions');
    }
};