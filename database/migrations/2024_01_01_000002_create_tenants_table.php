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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('database_name');
            $table->enum('status', ['active','inactive','pending'])->default('active');
            $table->integer('max_users')->default(50);
            $table->integer('max_branches')->default(10);
            $table->string('subscription_plan', 100)->nullable()->default('standard');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->text('settings')->nullable();
            $table->timestamps();
            $table->index('domain');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
