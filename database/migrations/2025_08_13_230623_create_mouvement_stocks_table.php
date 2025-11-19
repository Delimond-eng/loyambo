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
        Schema::create('mouvement_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->cascadeOnDelete();
            $table->unsignedBigInteger("numdoc")->nullable();
            $table->enum('type_mouvement', ['entrée', 'sortie', 'transfert','vente', 'ajustement']);
            $table->integer('quantite');
            $table->unsignedBigInteger('source')->nullable();
            $table->unsignedBigInteger('destination')->nullable();
            $table->unsignedBigInteger('sale_day_id')->nullable();
            $table->dateTime('date_mouvement');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('emplacement_id')->nullable();
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
        Schema::dropIfExists('mouvement_stocks');
    }
};
