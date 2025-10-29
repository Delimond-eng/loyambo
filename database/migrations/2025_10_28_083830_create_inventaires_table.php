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
        Schema::create('inventaires', function (Blueprint $table) {
             $table->id();
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->foreignId('ets_id')->nullable()->constrained('etablissements')->onDelete('cascade');
            $table->integer('quantite_physique');
            $table->integer('quantite_theorique')->nullable();
            $table->integer('ecart')->nullable(); // différence entre théorie et réel
            $table->text('observation')->nullable();
            $table->date('date_inventaire')->default(now());
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // l’utilisateur qui a fait l’inventaire
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
        Schema::dropIfExists('inventaires');
    }
};
