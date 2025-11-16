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
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->date("date_debut");
            $table->date("date_fin")->nullable();
            $table->unsignedBigInteger("admin_id");
            $table->unsignedBigInteger('ets_id');
            $table->unsignedBigInteger('emplacement_id')->nullable();
            $table->text("comment")->nullable();
            $table->enum("status", ["pending", "closed"])->default("pending");
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
        Schema::dropIfExists('inventaires');
    }
};
