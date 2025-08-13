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
        Schema::create('caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emplacement_id')->constrained()->cascadeOnDelete(); // où est la caisse
            $table->foreignId('caissier_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('fond_initial', 15, 2)->default(0); // montant d’ouverture
            $table->string('devise', 3)->default('USD'); // ex: USD, EUR
            $table->decimal('taux_change', 15, 6)->nullable();
            $table->decimal('total_encaisse', 15, 2)->default(0);
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
        Schema::dropIfExists('caisses');
    }
};
