<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Department;
use App\Models\ProfType;
use App\Models\UserPermission;
use App\Models\VisitorType;
use App\Models\VisitPurpose;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $user = \App\Models\User::create([
            "name"=>"Mr. Didier ",
            "email"=>"admin@gmail.com",
            "password"=>bcrypt("12345"),
            "role"=>"ADMIN"
        ]);
        if($user){
            if ($user->role === 'ADMIN') {
                foreach (['Update', 'Delete', 'Export'] as $type) {
                    UserPermission::create([
                        'user_id' => $user->id,
                        'permission_type' => $type,
                        'enabled' => true,
                    ]);
                }
            }
        }

        foreach([
            "Réunion",
            "Entretien",
            "Livraison",
            "Support",
            "Séminaire",
            "Proposition commerciale",
            "Réparation",
            "Inspection",
            "Consultation",
            "Formation",
        ] as $purpose){
            VisitPurpose::create(["libelle"=>$purpose]);
        }

        foreach([
            "Ressources Humaines",
            "Informatique",
            "Administration",
            "Ventes",
            "Support Technique",
            "Juridique",
            "Marketing",
            "Maintenance",
            "Opérations"
        ] as $dpt){
            Department::create(["libelle"=>$dpt]);
        }

        foreach([
            "Fournisseur",
            "Candidat",
            "Coursier",
            "Client",
            "Invité",
            "Technicien",
            "Auditeur",
            "Employé",
        ] as $vtype){
            VisitorType::create(["libelle"=>$vtype]);
        }

        foreach([
            "Carte d'Electeur",
            "Permis de conduire",
            "Carte de Service",
            "Passeport"
        ] as $profType){
            ProfType::create(["libelle"=>$profType]);
        }
    }
}
