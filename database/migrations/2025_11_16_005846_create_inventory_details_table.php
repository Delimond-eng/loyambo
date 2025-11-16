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
        Schema::create('inventory_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('produit_id');
            $table->integer('quantite_physique');
            $table->integer('quantite_theorique')->nullable();
            $table->integer('ecart')->nullable(); // différence entre théorie et réel
            $table->text('observation')->nullable();
            $table->unsignedBigInteger("inventory_id");
            $table->string("status")->default("actif");
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
        Schema::dropIfExists('inventory_details');
    }
};
