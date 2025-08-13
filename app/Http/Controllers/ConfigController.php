<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ProfType;
use App\Models\VisitorType;
use App\Models\VisitPurpose;

class ConfigController extends Controller
{
    public function showConfigs(){
        $departments = Department::all();
        $profTypes = ProfType::all();
        $visitorTypes = VisitorType::all();
        $visitPurposes = VisitPurpose::all();
        return view("configs", [
            "departments"=>$departments,
            "profTypes"=>$profTypes,
            "visitorTypes"=>$visitorTypes,
            "visitPurposes"=>$visitPurposes
        ]);
    }
}
