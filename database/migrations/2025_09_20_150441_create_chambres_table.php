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
        Schema::create('chambres', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10);
            $table->string('type', 30); // simple, double, suite...
            $table->integer('capacite')->default(1);
            $table->decimal('prix', 15, 2);
            $table->string('prix_devise')->default('CDF');
            $table->foreignId('emplacement_id')->constrained('emplacements')->cascadeOnDelete();
            $table->enum('statut', ['libre', 'occupée', 'réservée'])->default('libre');
            $table->unsignedBigInteger('ets_id');
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
        Schema::dropIfExists('chambres');
    }
};
