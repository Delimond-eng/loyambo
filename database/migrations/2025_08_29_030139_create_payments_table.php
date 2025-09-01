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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->float("amount");
            $table->string("devise")->default("CDF");
            $table->enum("mode",["cash", "mobile", "cheque","virement"]);
            $table->string("mode_ref")->nullable();
            $table->dateTime("pay_date");
            $table->unsignedBigInteger("emplacement_id")->nullable();
            $table->unsignedBigInteger("facture_id");
            $table->unsignedBigInteger("table_id");
            $table->unsignedBigInteger("chambre_id")->nullable();
            $table->unsignedBigInteger("caisse_id")->nullable();
            $table->unsignedBigInteger("sale_day_id")->nullable();
            $table->unsignedBigInteger("user_id");
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
        Schema::dropIfExists('payments');
    }
};
