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
        Schema::create('licence_pay_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ets_id');
            $table->string("uuid")->unique();
            $table->float("amount");
            $table->integer("months")->default(1);
            $table->enum("status", ["pending", "valid"])->default("pending");
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
        Schema::dropIfExists('licence_pay_requests');
    }
};
