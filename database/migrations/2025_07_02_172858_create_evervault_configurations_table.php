<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('evervault_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->text('api_key'); // Chiffré
            $table->string('app_id');
            $table->text('webhook_secret')->nullable(); // Chiffré
            $table->boolean('test_mode')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->json('features_enabled')->nullable(); // 3ds, preauth, etc.
            $table->timestamps();
            
            $table->unique('organization_id');
            $table->index(['organization_id', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('evervault_configurations');
    }
};