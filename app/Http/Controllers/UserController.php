<?php

namespace App\Http\Controllers;

use App\Models\Etablissement;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function createEtsAccount(Request $request)
    {
        try{
            $data = $request->validate([
                "nom"=>"required|string",
                "type"=>"required|string",
                "adresse"=>"required|string",
                "telephone"=>"nullable|string",
                "name"=>"required|string",
                "email"=>"required|email|unique:users,email",
                "password"=>"required|string|max:6",
            ]);

             // 1️⃣ Créer l'établissement
            $etablissement = Etablissement::create([
                'nom' => $data['nom'],
                'type' => $data['type'] ?? null,
                'adresse' => $data['adresse'] ?? null,
                'telephone' => $data['telephone'] ?? null,
            ]);

            // 2️⃣ Créer l'utilisateur admin lié à cet établissement
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'actif' => true,
                'role' => 'admin',
                'ets_id' => $etablissement->id,
            ]);

            // 3️⃣ Assigner le rôle admin via Spatie
            $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
            $user->assignRole($roleAdmin);

            return response()->json([
                "status"=>"success",
                "user"=>$user
            ]);

        }
         catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }
}
