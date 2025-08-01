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
        Schema::create('rollo_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('config')->nullable();
            $table->double('order')->nullable();
            $table->timestamps();
            
            $table->index('name');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollo_permissions');
    }
};