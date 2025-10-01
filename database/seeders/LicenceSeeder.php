<?php

namespace Database\Seeders;

use App\Models\Emplacement;
use App\Models\Licence;
use Illuminate\Database\Seeder;

class LicenceSeeder extends Seeder
{
    public function run(): void
    {
        Licence::create([
            'ets_id' => 1,
            'type' => 'trial',
            'date_debut' => now(),          // date du serveur
            'date_fin'   => now()->addDays(15), // 15 jours depuis aujourd'hui (serveur)
        ]);
    }
}
