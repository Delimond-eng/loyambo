<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Visit;
use App\Models\VisitHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        
        $dailyVisits = Visit::with('histories')
                        ->whereDate('visit_date', Carbon::now())
                        ->orderBy('time_in')
                        ->paginate(10);
        return view('dashboard', [
            "visits"=>$dailyVisits
        ]);
    }

    public function counts(){
        $dailyVisitCount  = Visit::whereDate('visit_date', Carbon::now())
                        ->count();
        $activeVisitor = Visit::whereDate("visit_date", Carbon::now())
                            ->where("time_out", null)->count();
        $allVisits = Visit::count();
        $allUsers = User::count();

        return response()->json([
            "count"=>[
                "daily"=>$dailyVisitCount,
                "active"=>$activeVisitor,
                "visitCount"=>$allVisits,
                "users"=>$allUsers
            ]
        ]);
    }


    public function getStories(){
        $histories = VisitHistory::with(['visit', 'user'])
        ->orderBy('update_timestamp', 'desc')
        ->get();
        return view("stories", [
            "histories"=>$histories
        ]);
    }
}
