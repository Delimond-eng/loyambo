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
        Schema::create('sell_datas', function (Blueprint $table) {
            $table->id();
            $table->string('code_societe');
            $table->dateTime('date_cloture');
            $table->decimal('total_ventes_ht', 10, 2)->default(0);
            $table->decimal('tva_16', 10, 2)->default(0);
            $table->decimal('total_ventes_ttc', 10, 2)->default(0);
            $table->decimal('montant_theorique', 10, 2)->default(0);
            $table->decimal('montant_encaisse', 10, 2)->default(0);
            $table->decimal('ecart_caisse', 10, 2)->default(0);
            $table->decimal('cash', 10, 2)->default(0);
            $table->decimal('mobile_money', 10, 2)->default(0);
            $table->decimal('carte', 10, 2)->default(0);
            $table->string('caissier');
            $table->string('liaison_id')->nullable();
            $table->unsignedBigInteger('ets_id')->nullable();
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
        Schema::dropIfExists('sell_data');
    }
};
