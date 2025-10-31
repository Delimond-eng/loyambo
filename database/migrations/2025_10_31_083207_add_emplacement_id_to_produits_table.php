<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produits', function (Blueprint $table) {
            // On ajoute la colonne emplacement_id après reference
            $table->foreignId('emplacement_id')
                  ->after('reference')
                  ->constrained('emplacements')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Annuler les migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produits', function (Blueprint $table) {
            // On supprime d’abord la contrainte avant de supprimer la colonne
            $table->dropForeign(['emplacement_id']);
            $table->dropColumn('emplacement_id');
        });
    }
};
