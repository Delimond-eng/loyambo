<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\VisitHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dompdf\Options;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AppController extends Controller
{


    public function __construct()
    {
        $this->middleware("auth");
    }

    // ===============================
    // VISIT CRUD
    // ===============================

    /**
     * Create a new visit entry.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function saveVisit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visit_id' => 'nullable|exists:visits,id',
            'full_name' => 'required|string|max:255',
            'company_or_address' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email_address' => 'nullable|email|max:255',
            'visitor_type' => 'nullable|string|max:255',
            'id_proof_type' => 'nullable|string|max:50',
            'id_proof_number' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:50',
            'purpose' => 'required|string|max:255',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'entry_authorized_by' => 'nullable|string|max:255',
            'pass_number' => 'nullable|string|max:50',
            'hostname' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
            'picture_url' => 'nullable|file|mimes:jpg,jpeg,png'
        ], [
            'time_out.after' => 'L\'heure de sortie invalide !',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $data = $request->only([
                'full_name',
                'company_or_address',
                'contact_number',
                'email_address',
                'visitor_type',
                'id_proof_type',
                'id_proof_number',
                'vehicle_number',
                'department',
                'purpose',
                'time_in',
                'time_out',
                'entry_authorized_by',
                'pass_number',
                'hostname',
                'status',
                'remarks'
            ]);

            // Ajout de l'utilisateur connecté et de la date de mise à jour
            $data['updated_by'] =Auth::user()->id;
            $data['update_timestamp'] = $request->update_timestamp ?? now();

            // Gestion du fichier image
            if ($request->hasFile('picture_url')) {
                $file = $request->file('picture_url');
                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/visitors'), $filename);
                $data['picture_url'] = 'uploads/visitors/' . $filename;
            }

            // Calcul automatique du stay_time si possible
            if (!empty($request->time_in) && !empty($request->time_out)) {
                $timeIn = \Carbon\Carbon::createFromFormat('H:i', $request->time_in);
                $timeOut = \Carbon\Carbon::createFromFormat('H:i', $request->time_out);

                if ($timeOut->greaterThan($timeIn)) {
                    $diff = $timeIn->diff($timeOut);
                    $data['stay_time'] = $diff->format('%Hh %Im');
                } else {
                    $data['stay_time'] = null;
                }
            }

            if(isset($data["time_out"]) && !empty($data["time_out"])){
                $data["status"] = 'Completed';
            }

            // On récupère l'ancienne visite si elle existe (avant l'update)
            $oldVisit = $request->visit_id ? Visit::find($request->visit_id) : null;

            // Création ou mise à jour
            $visit = Visit::updateOrCreate(
                ['id' => $request->visit_id],
                $data
            );

            // Préparation de l'historique
            $changes = [];

            if ($oldVisit) {
                foreach ($data as $key => $value) {
                    if ($oldVisit->{$key} != $value) {
                        $changes[$key] = [
                            'old' => $oldVisit->{$key},
                            'new' => $value
                        ];
                    }
                }
            } else {
                foreach ($data as $key => $value) {
                    $changes[$key] = [
                        'old' => null,
                        'new' => $value
                    ];
                }
            }

            // Sauvegarde de l'historique
            if (!empty($changes)) {
                VisitHistory::create([
                    'visit_id' => $visit->id,
                    'updated_by' => $data['updated_by'],
                    'update_timestamp' => $data['update_timestamp'],
                    'changes' => json_encode($changes, JSON_PRETTY_PRINT),
                ]);
            }

            return response()->json($visit);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()], 500);
        }
    }

    public function allVisits(Request $request)
    {
        $req = Visit::with("histories");

        if ($request->started_at || $request->end_date) {
            $startDate = $request->started_at;
            $endDate = $request->end_date;

            if ($startDate && !$endDate) {
                $req->whereDate("visit_date", $startDate);
            } elseif ($startDate && $endDate) {
                $req->whereBetween("visit_date", [$startDate, $endDate]);
            }
        }

        $visits = $req->get();

        return view("visits", [
            "visits" => $visits
        ]);
    }



    /**
     * Delete a visit by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteVisit(int $id): JsonResponse
    {
        Visit::findOrFail($id)->delete();
        return response()->json(['message' => 'Visite supprimée']);
    }

    /**
     * List all visits with related visitor data.
     *
     * @return JsonResponse
     */
    public function listVisits(): JsonResponse
    {
        return response()->json(Visit::with('visitor')->get());
    }




    // ===============================
    // AFFICHAGES SPÉCIFIQUES
    // ===============================

    /**
     * Get all visits on a specific date.
     *
     * @param string $date
     * @return JsonResponse
     */
    public function dailyEntries(string $date): JsonResponse
    {
        return response()->json(
            Visit::with('visitor')
                ->whereDate('visit_date', $date)
                ->orderBy('time_in')
                ->get()
        );
    }

    public function triggerDelete(string $table, $val){
        $field = 'id';
        $result = DB::table($table)
            ->where($field, $val)
            ->delete();
        return redirect()->back()->with('success', 'Suppression effectuée avec succès.');
    }
    public function createConfig(Request $request){
        if($request->libelle){
            DB::table($request->table)->updateOrInsert(
                ['libelle' => $request->libelle], // condition de recherche
                ['libelle' => $request->libelle]  // données à insérer ou mettre à jour
            );
        }
        return redirect()->back()->with('success', 'Configuration enregistrée avec succès.');
    }


    /**
     * Get history logs of a specific visit.
     *
     * @param int $visitId
     * @return JsonResponse
     */
    public function visitHistories($visitId): JsonResponse
    {
        return response()->json(
            VisitHistory::where('visit_id', $visitId)
                ->orderByDesc('update_timestamp')
                ->get()
        );
    }

    public function exportToPDF(Request $request){
        $query = $request->query("day");
        $l = Visit::with('histories');
        if(isset($query)){
            $l->whereDate("visit_date", Carbon::now());
        }
        $visits = $l->get();
        $pdf = Pdf::loadView('exports.visits', compact('visits'))
          ->setPaper('A4', 'landscape');
        return $pdf->download('visits.pdf');
    }
}
