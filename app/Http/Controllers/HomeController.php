<?php

namespace App\Http\Controllers;

use App\Models\Currencie;
use App\Models\Facture;
use App\Models\FactureDetail;
use App\Models\SaleDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /** 
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }



    public function saveFacture(Request $request)
    {
        try {
            $data = $request->validate([
                'facture_id' => 'nullable|exists:factures,id',
                'table_id' => 'nullable|exists:restaurant_tables,id',
                'user_id' => 'nullable|exists:users,id',
                'remise' => 'nullable|numeric',
                'details' => 'required|array|min:1',
                'details.*.produit_id' => 'required|exists:produits,id',
                'details.*.quantite' => 'required|integer|min:1',
                'details.*.prix_unitaire' => 'required|numeric|min:0',
            ]);

            $factureId = null;
            DB::transaction(function () use ($data) {
                if (isset($data['facture_id'])) {
                    // Modification
                    $facture = Facture::findOrFail($data['facture_id']);
                    // Supprimer les anciens détails
                    $facture->details()->delete();
                } else {
                    // Création
                    $facture = new Facture();
                    $facture->numero_facture = 'FAC-' . time(); 
                }
                $saleDay = SaleDay::whereNull("end_time")->latest()->first();
                // Mise à jour des infos
                $facture->user_id = $data["user_id"] ?? Auth::id();
                $facture->table_id = $data['table_id'] ?? null;
                $facture->sale_day_id = $saleDay->id;
                $facture->remise = $data['remise'] ?? 0;
                $facture->statut = "en_attente";
                // Calcul total HT
                $total_ht = 0;
                foreach ($data['details'] as $detail) {
                    $total_ht += (int)$detail['quantite'] * (float)$detail['prix_unitaire'];
                }
                $facture->total_ht = $total_ht;
                // Calcul total TTC
                $facture->total_ttc = $total_ht - $facture->remise;
                $facture->save();

                // Création des détails
                foreach ($data['details'] as $detail) {
                    FactureDetail::create([
                        'facture_id' => $facture->id,
                        'produit_id' => $detail['produit_id'],
                        'quantite' => $detail['quantite'],
                        'prix_unitaire' => $detail['prix_unitaire'],
                        'total_ligne' => $detail['quantite'] * $detail['prix_unitaire'],
                    ]);
                }
                $factureId = $facture->id;
            });

            $facture = Facture::with('details')->find($factureId);

            return response()->json([
                'status' => 'success',
                'result' => $facture
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()->all()], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()], 500);
        }
    }

}
