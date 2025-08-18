<?php

namespace Database\Seeders;

use App\Models\Emplacement;
use Illuminate\Database\Seeder;

class PlaceSeeder extends Seeder
{
    public function run(): void
    {
    

        // Liste des modules CRUD
        $modules = [
            "PAVILLON A",
            "PAVILLON B",
            "HOTEL",
            "LOUNGE"
        ];

        foreach ($modules as $val) {
            Emplacement::create([
                "libelle"=>$val,
                "ets_id"=>1
            ]);
        }
    }
}
