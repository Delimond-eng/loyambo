<?php

namespace App\View\Components;

use App\Models\Department;
use App\Models\ProfType;
use App\Models\VisitorType;
use App\Models\VisitPurpose;
use Illuminate\View\Component;

class VisitCreateModal extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $departments;
    public $profTypes;
    public $visitorTypes;
    public $visitPurposes;
    public function __construct()
    {
        $this->departments = Department::all();
        $this->profTypes = ProfType::all();
        $this->visitorTypes = VisitorType::all();
        $this->visitPurposes = VisitPurpose::all();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.visit-create-modal');
    }
}
