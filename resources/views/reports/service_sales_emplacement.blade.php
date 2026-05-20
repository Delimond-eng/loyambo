@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="d-flex align-items-center">
               <div class="col-xl-12">
                    @include("components.menus.reports")
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border p-5 text-center">
                            <h4 class="box-title">Ventes par service - etape 2/2 ({{ $emplacement->libelle }})</h4>
                            <p class="text-muted">Les donnees sont groupees par journee de vente avec heure de debut et de fin.</p>
                        </div>
                        
                        <!-- Filtres -->
                        <div class="box-body">
<div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="date_debut">Date dÃ©but</label>
                                    <input type="date" id="date_debut" class="form-control" value="{{ request('date_debut') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="date_fin">Date fin</label>
                                    <input type="date" id="date_fin" class="form-control" value="{{ request('date_fin') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="search">Recherche</label>
                                    <input type="text" id="search" class="form-control" placeholder="Rechercher...">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" id="appliquerFiltres">
                                        <i class="fa fa-filter"></i> Appliquer les filtres
                                    </button>
                                    <button class="btn btn-secondary" id="resetFiltres">
                                        <i class="fa fa-refresh"></i> RÃ©initialiser
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des journÃ©es de vente -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date de vente</th>
                                            <th>Heure dÃ©but</th>
                                            <th>Heure fin</th>
                                            <th>Factures payÃ©es</th>
                                            <th>Total des ventes</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($saledays as $saleday)
                                            @php
                                                // Filtrer les factures par emplacement et statut payÃ©e
                                                $facturesFiltrees = $saleday->factures->filter(function($facture) use ($emplacement) {
                                                    // VÃ©rifier l'emplacement
                                                    $emplacementOk = false;
                                                    if ($emplacement->type === 'hotel') {
                                                        $emplacementOk = $facture->chambre && $facture->chambre->emplacement_id == $emplacement->id;
                                                    } else {
                                                        $emplacementOk = $facture->table && $facture->table->emplacement_id == $emplacement->id;
                                                    }
                                                    
                                                    // VÃ©rifier le statut payÃ©e
                                                    $statutOk = $facture->statut === 'payÃ©e';
                                                    
                                                    return $emplacementOk && $statutOk;
                                                });
                                                
                                                $totalFacturesPayees = $facturesFiltrees->count();
                                                $totalVentesPayees = $facturesFiltrees->sum('total_ttc');
                                                
                                                // RÃ©cupÃ©rer toutes les devises utilisÃ©es dans les paiements des factures payÃ©es
                                                $devises = [];
                                                foreach ($facturesFiltrees as $facture) {
                                                    foreach ($facture->payments as $payment) {
                                                        if (!empty($payment->devise)) {
                                                            $devises[$payment->devise] = $payment->devise;
                                                        }
                                                    }
                                                }
                                                
                                                // Si plusieurs devises, afficher "Multiples", sinon la devise unique
                                                if (count($devises) > 1) {
                                                    $deviseAffichage = 'Multiples';
                                                } elseif (count($devises) === 1) {
                                                    $deviseAffichage = reset($devises);
                                                } else {
                                                    $deviseAffichage = 'N/A';
                                                }
                                            @endphp
                                            <tr class="sale-day-row" 
                                                data-date="{{ $saleday->sale_date->format('Y-m-d') }}"
                                                data-search="{{ $saleday->sale_date->format('d/m/Y') }} {{ $totalFacturesPayees }} {{ $totalVentesPayees }} {{ $deviseAffichage }}">
                                                <td>
                                                    <strong>{{ $saleday->sale_date->format('d/m/Y') }}</strong>
                                                </td>
                                                <td>{{ $saleday->start_time->format('H:i') }}</td>
                                                <td>
                                                    @if($saleday->end_time)
                                                        {{ $saleday->end_time->format('H:i') }}
                                                    @else
                                                        <span class="badge badge-warning">En cours</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary" style="font-size: 14px;">
                                                        {{ $totalFacturesPayees }} factures payÃ©es
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong class="text-success">
                                                        {{ number_format($totalVentesPayees, 0, ',', ' ') }} 
                                                        <span class="text-muted" style="font-size: 12px;">{{ $deviseAffichage }}</span>
                                                    </strong>
                                                </td>
                                                <td>
                                                    @if($saleday->end_time)
                                                        <span class="badge badge-success">TerminÃ©e</span>
                                                    @else
                                                        <span class="badge badge-warning">En cours</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('reports.service.vente.details', ['id_saleDay'=>$saleday->id,"emplacement_id"=>$emplacement->id] + request()->query()) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fa fa-eye"></i> Voir dÃ©tails
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Aucune journee de vente trouvee pour ce filtre.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-4">
                                {{ $saledays->links('vendor.pagination.vue-table') }}
                            </div>

                            <!-- Statistiques rÃ©sumÃ©es -->
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">{{ $saledays->total() }}</h3>
                                            <p class="mb-0">JournÃ©es totales</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">
                                                @php
                                                    $totalFacturesGlobal = 0;
                                                    $saledays->each(function($saleday) use ($emplacement, &$totalFacturesGlobal) {
                                                        $facturesFiltrees = $saleday->factures->filter(function($facture) use ($emplacement) {
                                                            $emplacementOk = false;
                                                            if ($emplacement->type === 'hotel') {
                                                                $emplacementOk = $facture->chambre && $facture->chambre->emplacement_id == $emplacement->id;
                                                            } else {
                                                                $emplacementOk = $facture->table && $facture->table->emplacement_id == $emplacement->id;
                                                            }
                                                            return $emplacementOk && $facture->statut === 'payÃ©e';
                                                        });
                                                        $totalFacturesGlobal += $facturesFiltrees->count();
                                                    });
                                                @endphp
                                                {{ $totalFacturesGlobal }}
                                            </h3>
                                            <p class="mb-0">Factures payÃ©es totales</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">
                                                @php
                                                    $totalVentesGlobal = 0;
                                                    $devisesGlobal = [];
                                                    $saledays->each(function($saleday) use ($emplacement, &$totalVentesGlobal, &$devisesGlobal) {
                                                        $facturesFiltrees = $saleday->factures->filter(function($facture) use ($emplacement) {
                                                            $emplacementOk = false;
                                                            if ($emplacement->type === 'hotel') {
                                                                $emplacementOk = $facture->chambre && $facture->chambre->emplacement_id == $emplacement->id;
                                                            } else {
                                                                $emplacementOk = $facture->table && $facture->table->emplacement_id == $emplacement->id;
                                                            }
                                                            return $emplacementOk && $facture->statut === 'payÃ©e';
                                                        });
                                                        $totalVentesGlobal += $facturesFiltrees->sum('total_ttc');
                                                        
                                                        // Collecter toutes les devises
                                                        foreach ($facturesFiltrees as $facture) {
                                                            foreach ($facture->payments as $payment) {
                                                                if (!empty($payment->devise)) {
                                                                    $devisesGlobal[$payment->devise] = $payment->devise;
                                                                }
                                                            }
                                                        }
                                                    });
                                                    
                                                    // DÃ©terminer l'affichage de la devise pour les stats globales
                                                    if (count($devisesGlobal) > 1) {
                                                        $deviseAffichageGlobal = 'Multiples';
                                                    } elseif (count($devisesGlobal) === 1) {
                                                        $deviseAffichageGlobal = reset($devisesGlobal);
                                                    } else {
                                                        $deviseAffichageGlobal = '';
                                                    }
                                                @endphp
                                                {{ number_format($totalVentesGlobal, 0, ',', ' ') }} {{ $deviseAffichageGlobal }}
                                            </h3>
                                            <p class="mb-0">Chiffre d'affaires (payÃ©)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
