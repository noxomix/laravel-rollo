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
        Schema::create('rollo_model_has_roles', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('rollo_roles')->onDelete('cascade');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            
            $table->primary(['role_id', 'model_type', 'model_id']);
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollo_model_has_roles');
    }
};