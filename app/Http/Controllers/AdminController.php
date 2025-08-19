<?php

namespace App\Http\Controllers;

use App\Models\AccessAllow;
use App\Models\Currencie;
use App\Models\Emplacement;
use App\Models\RestaurantTable;
use App\Models\SaleDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller
{
    /**
     * Fonction permettant de commencer une journée de ventes
     * @param Request $request
     * @return 
    */
    public function startDay(Request $request){
        try{
            $data = $request->validate([
                "currencie_value"=>"required|numeric"
            ]);

            $currencie = Currencie::updateOrCreate(
                ["currencie_date"=>Carbon::now()->toDateString(),],
            [
                "currencie_date"=>Carbon::now()->toDateString(),
                "currencie_value"=>$data["currencie_value"],
            ]);

            $saleDay = SaleDay::updateOrCreate(
                ["sale_date"=>Carbon::now()->toDateString()],
                [
                "sale_date"=>Carbon::now()->toDateString(),
                "start_time"=>Carbon::now()->setTimezone("Africa/Kinshasa"),
                "end_time"=>null
            ]);
            $accessAllow = AccessAllow::latest()->first();
            $accessAllow->update([
                "allowed"=>true
            ]);

            return response()->json([
                "status"=>"success",
                "result"=>[
                    "saleDay"=>$saleDay,
                    "currencie"=>$currencie,
                    "access"=>$accessAllow
                ]
            ]);

        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }


    //GET ALL USERS 
    public function getAllUsersWithLatestLog(Request $request){
        $role = $request->query("role") ?? null;
        $query = User::with(["lastLog", "emplacement","permissions", "roles.permissions"])
                ->orderBy("name");
        if($role){
            $query->where("role", $role);
        }
        $users = $query->get();
        return response()->json([
            "status"=>"success",
            "users"=>$users
        ]);
    }


    //GET ALL USER PERMISSION
    public function getAllPermissions(){
        $permissions = Permission::all();
        return response()->json([
            "status"=>"success",
            "permissions"=>$permissions
        ]);
    }


    //UPDATE SELECTED USER PERMISSION
    public function updateUserPermissions(Request $request)
    {
        try{
            // Validation
            $data = $request->validate([
                'user_id'=>'required|int|exists:users,id',
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id'
            ]);
            $user = User::findOrFail($data["user_id"]);

            $permissionNames = Permission::whereIn('id', $data['permissions'])
                                ->pluck('name')
                                ->toArray();
            \Log::info('Permissions à synchroniser: ', $permissionNames);
            // Synchroniser les permissions
            $user->syncPermissions($permissionNames);
            return response()->json([
                'status'=>'success',
                'message' => 'Permissions mises à jour avec succès',
                'result' => $user->getAllPermissions()
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
        
    }

    /**
     * Create Emplacement
     * @param Request $request
     * @return mixed
    */
    public function createEmplacement(Request $request){
        try{
            // Validation
            $data = $request->validate([
                "libelle"=>"required|string",
                "type"=>"required|string",
            ]);
            $data["ets_id"] = Auth::user()->ets_id;
            $emplacement = Emplacement::updateOrCreate([
                "id"=>$request->id ?? null
            ],$data);
           
            return response()->json([
                'status'=>'success',
                'message' => 'Nouveau emplacement créé avec succès !',
                'result' => $emplacement
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }


    //GET ALL Emplacements with tables
    public function getAllEmplacements(){
        $emplacements = Emplacement::with("tables")->orderBy("libelle")->get();
        return response()->json([
            "status"=>"success",
            "emplacements"=>$emplacements
        ]);
    }

    /**
     * Create Table
     * @param Request $request
     * @return mixed
    */
    public function createTable(Request $request){
        try{
            // Validation
            $data = $request->validate([
                "tables.*.numero"=>"required|string",
                "tables.*.emplacement_id"=>"required|int|exists:emplacements,id",
                "tables.*.id"=>"nullable|int",
            ]);
            $tables = $data["tables"];
            foreach ($tables as $table) {
                if(isset($table["id"])){
                    $cdts["id"] = $table["id"];
                }
                else{
                    $cdts = [
                        "numero"=>$table["numero"],
                        "emplacement_id"=>$table["emplacement_id"]
                    ];
                }
                Log::info($cdts);
                RestaurantTable::updateOrCreate($cdts,["numero"=>$table["numero"], "emplacement_id"=>$table["emplacement_id"]]);
            }
            return response()->json([
                'status'=>'success',
                'message' => 'Nouvelle table créée avec succès !',
                'result' => 'Nouvelle table créée avec succès !',
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['errors' => $errors]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['errors' => $e->getMessage()]);
        }
    }

    //GET ALL TABLES
    public function getAllTables(Request $request){
        $placeId = $request->query("place") ?? null;
        $query = RestaurantTable::with("emplacement")->orderBy("numero");
        if($placeId){
            $query->where("emplacement_id", $placeId);
        }
        $tables = $query->get();
        return response()->json([
            "status"=>"success",
            "tables"=>$tables
        ]);
    }
   


}
