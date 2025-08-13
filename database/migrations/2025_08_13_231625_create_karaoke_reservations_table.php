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
        Schema::create('karaoke_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salle_id')->constrained('karaoke_salles')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date_reservation');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->integer('duree_minutes');
            $table->enum('statut', ['en_attente', 'confirmée', 'terminée', 'annulée'])->default('en_attente');
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
        Schema::dropIfExists('karaoke_reservations');
    }
};
