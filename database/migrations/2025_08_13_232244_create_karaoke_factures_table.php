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
        Schema::create('karaoke_factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('karaoke_reservations')->cascadeOnDelete();
            $table->foreignId('facture_id')->nullable()->constrained('factures')->nullOnDelete();
            $table->decimal('montant', 15, 2);
            $table->string('devise', 10)->default("CDF");
            $table->boolean('paye')->default(false);
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
        Schema::dropIfExists('karaoke_factures');
    }
};
