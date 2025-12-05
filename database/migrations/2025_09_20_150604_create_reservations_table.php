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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("chambre_id")->nullable();
            $table->unsignedBigInteger("table_id")->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('sale_day_id')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['en_attente', 'confirmée', 'annulée', 'terminée'])->default('en_attente');
            $table->unsignedBigInteger('ets_id');
            $table->unsignedBigInteger('emplacement_id')->nullable();
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
        Schema::dropIfExists('reservations');
    }
};