</div>
<!-- /.content-wrapper -->
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const search = document.getElementById('search');
    const appliquerFiltres = document.getElementById('appliquerFiltres');
    const resetFiltres = document.getElementById('resetFiltres');
    const rows = document.querySelectorAll('.sale-day-row');

    if (appliquerFiltres) {
        appliquerFiltres.addEventListener('click', function() {
            const params = new URLSearchParams(window.location.search);
            if (dateDebut && dateDebut.value) {
                params.set('date_debut', dateDebut.value);
            } else {
                params.delete('date_debut');
            }
            if (dateFin && dateFin.value) {
                params.set('date_fin', dateFin.value);
            } else {
                params.delete('date_fin');
            }
            const baseUrl = '{{ route("reports.service_sales.emplacement", ["emplacement_id" => $emplacement->id]) }}';
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        });
    }

    if (resetFiltres) {
        resetFiltres.addEventListener('click', function() {
            if (dateDebut) dateDebut.value = '';
            if (dateFin) dateFin.value = '';
            if (search) search.value = '';
            const params = new URLSearchParams(window.location.search);
            params.delete('date_debut');
            params.delete('date_fin');
            const baseUrl = '{{ route("reports.service_sales.emplacement", ["emplacement_id" => $emplacement->id]) }}';
            const query = params.toString();
            window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
        });
    }

    if (search) {
        search.addEventListener('input', function() {
            const filterSearch = search.value.toLowerCase();
            rows.forEach(row => {
                const rowSearch = row.getAttribute('data-search').toLowerCase();
                row.style.display = filterSearch && !rowSearch.includes(filterSearch) ? 'none' : '';
            });
        });
    }
});
</script>

<style>
.sale-day-row:hover {
    background-color: #f8f9fa !important;
    transform: translateY(-1px);
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}

.badge {
    font-size: 12px;
    padding: 6px 10px;
}

.box.bg-primary, .box.bg-success, .box.bg-info {
    border-radius: 8px;
    border: none;
}

.box.bg-primary .box-body, 
.box.bg-success .box-body, 
.box.bg-info .box-body {
    padding: 20px;
}
</style>
@endpush




