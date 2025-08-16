<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AccessAllow;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }


    public function login(Request $request)
    {
        // 1️⃣ Validation
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2️⃣ Vérifier si la journée est autorisée
        $access = AccessAllow::latest()->first();
        $isAllowed = $access ? $access->allowed : false;

        $user = User::where('name', $request->name)->first();

        if (!$user) {
            return response()->json(['errors' => 'Utilisateur introuvable']);
        }

        // 3️⃣ Bloquer la connexion si journée non autorisée et non admin
        if (!$isAllowed && !$user->hasRole('admin')) {
            return response()->json([
                'alerts' => 'La journée n\'est pas ouverte.'
            ]);
        }

        // 4️⃣ Tentative de connexion
        if (Auth::attempt(['name' => $request->name, 'password' => $request->password], $request->filled('remember'))) {
            // 5️⃣ Redirection selon rôle
            return response()->json([
                "user"=>$user,
                "redirect"=>route("home")
            ]);
        }
        return response()->json(['errors' => 'Identifiants invalides']);
    }
}
