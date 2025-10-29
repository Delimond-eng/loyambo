<?php

namespace App\Http\Controllers\Commandes;

use App\Models\Facture;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class commandesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        

    // Si l'utilisateur est admin ou manager
    if ($user->hasRole('admin') || $user->hasRole('manager')) {
 $commandes = Facture::with('user', 'table', 'chambre')
    ->where('emplacement_id', $user->emplacement_id)
    ->whereIn('client_id', function($query) {
        $query->select('client_id')
              ->from('reservations')
              ->whereNotNull('client_id');
    })
    ->orderBy('created_at', 'desc')
    ->get();
} 
else if ($user->role == 'caissier') {
   $commandes = Facture::with('user', 'table', 'chambre')
    ->where('emplacement_id', $user->emplacement_id)
    ->whereIn('client_id', function($query) {
        $query->select('client_id')
              ->from('reservations')
              ->whereNotNull('client_id');
    })
    ->orderBy('created_at', 'desc')
    ->get();
}

    // Autres rôles (sécurité)
    else {
        abort(403, 'Accès non autorisé');
    }
        return view('commandes.index', compact('commandes'));
    }
    public function servir($id){
         $user = auth()->user();
         
         $facture=Facture::where("id",$id)->first();
         
         if($facture->statut !="payée"){
            return redirect()->back()->with("error","La facture n'est pas encore, la table ne peutpas etre servie");
         }
          if($facture->statut_service =="servie"){
            return redirect()->back()->with("error","Cette table a été déjà servie");
         }
         
         if($user->role=='admin'){
            $facture->statut_service="servie";
            $facture->save();
            return redirect()->back()->with("success","Une table a été servie avec succès");
         }
          if($user->role=='caissier'){
            $facture->statut_service="servie";
            $facture->save();
            return redirect()->back()->witht("success","Une table a été servie avec succès");
         }
        
    }
}
