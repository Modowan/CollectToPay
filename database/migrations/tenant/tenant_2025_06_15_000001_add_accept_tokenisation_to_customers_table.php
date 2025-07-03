<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cette migration ajoute la colonne accept_tokenisation à la table customers existante.
     */
    public function up(): void
    {
        Schema::connection($this->getConnection())->table('customers', function (Blueprint $table) {
            $table->boolean('accept_tokenisation')->default(0)->after('imported_from')->comment('Indique si le client accepte la tokenisation de sa carte bancaire');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->getConnection())->table('customers', function (Blueprint $table) {
            $table->dropColumn('accept_tokenisation');
        });
    }
};
