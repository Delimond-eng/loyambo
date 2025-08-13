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
        Schema::create('caisse_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caisse_id')->constrained('caisses')->cascadeOnDelete();
            $table->foreignId('serveur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('caissier_id')->constrained('users')->cascadeOnDelete();
            $table->date('rapport_date');
            $table->decimal('valeur_theorique', 15, 2)->default(0);
            $table->decimal('total_especes', 15, 2)->default(0);
            $table->integer('tickets_emis')->default(0);
            $table->integer('tickets_serveur')->default(0);
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
        Schema::dropIfExists('caisse_reports');
    }
};
