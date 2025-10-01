<?php

namespace App\Http\Controllers;

use App\Models\AccessAllow;
use App\Models\Etablissement;
use App\Models\Licence;
use App\Models\LicencePayRequest;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            AccessAllow::create([
                "allowed"=>false,
                "ets_id"=>$user->ets_id
            ]);

            // 3️⃣ Assigner le rôle admin via Spatie
            $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
            $user->assignRole($roleAdmin);

            Licence::create([
                'ets_id' => $etablissement->id,
                'type' => 'trial',
                'date_debut' => now(),
                'date_fin' => now()->addDays(15),
            ]);

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
        catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisée !"]);
        }
    }



     //CREATE NEW USER
    public function createUser(Request $request)
    {
        try {
            // Validation
            $data = $request->validate([
                'name'=>'required|string',
                'password'=>'required|string',
                'emplacement_id'=>'required|int|exists:emplacements,id',
                'role'=>"required|string",
                'salaire'=>'nullable|numeric'
            ]);

            $userId = $request->id ?? null;
            if ($userId) { // seulement si on met à jour un utilisateur existant
                $userToEdit = User::find($userId);
                // Vérifier si l'utilisateur a le rôle "admin"
                if ($userToEdit->hasRole('admin')) {
                    // Vérifier s'il n'y a qu'un seul admin dans la base
                    $adminCount = User::role('admin')->count();
                    if ($adminCount <= 1) {
                        return response()->json([
                            'errors' => "Impossible de modifier cet utilisateur car il est le seul admin existant."
                        ]);
                    }
                }
            }
            $data["email"] = trim(strtolower($data["name"])) . "@gmail.com";
            $data["ets_id"] = Auth::user()->ets_id;
            $data["password"] = bcrypt($data["password"]);

            // Création ou mise à jour
            $user = User::updateOrCreate(
                ["id" => $request->id ?? null],
                $data
            );

            // Assigner le rôle
            $user->assignRole($data["role"]);

            // Synchroniser les permissions du rôle vers l'utilisateur
            $role = Role::findByName($data["role"]);
            $user->syncPermissions($role->permissions->pluck('name')->toArray());

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur créé avec succès',
                'result' => $user->getAllPermissions() // ou $user->permissions si tu veux seulement direct + rôle fusionné
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
        catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            return response()->json(['errors' => "Action non autorisée !"]);
        }
    }

    public function redirectToPayment($ets_id)
    {
        $etablissement = Etablissement::findOrFail($ets_id);
        // Nombre d'utilisateurs pour calculer le montant
        $userCount = User::where('ets_id', $etablissement->id)->count();
        $amount = $userCount * 8; // 8$ par utilisateur

        // Générer un UUID unique pour le paiement
        $uuid = (string) \Illuminate\Support\Str::uuid();

        // Créer une entrée dans licence_pay_requests
        $payRequest = LicencePayRequest::create([
            'ets_id' => $etablissement->id,
            'uuid' => $uuid,
            'amount' => $amount,
            'status' => 'pending'
        ]);

        // Préparer les données HMAC pour le paiement
        $data = json_encode([
            'amount' => $amount,
            'currency' => 'USD',
            'uuid' => $uuid,
            'phone' => $etablissement->telephone
        ]);

        $secretKey = '9f8b7c3a2d1e4f5a6b7c8d9e0f1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a';
        $hash = hash_hmac('sha256', $data, $secretKey);
        $payload = base64_encode($data . '::' . $hash);

        $url = 'https://payment.milleniumhorizon.com?query=' . urlencode($payload);

        return redirect($url);
    }


    private function generatePaymentLink($ets_id)
    {
        $etablissement = Etablissement::findOrFail($ets_id);
        // Nombre d’utilisateurs de l’établissement
        $userCount = User::where('ets_id', $etablissement->id)->count();

        $amount = $userCount * 8;
        $currency = "USD";

        $data = json_encode([
            'amount' => $amount,
            'currency' => $currency,
            "uuid" => (string) \Illuminate\Support\Str::uuid(),
            "phone" => $etablissement->telephone
        ]);
        $secretKey = '9f8b7c3a2d1e4f5a6b7c8d9e0f1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a';
        $hash = hash_hmac('sha256', $data, $secretKey);

        $payload = base64_encode($data . '::' . $hash);

        $url = 'https://payment.milleniumhorizon.com?query=' . urlencode($payload);

        return $url;
    }

}
