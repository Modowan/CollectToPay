<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration will be executed on each tenant database.
     */
    public function up(): void
    {
        Schema::connection($this->getConnection())->create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->default('');
            $table->string('first_name', 100)->nullable()->default('');
            $table->string('last_name', 100)->nullable()->default('');
            $table->string('email');
            $table->string('password')->nullable();
            $table->string('phone')->default('');
            $table->string('address')->default('');
            $table->string('city')->default('');
            $table->string('country')->default('');
            $table->string('postal_code', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male','female','other'])->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('id_number', 50)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relation', 100)->nullable();
            $table->text('preferences')->nullable();
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            $table->integer('branch_id')->default(1);
            $table->enum('status', ['pending_password','active','inactive'])->nullable()->default('active');
            $table->boolean('is_activated')->default(1);
            $table->timestamp('activated_at')->nullable();
            $table->boolean('profile_completed')->default(0);
            $table->timestamp('last_profile_update')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedBigInteger('imported_from')->nullable();
            $table->timestamps();
            $table->index('email');
            $table->index('branch_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists('customers');
    }
};
