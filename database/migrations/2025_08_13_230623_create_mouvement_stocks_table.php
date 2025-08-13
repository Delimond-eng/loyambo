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
            $table->enum('type_mouvement', ['entrée', 'sortie', 'transfert']);
            $table->integer('quantite');
            $table->string('source', 50)->nullable();
            $table->string('destination', 50)->nullable();
            $table->timestamp('date_mouvement')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
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
