<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

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
            $data["libelle"] = Str::upper($data["libelle"]);
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
                "emplacement_id"=>"nullable|int|exists:emplacements,id",
                "libelle"=>"required|string",
                "prix_unitaire"=>"required|string",
                "unite"=>"nullable|string",
                "seuil_reappro"=>"nullable|int",
                "qte_init"=>"nullable|int"
            ]);

            $data["libelle"] = Str::upper($data["libelle"]);

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
            $data["tva"] = $request->tva ?? false;
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
                    "emplacement_id"=> $data["emplacement_id"] ?? null
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
    public function updateProductTva(Request $request){
        $produit = Produit::find($request->id);
        if($produit){
            $produit->update(["tva"=>$request->tva]);
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
        $products = Produit::with("categorie")
            ->where("ets_id", $user->ets_id)
            ->select("produits.*")
            ->selectRaw("
                (
                    SELECT SUM(
                        CASE
                            WHEN type_mouvement = 'entrée' THEN quantite
                            WHEN type_mouvement = 'sortie' THEN -quantite
                            WHEN type_mouvement = 'vente' THEN -quantite
                            WHEN type_mouvement = 'transfert' AND destination IS NOT NULL THEN quantite
                            WHEN type_mouvement = 'transfert' AND source IS NOT NULL THEN -quantite
                            WHEN type_mouvement = 'ajustement' AND quantite > 0 THEN quantite
                            WHEN type_mouvement = 'ajustement' AND quantite < 0 THEN quantite
                            ELSE 0
                        END
                    )
                    FROM mouvement_stocks
                    WHERE mouvement_stocks.produit_id = produits.id
                ) AS stock_actuel
            ")
            ->orderBy("libelle")
            ->get();

        return response()->json(["produits" => $products]);
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



    //Afficher les données d'une fiche de stock...
    public function getFicheStockData()
    {
        $ets_id = Auth::user()->ets_id;

        $stocks = MouvementStock::select(
                'produit_id',
                'emplacement_id',

                // Stock initial = entrée sans document
                DB::raw("SUM(CASE WHEN type_mouvement = 'entrée' AND (numdoc IS NULL OR numdoc = 0) THEN quantite ELSE 0 END) as stock_initial"),

                // Entrées réelles (approvisionnement)
                DB::raw("SUM(CASE WHEN type_mouvement = 'entrée' AND (numdoc IS NOT NULL AND numdoc != 0) THEN quantite ELSE 0 END) as total_entree"),

                DB::raw("SUM(CASE WHEN type_mouvement = 'sortie' THEN quantite ELSE 0 END) as total_sortie"),
                DB::raw("SUM(CASE WHEN type_mouvement = 'vente' THEN quantite ELSE 0 END) as total_vente"),

                DB::raw("SUM(CASE WHEN type_mouvement = 'transfert' AND destination = emplacement_id THEN quantite ELSE 0 END) as total_transfert_entree"),
                DB::raw("SUM(CASE WHEN type_mouvement = 'transfert' AND source = emplacement_id THEN quantite ELSE 0 END) as total_transfert_sortie"),

                DB::raw("SUM(CASE WHEN type_mouvement = 'ajustement' AND quantite > 0 THEN quantite ELSE 0 END) as ajustement_plus"),
                DB::raw("SUM(CASE WHEN type_mouvement = 'ajustement' AND quantite < 0 THEN ABS(quantite) ELSE 0 END) as ajustement_moins")
            )
            ->with('produit:id,libelle')
            ->with('emplacement:id,libelle')
            ->where('ets_id', $ets_id)
            ->groupBy('produit_id', 'emplacement_id')
            ->get()
            ->map(function ($s) {
                // Calcul du solde réel (final)
                $s->solde =
                    $s->stock_initial +
                    $s->total_entree +
                    $s->total_transfert_entree +
                    $s->ajustement_plus -
                    $s->total_sortie -
                    $s->total_vente -
                    $s->total_transfert_sortie -
                    $s->ajustement_moins;
                return $s;
            });

        return view('fiche_stock', compact('stocks'));
    }

    public function exportFicheStockToExcel(){
        $stocks = $this->getFicheDatas();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Fiche Stock");
        // En-têtes
        $headers = ["Produit","Emplacement","Stock Initial","Entrée","Sortie","Transf. +","Transf. -","Vente","Ajust. +","Ajust. -","Solde Final"];
        $col = "A";
        foreach($headers as $header){
            $sheet->setCellValue($col."1", $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // Données
        $row = 2;
        foreach($stocks as $s){
            $sheet->setCellValue("A$row", $s->produit->libelle);
            $sheet->setCellValue("B$row", $s->emplacement->libelle ?? '-');
            $sheet->setCellValue("C$row", $s->stock_initial);
            $sheet->setCellValue("D$row", $s->total_entree);
            $sheet->setCellValue("E$row", $s->total_sortie);
            $sheet->setCellValue("F$row", $s->total_transfert_entree);
            $sheet->setCellValue("G$row", $s->total_transfert_sortie);
            $sheet->setCellValue("H$row", $s->total_vente);
            $sheet->setCellValue("I$row", $s->ajustement_plus);
            $sheet->setCellValue("J$row", $s->ajustement_moins);
            $sheet->setCellValue("K$row", $s->solde);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'fiche_stock_' . date('Ymd_His') . '.xlsx';

        return new StreamedResponse(function() use ($writer){
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ]);
    }
    public function exportFicheStockToPDF(){
        $stocks = $this->getFicheDatas();
        $pdf = PDF::loadView('pdf.fiche_stock_pdf', compact('stocks'))
              ->setPaper('A4', 'landscape');
        return $pdf->download('fiche_stock_' . date('Ymd_His') . '.pdf');
    }

    private function getFicheDatas(){
        $stocks = MouvementStock::select(
            'produit_id',
            'emplacement_id',
            DB::raw("SUM(CASE WHEN type_mouvement = 'entrée' AND (numdoc IS NULL OR numdoc = 0) THEN quantite ELSE 0 END) as stock_initial"),
            DB::raw("SUM(CASE WHEN type_mouvement = 'entrée' AND (numdoc IS NOT NULL AND numdoc != 0) THEN quantite ELSE 0 END) as total_entree"),
            DB::raw("SUM(CASE WHEN type_mouvement = 'sortie' THEN quantite ELSE 0 END) as total_sortie"),
            DB::raw("SUM(CASE WHEN type_mouvement = 'vente' THEN quantite ELSE 0 END) as total_vente"),
            DB::raw("SUM(CASE WHEN type_mouvement = 'transfert' AND destination = emplacement_id THEN quantite ELSE 0 END) as total_transfert_entree"),
            DB::raw("SUM(CASE WHEN type_mouvement = 'transfert' AND source = emplacement_id THEN quantite ELSE 0 END) as total_transfert_sortie"),
            DB::raw("SUM(CASE WHEN type_mouvement = 'ajustement' AND quantite > 0 THEN quantite ELSE 0 END) as ajustement_plus"),
            DB::raw("SUM(CASE WHEN type_mouvement = 'ajustement' AND quantite < 0 THEN ABS(quantite) ELSE 0 END) as ajustement_moins")
        )
        ->with('produit:id,libelle')
        ->with('emplacement:id,libelle')
        ->where('ets_id', Auth::user()->ets_id)
        ->groupBy('produit_id', 'emplacement_id')
        ->get()
        ->map(function ($s) {
            $s->solde = $s->stock_initial + $s->total_entree + $s->total_transfert_entree + $s->ajustement_plus
                        - $s->total_sortie - $s->total_vente - $s->total_transfert_sortie - $s->ajustement_moins;
            return $s;
        });

        return $stocks;
    }


}
