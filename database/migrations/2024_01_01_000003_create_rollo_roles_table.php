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
        Schema::create('rollo_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('context_id')->nullable()->constrained('rollo_contexts')->onDelete('cascade');
            $table->json('config')->nullable();
            $table->double('order')->nullable();
            $table->timestamps();
            
            $table->index(['name', 'context_id']);
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollo_roles');
    }
};