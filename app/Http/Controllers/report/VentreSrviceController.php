<?php

namespace App\Http\Controllers\report;

use App\Http\Controllers\Controller;
use App\Models\Emplacement;
use App\Models\SaleDay;
use App\Support\ReportExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class VentreSrviceController extends Controller
{
    public function index(Request $request)
    {
        $etsId = auth()->user()->ets_id;
        $dateDebut = $request->query("date_debut");
        $dateFin = $request->query("date_fin");
        $serviceType = $request->query("service_type");
        $serviceType = $request->query("service_type");

        $emplacementsQuery = Emplacement::where("ets_id", $etsId)->orderBy("libelle");
        if ($serviceType) {
            $emplacementsQuery->where("type", $serviceType);
        }
        $emplacements = $emplacementsQuery->get();
        $serviceTypes = Emplacement::getTypesForEts($etsId);

        return view("reports.service_sales", compact("emplacements", "serviceTypes", "serviceType"));
    }

    public function showEmplacementSales(Request $request, $emplacement_id)
    {
        $etsId = auth()->user()->ets_id;
        $dateDebut = $request->query("date_debut");
        $dateFin = $request->query("date_fin");
        $serviceType = $request->query("service_type");

        $emplacement = Emplacement::where("ets_id", $etsId)
            ->where("id", $emplacement_id)
            ->first();

        if (!$emplacement) {
            return redirect()->route("reports.service.vente")->with("error", "Cet emplacement n'existe pas.");
        }

        if ($serviceType && $emplacement->type !== $serviceType) {
            return redirect()->route("reports.service.vente", ["service_type" => $serviceType])
                ->with("error", "Cet emplacement ne correspond pas au service sÃ©lectionnÃ©.");
        }

        $saledays = SaleDay::query()
            ->where("ets_id", $etsId)
            ->whereHas("factures", function ($q) use ($emplacement_id) {
                $q->where("emplacement_id", $emplacement_id)->where("statut", "payÃ©e");
            })
            ->when($dateDebut, fn($q) => $q->whereDate("sale_date", ">=", $dateDebut))
            ->when($dateFin, fn($q) => $q->whereDate("sale_date", "<=", $dateFin))
            ->with([
                "factures" => function ($q) use ($emplacement_id) {
                    $q->where("emplacement_id", $emplacement_id)
                        ->where("statut", "payÃ©e")
                        ->with(["payments", "user", "table", "chambre", "details.produit.categorie"]);
                },
            ])
            ->orderByDesc("sale_date")
            ->paginate(15)->withQueryString();

        return view("reports.service_sales_emplacement", compact("emplacement", "saledays", "serviceType", "dateDebut", "dateFin"));
    }

    public function showSaleDetails(Request $request, $id_saleDay, $emplacement_id)
    {
        $etsId = auth()->user()->ets_id;

        $emplacement = Emplacement::where("ets_id", $etsId)
            ->where("id", $emplacement_id)
            ->first();

        if (!$emplacement) {
            return redirect()->route("reports.service.vente")->with("error", "Cet emplacement n'existe pas.");
        }

        $saleday = SaleDay::with([
            "factures" => function ($query) use ($emplacement_id) {
                $query
                    ->where("emplacement_id", $emplacement_id)
                    ->where("statut", "payÃ©e");
            },
            "factures.user",
            "factures.table",
            "factures.chambre",
            "factures.details.produit.categorie",
            "factures.payments",
        ])
            ->where("id", $id_saleDay)
            ->where("ets_id", $etsId)
            ->first();

        if (!$saleday) {
            return redirect()
                ->route("reports.service_sales.emplacement", ["emplacement_id" => $emplacement_id])
                ->with("error", "Cette journée de vente n'existe pas.");
        }

        if ($saleday->factures->count() <= 0) {
            return redirect()
                ->route("reports.service_sales.emplacement", ["emplacement_id" => $emplacement_id])
                ->with("error", "Aucune vente trouvée pour cet emplacement.");
        }

        return view("reports.service_sales_details", compact("saleday", "emplacement"));
    }

    public function exportSaleDetailsPdf(Request $request, $id_saleDay, $emplacement_id)
    {
        $etsId = auth()->user()->ets_id;
        [$emplacement, $saleday] = $this->getSaleDayDetails($etsId, $id_saleDay, $emplacement_id);

        if (!$emplacement || !$saleday) {
            return redirect()->route("reports.service.vente")->with("error", "DonnÃ©es introuvables.");
        }

        $headers = ["Facture", "Date", "Client", "Serveur", "Table/Chambre", "Total", "Devise"];
        $rows = $saleday->factures->map(function ($facture) {
            $devise = $facture->payments->first()->devise ?? "CDF";
            $tableOrRoom = $facture->table ? ("Table " . $facture->table->numero) : ($facture->chambre ? ("Chambre " . $facture->chambre->numero) : "-");
            return [
                $facture->numero_facture,
                optional($facture->date_facture)->format("d/m/Y H:i"),
                $facture->client?->nom ?? "-",
                $facture->user?->name ?? "-",
                $tableOrRoom,
                number_format($facture->total_ttc, 0, ",", " "),
                $devise,
            ];
        })->toArray();

        $pdf = Pdf::loadView("pdf.report_table", [
            "title" => "DÃ©tails journÃ©e de vente",
            "subtitle" => $saleday->sale_date->format("d/m/Y") . " - " . $emplacement->libelle,
            "headers" => $headers,
            "rows" => $rows,
        ])->setPaper("a4", "landscape");

        return $pdf->download("vente_details_" . $saleday->sale_date->format("Ymd") . ".pdf");
    }

    public function exportSaleDetailsExcel(Request $request, $id_saleDay, $emplacement_id)
    {
        $etsId = auth()->user()->ets_id;
        [$emplacement, $saleday] = $this->getSaleDayDetails($etsId, $id_saleDay, $emplacement_id);

        if (!$emplacement || !$saleday) {
            return redirect()->route("reports.service.vente")->with("error", "DonnÃ©es introuvables.");
        }

        $headers = ["Facture", "Date", "Client", "Serveur", "Table/Chambre", "Total", "Devise"];
        $rows = $saleday->factures->map(function ($facture) {
            $devise = $facture->payments->first()->devise ?? "CDF";
            $tableOrRoom = $facture->table ? ("Table " . $facture->table->numero) : ($facture->chambre ? ("Chambre " . $facture->chambre->numero) : "-");
            return [
                $facture->numero_facture,
                optional($facture->date_facture)->format("d/m/Y H:i"),
                $facture->client?->nom ?? "-",
                $facture->user?->name ?? "-",
                $tableOrRoom,
                $facture->total_ttc,
                $devise,
            ];
        })->toArray();

        return ReportExporter::toExcel(
            "vente_details_" . $saleday->sale_date->format("Ymd") . ".xlsx",
            "Vente details",
            $headers,
            $rows
        );
    }

    private function getSaleDayDetails(int $etsId, int $id_saleDay, int $emplacement_id): array
    {
        $emplacement = Emplacement::where("ets_id", $etsId)
            ->where("id", $emplacement_id)
            ->first();

        if (!$emplacement) {
            return [null, null];
        }

        $saleday = SaleDay::with([
            "factures" => function ($query) use ($emplacement_id) {
                $query
                    ->where("emplacement_id", $emplacement_id)
                    ->where("statut", "payÃƒÂ©e");
            },
            "factures.user",
            "factures.table",
            "factures.chambre",
            "factures.details.produit.categorie",
            "factures.payments",
            "factures.client",
        ])
            ->where("id", $id_saleDay)
            ->where("ets_id", $etsId)
            ->first();

        return [$emplacement, $saleday];
    }
}


