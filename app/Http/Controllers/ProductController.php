<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    /**
     * Create categorie
     * @param Request $request
     * @return mixed
    */
    public function createCategory(Request $request){
        try{
            $data = $request->validate([
                "libelle"=>"required|string",
                "type_service"=>"required|string",
                "couleur"=>"required|string"
            ]);
            // Génération d'un code unique : 6 caractères alphanumériques
            $data["code"] = strtoupper(substr(uniqid(), -6));
            $data["ets_id"] = Auth::user()->ets_id;
            $categorie = Categorie::updateOrCreate(
                ["id"=>$request->id],
                $data
            );

            return response()->json([
                "status"=>"success",
                "categorie"=>$categorie
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    //GET CATEGORIES
     public function getAllCategories(){
        $user = Auth::user();
        $categories = Categorie::with("produits.stocks", "produits.categorie")
            ->where("ets_id", $user->ets_id)
            ->orderBy("libelle")->get();
        return response()->json(["categories"=>$categories]);
    }


    /**
     * Create categorie
     * @param Request $request
     * @return mixed
    */
    public function createProduct(Request $request){
        try{
            $data = $request->validate([
                "code_barre"=>"required|string",
                "reference"=>"required|string",
                "categorie_id"=>"required|int|exists:categories,id",
                "libelle"=>"required|string",
                "prix_unitaire"=>"required|string",
                "unite"=>"nullable|string",
                "seuil_reappro"=>"nullable|int",
                "qte_init"=>"nullable|int"
            ]);

            if ($request->hasFile('image')) {
                $file = $request->file('photo');
                $filename = uniqid('product') . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/products');
                $file->move($destination, $filename);
                // Générer un lien complet sans utiliser storage
                $data['image'] = url('uploads/products/' . $filename);
            }
            $user = Auth::user();

            $data["ets_id"] = $user->ets_id;
            $data["quantified"] = $request->quantified ?? false;
            $data["seuil_reappro"] = $data["seuil_reappro"] ?? 0;
            $data["qte_init"] = $data["qte_init"] ?? 0;

            $produit = Produit::updateOrCreate(["id"=>$request->id ?? null], $data);

            if($produit && $produit->qte_init >= 1){
                MouvementStock::create([
                    "produit_id"=>$produit->id,
                    "quantite"=>$produit->qte_init,
                    "type_mouvement"=>"entrée",
                    "destination"=>$request->emplacement_id,
                    "date_mouvement"=> Carbon::now()->setTimezone("Africa/Kinshasa"),
                    "user_id"=>Auth::id(),
                    "ets_id"=>$user->ets_id,
                    "emplacement_id"=>$user->emplacement_id ?? null
                ]);
            }
            return response()->json([
                "status"=>"success",
                "produit"=>$produit
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    public function updateProductQuantified(Request $request){
        $produit = Produit::find($request->id);
        if($produit){
            $produit->update(["quantified"=>$request->quantified]);
        }
        return response()->json([
            "status"=>"success",
            "result"=>"updated success",
            "product"=>$request->all()
        ]);
    }

    //ALL PRODUCT
    public function getAllProducts(){
        $user = Auth::user();
        $products = Produit::with(["categorie", "stocks"])
            ->where("ets_id", $user->ets_id)
            ->orderBy("libelle")->get();
        return response()->json(["produits"=>$products]);
    }


    /**
     * create Mouvement stock
     * @param Request $request
     * @return mixed
    */
    public function createStockMvt(Request $request){
        try{
            $data = $request->validate([
                "produit_id"=>"required|int|exists:produits,id",
                "type_mouvement"=>"required|string",
                "numdoc"=>"nullable|int",
                "quantite"=>"required|int",
                "source"=>"nullable|int",
                "destination"=>"required|int",
                "date_mouvement"=>"nullable|date",
            ]);
            $user = Auth::user();
            $data["date_mouvement"] = !isset($data["date_mouvement"]) ? Carbon::now()->setTimezone("Africa/Kinshasa") : $data["date_mouvement"];
            $data["user_id"] =$user->id;

            if(!$data["numdoc"]){
                $lastMvt = MouvementStock::where("ets_id", $user->ets_id)->latest()->first();
                if($lastMvt){
                    $data["numdoc"]= (int)$lastMvt->numdoc + 1;
                }
                else{
                    $data["numdoc"] = 1;
                }
            }

            $data["ets_id"] = $user->ets_id;
            $data["emplacement_id"] = $user->emplacement_id;
            $mvt = MouvementStock::updateOrCreate(["id"=>$request->id ?? null],$data);

            return response()->json([
                "status"=>"success",
                "result"=>$mvt
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

    //Get all mouvement
    public function getStockMvts(){
        $user = Auth::user();
        $reqs = MouvementStock::with(["produit","prov", "dest", "user"]);
        $reqs->where("ets_id",$user->ets_id);
        if($user->role !=="admin" && $user->emplacement_id){
            $reqs->where("emplacement_id", $user->emplacement_id);
        }
        $mvts = $reqs->orderByDesc("id")->get();
        return response()->json([
            "status"=>"success",
            "mouvements"=>$mvts
        ]);
    }

}
