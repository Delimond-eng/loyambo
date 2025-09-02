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
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users", "id")->cascadeOnDelete();
            $table->foreignId("sale_day_id")->nullable()->constrained("sale_days", "id")->nullOnDelete();
            $table->date("log_date");
            $table->dateTime("logged_in_at")->useCurrent();
            $table->dateTime("logged_out_at")->nullable();
            $table->string("status")->default("online");
            $table->unsignedBigInteger("ets_id");
            $table->unsignedBigInteger("emplacement_id");
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
        Schema::dropIfExists('user_logs');
    }
};
