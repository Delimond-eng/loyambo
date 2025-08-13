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
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10);
            $table->foreignId('emplacement_id')->constrained('emplacements')->cascadeOnDelete();
            $table->enum('statut', ['libre', 'occupée', 'réservée'])->default('libre');
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
        Schema::dropIfExists('restaurant_tables');
    }
};
