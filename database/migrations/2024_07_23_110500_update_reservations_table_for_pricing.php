<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('type_sejour')->default('nuit')->after('date_fin')->comment('passage ou nuit');
            $table->decimal('prix_base', 10, 2)->default(0)->after('type_sejour')->comment('Prix standard de la chambre');
            $table->decimal('prix_facture', 10, 2)->default(0)->after('prix_base')->comment('Prix réellement appliqué');
            $table->decimal('remise_appliquee', 10, 2)->default(0)->after('prix_facture');
        });
    }

    public function down()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['type_sejour', 'prix_base', 'prix_facture', 'remise_appliquee']);
        });
    }
};
