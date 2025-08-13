<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware("auth");
    }
    public function showAllUsers(){
        return view("users", [
            "users"=>User::all()
        ]);
    }
    /**
     * Crée un nouvel utilisateur avec son rôle et ses permissions.
     *
     * @param Request $request
     */
    public function createUser(Request $request)
    {
        // Validation des données entrantes
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'. $request->user_id,
            'password' => 'required|string|min:6',
            'role' => 'required|in:ADMIN,USER',
        ]);

        // Création de l'utilisateur
        $user = User::updateOrCreate(
            ["id"=>$request->user_id ?? ''],
            [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
        ]);

        // Vérification des permissions de l'utilisateur en fonction de son rôle
        $permissions = $this->getPermissionsByRole($user->role);

        foreach ($permissions as $permissionType) {
            // Création de l'entrée pour chaque permission
            UserPermission::updateOrCreate(
                ['permission_type' => $permissionType, 'user_id' => $user->id,],
                [
                'user_id' => $user->id,
                'permission_type' => $permissionType,
                'enabled' => true,
            ]);
        }

        return redirect()->route('users_manage')->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Récupère les permissions en fonction du rôle de l'utilisateur.
     *
     * @param string $role
     * @return array
     */
    private function getPermissionsByRole(string $role): array
    {
        switch ($role) {
            case 'ADMIN':
                return ['Update', 'Delete', 'Export'];
            case 'USER':
                return ['Update'];
            default:
                return [];
        }
    }
}
