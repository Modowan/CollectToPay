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
        // Supprimer l'ancienne table notifications si elle existe
        Schema::dropIfExists('notifications');

        // Créer la nouvelle table notifications avec tous les champs Evervault
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Nouveaux champs pour Evervault
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->enum('channel', ['email', 'sms', 'push', 'database'])->default('database');
            $table->enum('delivery_status', ['pending', 'sent', 'failed', 'delivered'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->json('evervault_metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();

            // Index pour optimiser les requêtes
            $table->index(['organization_id', 'delivery_status']);
            $table->index(['delivery_status', 'created_at']);
            $table->index(['channel', 'delivery_status']);
            $table->index('recipient_email');
            $table->index('sent_at');

            // Clé étrangère vers la table organizations
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        
        // Optionnel : Recréer l'ancienne structure basique si nécessaire
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
};

