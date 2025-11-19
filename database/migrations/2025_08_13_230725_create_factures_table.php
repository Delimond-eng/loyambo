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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero_facture', 20)->unique();
            $table->timestamp('date_facture')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->unsignedBigInteger('chambre_id')->nullable();
            $table->unsignedBigInteger('sale_day_id');
            $table->decimal('total_ht', 15, 2);
            $table->decimal('remise', 15, 2)->default(0);
            $table->decimal('tva', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2);
            $table->string('devise')->default("CDF");
            $table->enum('statut', ['en_attente', 'payée', 'annulée'])->default('en_attente');
            $table->enum('statut_service', ['en_attente', 'servie'])->default('en_attente');
            $table->unsignedBigInteger("ets_id");
            $table->unsignedBigInteger("emplacement_id");
            $table->unsignedBigInteger("client_id")->nullable();
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
        Schema::dropIfExists('factures');
    }
};
