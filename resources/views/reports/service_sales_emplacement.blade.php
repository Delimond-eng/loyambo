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
                            <h4 class="box-title">Journées de vente - {{ $emplacement->libelle }}</h4>
                            <p class="text-muted">Sélectionnez une journée pour voir les détails des ventes</p>
                        </div>
                        
                        <!-- Filtres -->
                        <div class="box-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="date_debut">Date début</label>
                                    <input type="date" id="date_debut" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="date_fin">Date fin</label>
                                    <input type="date" id="date_fin" class="form-control">
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
                                        <i class="fa fa-refresh"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des journées de vente -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date de vente</th>
                                            <th>Heure début</th>
                                            <th>Heure fin</th>
                                            <th>Factures payées</th>
                                            <th>Total des ventes</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($saledays as $saleday)
                                            @php
                                                // Filtrer les factures par emplacement et statut payée
                                                $facturesFiltrees = $saleday->factures->filter(function($facture) use ($emplacement) {
                                                    // Vérifier l'emplacement
                                                    $emplacementOk = false;
                                                    if ($emplacement->type === 'hotel') {
                                                        $emplacementOk = $facture->chambre && $facture->chambre->emplacement_id == $emplacement->id;
                                                    } else {
                                                        $emplacementOk = $facture->table && $facture->table->emplacement_id == $emplacement->id;
                                                    }
                                                    
                                                    // Vérifier le statut payée
                                                    $statutOk = $facture->statut === 'payée';
                                                    
                                                    return $emplacementOk && $statutOk;
                                                });
                                                
                                                $totalFacturesPayees = $facturesFiltrees->count();
                                                $totalVentesPayees = $facturesFiltrees->sum('total_ttc');
                                                
                                                // Récupérer toutes les devises utilisées dans les paiements des factures payées
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
                                                        {{ $totalFacturesPayees }} factures payées
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
                                                        <span class="badge badge-success">Terminée</span>
                                                    @else
                                                        <span class="badge badge-warning">En cours</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('reports.service.vente.details', ['id_saleDay'=>$saleday->id,"emplacement_id"=>$emplacement->id]) }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fa fa-eye"></i> Voir détails
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $saledays->links() }}
                            </div>

                            <!-- Statistiques résumées -->
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">{{ $saledays->total() }}</h3>
                                            <p class="mb-0">Journées totales</p>
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
                                                            return $emplacementOk && $facture->statut === 'payée';
                                                        });
                                                        $totalFacturesGlobal += $facturesFiltrees->count();
                                                    });
                                                @endphp
                                                {{ $totalFacturesGlobal }}
                                            </h3>
                                            <p class="mb-0">Factures payées totales</p>
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
                                                            return $emplacementOk && $facture->statut === 'payée';
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
                                                    
                                                    // Déterminer l'affichage de la devise pour les stats globales
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
                                            <p class="mb-0">Chiffre d'affaires (payé)</p>
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

    // Appliquer les filtres
    appliquerFiltres.addEventListener('click', function() {
        filterRows();
    });

    // Réinitialiser les filtres
    resetFiltres.addEventListener('click', function() {
        dateDebut.value = '';
        dateFin.value = '';
        search.value = '';
        filterRows();
    });

    // Recherche en temps réel
    search.addEventListener('input', function() {
        filterRows();
    });

    function filterRows() {
        const filterDateDebut = dateDebut.value;
        const filterDateFin = dateFin.value;
        const filterSearch = search.value.toLowerCase();

        rows.forEach(row => {
            const rowDate = row.getAttribute('data-date');
            const rowSearch = row.getAttribute('data-search').toLowerCase();
            
            let showRow = true;

            // Filtre par date
            if (filterDateDebut && rowDate < filterDateDebut) {
                showRow = false;
            }
            if (filterDateFin && rowDate > filterDateFin) {
                showRow = false;
            }

            // Filtre par recherche - recherche dans tout le contenu de data-search
            if (filterSearch && !rowSearch.includes(filterSearch)) {
                showRow = false;
            }

            // Afficher ou masquer la ligne
            row.style.display = showRow ? '' : 'none';
        });
    }

    // Rendre les lignes cliquables
    rows.forEach(row => {
        const link = row.querySelector('a');
        if (link) {
            row.style.cursor = 'pointer';
            
            row.addEventListener('click', function(e) {
                // Éviter de déclencher le clic si on clique sur le bouton
                if (!e.target.closest('a, button')) {
                    link.click();
                }
            });
        }

        // Effet hover
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
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