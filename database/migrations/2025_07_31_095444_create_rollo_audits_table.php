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
        Schema::create('rollo_audits', function (Blueprint $table) {
            $table->id();
            
            // Event type (e.g., 'permission.assigned', 'role.created')
            $table->string('event', 50);
            
            // The model that was changed (e.g., User, RolloRole)
            $table->morphs('auditable');
            
            // The target model (e.g., the User who received a permission)
            $table->nullableMorphs('subject');
            
            // Store old and new values as JSON
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('metadata')->nullable();
            
            // Only created_at, no updated_at needed for audit logs
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes for performance
            $table->index(['event', 'created_at']);
            $table->index('user_id');
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at'); // For cleanup queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollo_audits');
    }
};