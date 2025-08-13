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
        Schema::create('facture_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->cascadeOnDelete();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('total_ligne', 15, 2); // QTE * prix_unitaire
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facture_details');
    }
};
