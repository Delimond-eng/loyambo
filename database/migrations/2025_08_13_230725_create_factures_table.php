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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero_facture', 20)->unique();
            $table->timestamp('date_facture')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('table_id')->nullable()->constrained('restaurant_tables')->nullOnDelete();
            $table->foreignId('sale_day_id')->nullable()->constrained('sale_days')->nullOnDelete();
            $table->decimal('total_ht', 15, 2);
            $table->decimal('remise', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2);
            $table->enum('statut', ['en_attente', 'payée', 'annulée'])->default('en_attente');
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
        Schema::dropIfExists('factures');
    }
};
