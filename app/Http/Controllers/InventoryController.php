<?php

namespace App\Http\Controllers;

use App\Models\Inventaire;
use App\Models\InventoryDetail;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Emplacement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function getInventoriesHistory()
    {
        $user = Auth::user();
        $inventories = Inventaire::with(['details.produit', 'admin', 'emplacement'])
            ->where('ets_id', $user->ets_id)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'closed' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['status' => 'success', 'inventories' => $inventories]);
    }

    public function startInventory(Request $request)
    {
        try {
            $data = $request->validate([
                'emplacement_id' => 'required|int|exists:emplacements,id'
            ]);

            $inventory = Inventaire::create([
                'date_debut' => Carbon::now(tz: "Africa/Kinshasa"),
                'status' => 'pending',
                'admin_id' => Auth::id(),
                'emplacement_id' => $data["emplacement_id"],
                'ets_id' => Auth::user()->ets_id,
            ]);

            return response()->json(['status' => 'success', 'inventory' => $inventory]);
        } catch (\Exception $e) { return response()->json(['errors' => [$e->getMessage()]]); }
    }

    public function getCurrentInventory()
    {
        $inventory = Inventaire::with('emplacement')->where('admin_id', Auth::id())
            ->where('status', 'pending')->latest()->first();
        return response()->json(['status' => $inventory ? 'success' : 'failed', 'inventory' => $inventory]);
    }

    public function getAllProductsWithStock(Request $request)
    {
        $ets_id = Auth::user()->ets_id;

        $products = Produit::with(['categorie', 'emplacements'])
            ->where('ets_id', $ets_id)
            ->get()
            ->map(function ($product) {
                $product->stock_global = COALESCE(Produit::find($product->id)->qte_init, 0) + MouvementStock::where('produit_id', $product->id)
                    ->selectRaw("SUM(CASE
                        WHEN type_mouvement = 'entrée' THEN quantite
                        WHEN type_mouvement = 'sortie' THEN -quantite
                        WHEN type_mouvement = 'vente' THEN -quantite
                        WHEN type_mouvement = 'transfert' AND destination IS NOT NULL THEN quantite
                        WHEN type_mouvement = 'transfert' AND source IS NOT NULL THEN -quantite
                        WHEN type_mouvement = 'ajustement' THEN quantite
                        ELSE 0 END) as total")
                    ->value('total') ?? 0;
                return $product;
            });

        return response()->json(['products' => $products]);
    }

    public function validateInventory(Request $request)
    {
        try {
            $validated = $request->validate([
                'inventory_id' => 'required|exists:inventaires,id',
                'items' => 'required|array',
            ]);

            return DB::transaction(function () use ($validated) {
                $inventory = Inventaire::findOrFail($validated['inventory_id']);
                $inventory->update(['status' => 'closed', 'date_fin' => Carbon::now('Africa/Kinshasa')]);

                foreach ($validated['items'] as $item) {
                    $difference = $item['real_quantity'] - $item["theoretical_quantity"];

                    InventoryDetail::create([
                        "inventory_id" => $inventory->id,
                        "produit_id" => $item["product_id"],
                        "quantite_theorique" => $item["theoretical_quantity"],
                        "quantite_physique" => $item["real_quantity"],
                        "ecart" => $difference
                    ]);

                    if ($difference != 0) {
                        MouvementStock::create([
                            'produit_id' => $item['product_id'],
                            'quantite' => $difference,
                            'type_mouvement' => 'ajustement',
                            'numdoc' => $inventory->id, // Liaison avec l'inventaire
                            'user_id' => Auth::id(),
                            'ets_id' => Auth::user()->ets_id,
                            'emplacement_id' => null,
                            'date_mouvement' => Carbon::now(),
                        ]);
                    }
                }
                return response()->json(['status' => 'success']);
            });
        } catch (\Exception $e) { return response()->json(['errors' => [$e->getMessage()]]); }
    }

    /**
     * Annule un inventaire déjà clôturé et restaure le stock
     */
    public function cancelInventory(Request $request)
    {
        try {
            $data = $request->validate([
                'inventory_id' => 'required|exists:inventaires,id'
            ]);

            return DB::transaction(function () use ($data) {
                $inventory = Inventaire::findOrFail($data['inventory_id']);

                if ($inventory->status !== 'closed') {
                    throw new \Exception("Seul un inventaire clôturé peut être annulé.");
                }

                // 1. Supprimer les mouvements d'ajustement liés à cet inventaire
                MouvementStock::where('type_mouvement', 'ajustement')
                    ->where('numdoc', $inventory->id)
                    ->delete();

                // 2. Marquer l'inventaire comme annulé
                $inventory->update(['status' => 'cancelled']);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Inventaire annulé et stock restauré avec succès.'
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['errors' => [$e->getMessage()]]);
        }
    }

    public function deleteInventory(Request $request)
    {
        Inventaire::where("id", $request->inventory_id)->delete();
        return response()->json(['status' => 'success']);
    }
}
