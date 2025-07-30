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
        Schema::create('rollo_contexts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('contextable_type')->nullable();
            $table->unsignedBigInteger('contextable_id')->nullable();
            $table->timestamps();
            
            $table->index(['contextable_type', 'contextable_id']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollo_contexts');
    }
};