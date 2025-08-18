<?php

namespace App\Http\Controllers;

use App\Models\AccessAllow;
use App\Models\Currencie;
use App\Models\SaleDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
                "start_time"=>Carbon::now()->setTimezone("Africa/kinshasa"),
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
    public function getAllUsersWithLatestLog(){
        $users = User::with(["lastLog", "emplacement","permissions", "roles.permissions"])->orderBy("name")->get();
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
   


}
