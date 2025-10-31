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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('code_barre', 50)->nullable();
            $table->string('reference', 50)->nullable();
            $table->foreignId('categorie_id')->constrained('categories')->cascadeOnDelete();

            

            $table->string('libelle', 100);
            $table->decimal('prix_unitaire', 15, 2);
            $table->string('unite', 20)->nullable();
            $table->integer("qte_init")->default(0);
            $table->integer('seuil_reappro')->default(0);
            $table->boolean('quantified')->default(true);
            $table->string('image', 255)->nullable();
            $table->unsignedBigInteger("ets_id");
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
        Schema::dropIfExists('produits');
    }
};
