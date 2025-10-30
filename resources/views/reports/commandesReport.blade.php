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
                            <h4 class="box-title">Rapport des Commandes</h4>
                            <p class="text-muted">Suivi des commandes et analyse des performances</p>
                        </div>

                        <!-- Filtres -->
                        <div class="box-body">
                            <form method="GET" action="{{ route('reports.commandes') }}">
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <label for="date_debut">Date début</label>
                                        <input type="date" name="date_debut" id="date_debut" class="form-control" 
                                               value="{{ request('date_debut') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="date_fin">Date fin</label>
                                        <input type="date" name="date_fin" id="date_fin" class="form-control" 
                                               value="{{ request('date_fin') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="emplacement_id">Emplacement</label>
                                        <select name="emplacement_id" id="emplacement_id" class="form-control">
                                            <option value="">Tous</option>
                                            @foreach($emplacements as $emp)
                                                <option value="{{ $emp->id }}" {{ request('emplacement_id') == $emp->id ? 'selected' : '' }}>
                                                    {{ $emp->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="statut">Statut</label>
                                        <select name="statut" id="statut" class="form-control">
                                            <option value="">Tous</option>
                                            <option value="payée" {{ request('statut') == 'payée' ? 'selected' : '' }}>Payée</option>
                                            <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                            <option value="annulée" {{ request('statut') == 'annulée' ? 'selected' : '' }}>Annulée</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="statut_service">Statut Service</label>
                                        <select name="statut_service" id="statut_service" class="form-control">
                                            <option value="">Tous</option>
                                            <option value="en_attente" {{ request('statut_service') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                                            <option value="en_préparation" {{ request('statut_service') == 'en_préparation' ? 'selected' : '' }}>En préparation</option>
                                            <option value="servie" {{ request('statut_service') == 'servie' ? 'selected' : '' }}>Servie</option>
                                            <option value="annulée" {{ request('statut_service') == 'annulée' ? 'selected' : '' }}>Annulée</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Centrer les boutons de filtre -->
                                <div class="row mb-4">
                                    <div class="col-md-12 d-flex justify-content-center">
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-filter"></i> Appliquer les filtres
                                            </button>
                                            <a href="{{ route('reports.commandes') }}" class="btn btn-secondary">
                                                <i class="fa fa-refresh"></i> Réinitialiser
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Statistiques -->
                        <div class="box-body">
                           <div class="row mb-4 justify-content-center text-center">
                           <div class="col-xl-2 col-md-4">
                               <div class="box bg-primary text-white">
                                   <div class="box-body">
                                       <h2 class="mb-0">{{ $stats['total_commandes'] }}</h2>
                                       <p class="mb-0">Total Commandes</p>
                                   </div>
                               </div>
                           </div>

                           <div class="col-xl-2 col-md-4">
                               <div class="box bg-info text-white">
                                   <div class="box-body">
                                       <h2 class="mb-0">{{ $stats['delai_moyen'] }} min</h2>
                                       <p class="mb-0">Délai Moyen</p>
                                   </div>
                               </div>
                           </div>

                           @foreach($stats['repartition_statut_service'] as $statut => $count)
                           <div class="col-xl-2 col-md-4">
                               <div class="box 
                                   @if($statut == 'servie') bg-success
                                   @elseif($statut == 'en_préparation') bg-warning
                                   @elseif($statut == 'en_attente') bg-secondary
                                   @else bg-danger @endif text-white">
                                   <div class="box-body">
                                       <h2 class="mb-0">{{ $count }}</h2>
                                       <p class="mb-0">{{ ucfirst($statut) }}</p>
                                   </div>
                               </div>
                           </div>
                           @endforeach
                       </div>


                            <!-- Tableau des commandes -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="box">
                                        <div class="box-header">
                                            <h4 class="box-title">Liste des Commandes</h4>
                                        </div>
                                        <div class="box-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>N° Commande</th>
                                                            <th>Date/Heure</th>
                                                            <th>Serveur</th>
                                                            <th>Table/Chambre</th>
                                                            <th>Client</th>
                                                            <th>Montant</th>
                                                            <th>Statut</th>
                                                            <th>Statut Service</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($commandes as $commande)
                                                            <tr class="commande-row" data-commande-id="{{ $commande->id }}">
                                                                <td>
                                                                    <strong>{{ $commande->numero_facture }}</strong>
                                                                </td>
                                                                <td>
                                                                   {{ \Carbon\Carbon::parse($commande->date_facture)->format('d/m/Y H:i') }}

                                                                </td>
                                                                <td>
                                                                    {{ $commande->user->name }}
                                                                </td>
                                                                <td>
                                                                    @if($commande->table)
                                                                        Table {{ $commande->table->numero }}
                                                                    @elseif($commande->chambre)
                                                                        Chambre {{ $commande->chambre->numero }}
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($commande->client)
                                                                        {{ $commande->client->nom }}
                                                                        @if($commande->client->telephone)
                                                                            <br>
                                                                            <small class="text-muted">{{ $commande->client->telephone }}</small>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted">Non renseigné</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-end">
                                                                    <strong>{{ number_format($commande->total_ttc, 0, ',', ' ') }} CDF</strong>
                                                                </td>
                                                                <td>
                                                                    <span class="badge 
                                                                        @if($commande->statut == 'payée') badge-success
                                                                        @elseif($commande->statut == 'en_attente') badge-warning
                                                                        @else badge-danger @endif">
                                                                        {{ $commande->statut }}
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <span class="badge 
                                                                        @if($commande->statut_service == 'servie') badge-success
                                                                        @elseif($commande->statut_service == 'en_préparation') badge-warning
                                                                        @elseif($commande->statut_service == 'en_attente') badge-secondary
                                                                        @else badge-danger @endif">
                                                                        {{ $commande->statut_service }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <button class="btn btn-sm btn-primary btn-details" 
                                                                            data-commande-id="{{ $commande->id }}">
                                                                        <i class="fa fa-eye"></i> Détails
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                            <!-- Ligne des détails (cachée par défaut) -->
                                                            <tr class="commande-details" id="details-{{ $commande->id }}" style="display: none;">
                                                                <td colspan="9">
                                                                    <div class="details-content p-3">
                                                                        <div class="text-center">
                                                                            <div class="spinner-border text-primary" role="status">
                                                                                <span class="visually-hidden">Chargement...</span>
                                                                            </div>
                                                                            <p class="mt-2">Chargement des détails...</p>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Pagination centrée -->
                                            <div class="d-flex justify-content-center mt-4">
                                                {{ $commandes->links() }}
                                            </div>
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
    // Gestion de l'affichage des détails
    document.querySelectorAll('.btn-details').forEach(button => {
        button.addEventListener('click', function() {
            const commandeId = this.getAttribute('data-commande-id');
            const detailsRow = document.getElementById('details-' + commandeId);
            
            // Basculer l'affichage
            if (detailsRow.style.display === 'none') {
                // Charger les détails si pas encore chargés
                if (detailsRow.querySelector('.details-content').innerHTML.includes('Chargement')) {
                    chargerDetailsCommande(commandeId);
                }
                detailsRow.style.display = '';
                this.innerHTML = '<i class="fa fa-eye-slash"></i> Masquer';
            } else {
                detailsRow.style.display = 'none';
                this.innerHTML = '<i class="fa fa-eye"></i> Détails';
            }
        });
    });

    function chargerDetailsCommande(commandeId) {
        const detailsContent = document.querySelector('#details-' + commandeId + ' .details-content');
        
        // Utiliser la route correcte pour les détails
        fetch(`/reports/commandes/${commandeId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    afficherDetailsCommande(data, detailsContent);
                } else {
                    detailsContent.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des détails</div>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                detailsContent.innerHTML = '<div class="alert alert-danger">Erreur de connexion: ' + error.message + '</div>';
            });
    }

    function afficherDetailsCommande(data, container) {
        const commande = data.commande;
        const details = data.details;
        const paiements = data.paiements;
        
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <h5>Détails de la commande</h5>
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th>Numéro:</th>
                            <td>${commande.numero_facture}</td>
                        </tr>
                        <tr>
                            <th>Date:</th>
                            <td>${new Date(commande.date_facture).toLocaleString('fr-FR')}</td>
                        </tr>
                        <tr>
                            <th>Serveur:</th>
                            <td>${commande.user.name}</td>
                        </tr>
                        <tr>
                            <th>Emplacement:</th>
                            <td>${commande.table ? commande.table.emplacement.libelle : (commande.chambre ? commande.chambre.emplacement.libelle : 'N/A')}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Informations client</h5>
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th>Nom:</th>
                            <td>${commande.client ? commande.client.nom : 'Non renseigné'}</td>
                        </tr>
                        <tr>
                            <th>Téléphone:</th>
                            <td>${commande.client && commande.client.telephone ? commande.client.telephone : 'Non renseigné'}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>${commande.client && commande.client.email ? commande.client.email : 'Non renseigné'}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <h5>Articles commandés</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <th>Quantité</th>
                                    <th>Prix unitaire</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>`;

        details.forEach(detail => {
            html += `
                <tr>
                    <td>${detail.produit.libelle}</td>
                    <td>${detail.produit.categorie ? detail.produit.categorie.libelle : '-'}</td>
                    <td class="text-center">${detail.quantite}</td>
                    <td class="text-end">${Number(detail.prix_unitaire).toLocaleString('fr-FR')} CDF</td>
                    <td class="text-end">${Number(detail.total_ligne).toLocaleString('fr-FR')} CDF</td>
                </tr>`;
        });

        html += `
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total HT:</th>
                                    <td class="text-end">${Number(commande.total_ht).toLocaleString('fr-FR')} CDF</td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Remise:</th>
                                    <td class="text-end text-danger">-${Number(commande.remise).toLocaleString('fr-FR')} CDF</td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Total TTC:</th>
                                    <td class="text-end"><strong>${Number(commande.total_ttc).toLocaleString('fr-FR')} CDF</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>`;

        if (paiements && paiements.length > 0) {
            html += `
            <div class="row mt-3">
                <div class="col-12">
                    <h5>Paiements</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Mode</th>
                                    <th>Montant</th>
                                    <th>Devise</th>
                                </tr>
                            </thead>
                            <tbody>`;

            paiements.forEach(paiement => {
                html += `
                    <tr>
                        <td>${new Date(paiement.pay_date).toLocaleString('fr-FR')}</td>
                        <td>${paiement.mode}</td>
                        <td class="text-end">${Number(paiement.amount).toLocaleString('fr-FR')}</td>
                        <td class="text-center">${paiement.devise}</td>
                    </tr>`;
            });

            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>`;
        }

        container.innerHTML = html;
    }
});
</script>

<style>
.box.bg-primary, .box.bg-success, .box.bg-info, .box.bg-warning, .box.bg-secondary, .box.bg-danger {
    border-radius: 8px;
    border: none;
}

.box.bg-primary .box-body, 
.box.bg-success .box-body, 
.box.bg-info .box-body,
.box.bg-warning .box-body,
.box.bg-secondary .box-body,
.box.bg-danger .box-body {
    padding: 15px;
}

.badge {
    font-size: 12px;
    padding: 6px 10px;
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}

.commande-details {
    background-color: #f8f9fa;
}

.commande-details td {
    border-top: none;
    padding: 0;
}

.details-content {
    background: white;
    border-radius: 8px;
    margin: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Centrage amélioré pour les boutons de filtre */
.d-flex.justify-content-center .text-center {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}
</style>
@endpush