<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_processor_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('processor_type'); // 'stripe', 'paypal'
            $table->text('api_key'); // Chiffré
            $table->text('api_secret')->nullable(); // Chiffré
            $table->string('webhook_endpoint_id')->nullable();
            $table->boolean('test_mode')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'processor_type']);
            $table->index(['organization_id', 'processor_type', 'is_active'], 'ppc_org_proc_active_idx');

        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_processor_configs');
    }
};