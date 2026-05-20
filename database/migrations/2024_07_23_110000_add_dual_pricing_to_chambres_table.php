<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('chambres', function (Blueprint $table) {
            $table->decimal('prix_nuit', 10, 2)->after('type')->default(0)->comment('Prix pour une nuitée');
            $table->decimal('prix_passage', 10, 2)->after('prix_nuit')->default(0)->comment('Prix pour un court séjour (passage)');

            // Si vous souhaitez supprimer l'ancienne colonne prix
            // Assurez-vous d'avoir migré les données si nécessaire
            if (Schema::hasColumn('chambres', 'prix')) {
                 $table->dropColumn('prix');
            }
        });
    }

    public function down()
    {
        Schema::table('chambres', function (Blueprint $table) {
            $table->dropColumn(['prix_nuit', 'prix_passage']);
            $table->decimal('prix', 10, 2)->default(0);
        });
    }
};
