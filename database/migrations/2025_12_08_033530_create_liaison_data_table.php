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
        Schema::create('liaison_data', function (Blueprint $table) {
            $table->id();
            $table->string("code_cpte");
            $table->string("token");
            $table->string("liaison_id")->nullable();
            $table->unsignedBigInteger("ets_id")->nullable();
            $table->enum("status", ["pending", "success"])->default("pending");
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
        Schema::dropIfExists('liaison_data');
    }
};
