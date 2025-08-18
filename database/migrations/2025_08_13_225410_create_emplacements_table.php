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
        Schema::create('etablissements', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('type', 50)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('emplacements', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 50);
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
        Schema::dropIfExists('etablissements');
        Schema::dropIfExists('emplacements');
    }
};
