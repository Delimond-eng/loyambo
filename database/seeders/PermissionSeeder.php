<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Liste des modules CRUD
        $modules = [
            'utilisateurs',
            'factures',
            'serveurs',
            'produits',
            'categories',
            'mouvements-stock',
            'tables',
            'emplacements',
            'chambres',
            'rapports',
            'ventes',
            'commandes',
            'paiements',
        ];

        // Permissions personnalisées hors CRUD
        $customPermissions = [
            'voir-dashboard',
            'voir-activites-serveurs',
            'voir-produits-vendus',
            'voir-occupations-tables',
            'cloturer-journee',
            'ouvrir-journee',
            'passer-commandes',
        ];

        // Génération des permissions CRUD
        $permissions = [];
        foreach ($modules as $module) {
            $permissions = array_merge($permissions, [
                "voir-$module",
                "creer-$module",
                "modifier-$module",
                "supprimer-$module",
            ]);
        }

        // Ajout des personnalisées
        $permissions = array_merge($permissions, $customPermissions);

        // Insertion en base
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Rôles
        $roleAdmin     = Role::firstOrCreate(['name' => 'admin']);
        $roleCaissier  = Role::firstOrCreate(['name' => 'caissier']);
        $roleServeur   = Role::firstOrCreate(['name' => 'serveur']);
        $roleCuisinier = Role::firstOrCreate(['name' => 'cuisinier']);

        // Attribution des permissions par rôle
        $roleCaissier->givePermissionTo([
            'voir-ventes',
            'creer-ventes',
            'voir-factures',
            'creer-factures',
            'voir-paiements',
            'creer-paiements',
            'voir-rapports',
            'cloturer-journee',
        ]);

        $roleServeur->givePermissionTo([
            'voir-commandes',
            'passer-commandes',
            'creer-commandes',
            'voir-tables',
        ]);

        $roleCuisinier->givePermissionTo([
            'voir-commandes',
            'modifier-commandes', // ex : changer le statut en "préparé"
        ]);

        // Admin a tous les droits
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
