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
        Schema::create('licences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ets_id');
            $table->enum('type', ['trial', 'paid'])->default('trial');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('status', ['available', 'expired', 'suspended', 'pending_payment'])->default('available');
            $table->timestamps();
            $table->foreign('ets_id')->references('id')->on('etablissements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('licences');
    }
};
