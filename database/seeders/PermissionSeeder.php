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
            'voir-dashboard',
            'voir-activites-serveurs',
            'voir-produits-vendus',
            'voir-occupations-tables',
            'voir-mouvements-stock',
            'voir-ventes',
            'voir-paiements',
            'voir-rapports',
            'voir-commandes',
            'voir-factures',
            'voir-categories',
            'voir-serveurs',
            'voir-utilisateurs',
            'voir-tables',
            'voir-chambres',
            'voir-produits',
            'voir-emplacements',
            'creer-produits',
            'supprimer-produits',
            'modifier-produits',
            'creer-mouvements-stock',
            'modifier-mouvements-stock',
            'supprimer-mouvements-stock',
            'creer-ventes',
            'supprimer-ventes',
            'modifier-ventes',
            'creer-factures',
            'supprimer-factures',
            'modifier-factures',
            'creer-paiements',
            'modifier-paiements',
            'supprimer-paiements',
            'creer-categories',
            'supprimer-categories',
            'modifier-categories',
            'cloturer-journee',
            'ouvrir-journee',
            'passer-commandes',
            'modifier-commandes',
            'supprimer-commandes',
        ]);

        $roleServeur->givePermissionTo([
            'voir-commandes',
            'passer-commandes',
            'creer-commandes',
            'voir-tables',
            'voir-chambres',
            'voir-factures',
            'voir-produits',
            'creer-factures',
            'voir-categories',
        ]);

        $roleCuisinier->givePermissionTo([
            'voir-commandes',
            'modifier-commandes', // ex : changer le statut en "préparé"
        ]);

        // Admin a tous les droits
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
