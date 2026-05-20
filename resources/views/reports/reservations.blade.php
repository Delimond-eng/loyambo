@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="col-xl-12">
                    @include("components.menus.reports")
                </div>
            </div>
        </div>

        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border p-5 text-center">
                            <h4 class="box-title">Rapport reservations hotel</h4>
                            <p class="text-muted mb-0">Suivi des reservations de chambres avec type de sejour et paiements.</p>
                        </div>

                        <div class="box-body">
                            <form method="GET" action="{{ route('reports.reservations') }}" class="mb-4">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label for="search">Recherche</label>
                                        <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Client, chambre, facture">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="date_debut">Date debut</label>
                                        <input type="date" id="date_debut" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="date_fin">Date fin</label>
                                        <input type="date" id="date_fin" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="type_sejour">Type sejour</label>
                                        <select id="type_sejour" name="type_sejour" class="form-control">
                                            <option value="">Tous</option>
                                            <option value="nuit" {{ request('type_sejour') === 'nuit' ? 'selected' : '' }}>Nuitee</option>
                                            <option value="passage" {{ request('type_sejour') === 'passage' ? 'selected' : '' }}>Passage</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="type_chambre">Type chambre</label>
                                        <select id="type_chambre" name="type_chambre" class="form-control">
                                            <option value="">Tous</option>
                                            @foreach($typesChambre as $typeChambre)
                                                <option value="{{ $typeChambre }}" {{ request('type_chambre') === $typeChambre ? 'selected' : '' }}>{{ $typeChambre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-4">
                                        <label for="statut_reservation">Statut reservation</label>
                                        <select id="statut_reservation" name="statut_reservation" class="form-control">
                                            <option value="">Tous</option>
                                            <option value="en_attente" {{ request('statut_reservation') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                                            <option value="confirmee" {{ request('statut_reservation') === 'confirmee' ? 'selected' : '' }}>Confirmee</option>
                                            <option value="terminee" {{ request('statut_reservation') === 'terminee' ? 'selected' : '' }}>Terminee</option>
                                            <option value="annulee" {{ request('statut_reservation') === 'annulee' ? 'selected' : '' }}>Annulee</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="statut_paiement">Statut paiement</label>
                                        <select id="statut_paiement" name="statut_paiement" class="form-control">
                                            <option value="">Tous</option>
                                            <option value="payee" {{ request('statut_paiement') === 'payee' ? 'selected' : '' }}>Payee</option>
                                            <option value="partiel" {{ request('statut_paiement') === 'partiel' ? 'selected' : '' }}>Partiel</option>
                                            <option value="en_attente" {{ request('statut_paiement') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                                            <option value="non_facturee" {{ request('statut_paiement') === 'non_facturee' ? 'selected' : '' }}>Non facturee</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="mode_paiement">Mode paiement</label>
                                        <select id="mode_paiement" name="mode_paiement" class="form-control">
                                            <option value="">Tous</option>
                                            @foreach($modesPaiement as $mode)
                                                <option value="{{ $mode }}" {{ request('mode_paiement') === $mode ? 'selected' : '' }}>{{ ucfirst($mode) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-filter"></i> Appliquer
                                        </button>
                                        <a href="{{ route('reports.reservations') }}" class="btn btn-secondary">
                                            <i class="fa fa-refresh"></i> Reinitialiser
                                        </a>
                                    </div>
                                </div>
                            </form>

                            @php
                                $resteGlobal = max(0, (float)$stats['montant_total'] - (float)$stats['total_paye']);
                            @endphp
                            <div class="row mb-4">
                                <div class="col-xl-2 col-md-6">
                                    <div class="box bg-primary text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                                            <p class="mb-0">Reservations</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-6">
                                    <div class="box bg-success text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">{{ $stats['confirmees'] }}</h3>
                                            <p class="mb-0">Confirmees</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-6">
                                    <div class="box bg-warning text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">{{ $stats['en_attente'] }}</h3>
                                            <p class="mb-0">En attente</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-6">
                                    <div class="box bg-danger text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">{{ $stats['annulees'] }}</h3>
                                            <p class="mb-0">Annulees</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-6">
                                    <div class="box bg-info text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">{{ number_format($stats['total_paye'], 0, ',', ' ') }}</h3>
                                            <p class="mb-0">Total paye</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-2 col-md-6">
                                    <div class="box bg-secondary text-white">
                                        <div class="box-body text-center">
                                            <h3 class="mb-0">{{ number_format($resteGlobal, 0, ',', ' ') }}</h3>
                                            <p class="mb-0">Reste</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date reservation</th>
                                            <th>Client</th>
                                            <th>Chambre</th>
                                            <th>Type</th>
                                            <th>Periode</th>
                                            <th class="text-end">Montant</th>
                                            <th class="text-end">Paye</th>
                                            <th class="text-end">Reste</th>
                                            <th>Paiement</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reservations as $reservation)
                                            @php
                                                $facture = $reservation->facture;
                                                $chambre = $reservation->chambre;
                                                $client = $reservation->client;
                                                $devise = $facture?->devise ?? $chambre?->prix_devise ?? 'USD';
                                                $montantReservation = (float)($reservation->prix_facture ?? $reservation->prix_base ?? ($facture?->total_ttc ?? 0));
                                                $totalPaye = (float)($facture?->payments?->sum('amount') ?? 0);
                                                $reste = max(0, $montantReservation - $totalPaye);
                                                $modes = $facture ? $facture->payments->pluck('mode')->filter()->unique()->implode(', ') : '-';

                                                $reservationStatus = (string)$reservation->statut;
                                                $reservationStatusClass = 'badge-secondary';
                                                if ($reservationStatus === 'en_attente') {
                                                    $reservationStatusClass = 'badge-warning';
                                                } elseif (str_starts_with(strtolower($reservationStatus), 'confirm')) {
                                                    $reservationStatusClass = 'badge-success';
                                                } elseif (str_starts_with(strtolower($reservationStatus), 'termin')) {
                                                    $reservationStatusClass = 'badge-primary';
                                                } elseif (str_starts_with(strtolower($reservationStatus), 'annul')) {
                                                    $reservationStatusClass = 'badge-danger';
                                                }

                                                $paymentStatus = $facture?->statut ?? 'non_facturee';
                                                $paymentStatusClass = 'badge-secondary';
                                                if ($paymentStatus === 'non_facturee') {
                                                    $paymentStatusClass = 'badge-dark';
                                                } elseif (str_starts_with(strtolower((string)$paymentStatus), 'pay')) {
                                                    $paymentStatusClass = 'badge-success';
                                                } elseif (str_starts_with(strtolower((string)$paymentStatus), 'part')) {
                                                    $paymentStatusClass = 'badge-info';
                                                } elseif ($paymentStatus === 'en_attente') {
                                                    $paymentStatusClass = 'badge-warning';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ optional($reservation->created_at)->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <strong>{{ $client?->nom ?? '-' }}</strong><br>
                                                    <small class="text-muted">{{ $client?->telephone ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    CH-{{ $chambre?->numero ?? '-' }}<br>
                                                    <small class="text-muted">{{ $chambre?->emplacement?->libelle ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light">{{ $chambre?->type ?? '-' }}</span>
                                                    <span class="badge badge-pill {{ $reservation->type_sejour === 'passage' ? 'badge-info' : 'badge-primary' }}">
                                                        {{ $reservation->type_sejour === 'passage' ? 'Passage' : 'Nuitee' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ optional($reservation->date_debut)->format('d/m/Y') ?? '-' }}
                                                    <span class="text-muted">au</span>
                                                    {{ optional($reservation->date_fin)->format('d/m/Y') ?? '-' }}
                                                </td>
                                                <td class="text-end fw-600">{{ number_format($montantReservation, 0, ',', ' ') }} {{ $devise }}</td>
                                                <td class="text-end text-success fw-600">{{ number_format($totalPaye, 0, ',', ' ') }} {{ $devise }}</td>
                                                <td class="text-end text-danger fw-600">{{ number_format($reste, 0, ',', ' ') }} {{ $devise }}</td>
                                                <td>
                                                    <span class="badge {{ $paymentStatusClass }}">{{ str_replace('_', ' ', $paymentStatus) }}</span><br>
                                                    <small class="text-muted">{{ $modes !== '' ? $modes : '-' }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $reservationStatusClass }} mb-2">{{ str_replace('_', ' ', $reservationStatus) }}</span><br>
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-view-reservation" data-id="{{ $reservation->id }}">
                                                        Voir details
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">Aucune reservation trouvee.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $reservations->links('vendor.pagination.vue-table') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal fade" id="reservationReportDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Details reservation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reservationReportDetailBody">
                <div class="text-center py-4">
                    <span class="spinner-border"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailBody = document.getElementById('reservationReportDetailBody');
    const baseUrl = '{{ url("/reports/reservations") }}';

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDate(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;
        return date.toLocaleString('fr-FR');
    }

    function formatAmount(value) {
        const number = Number(value || 0);
        return number.toLocaleString('fr-FR');
    }

    function labelMode(mode) {
        if (!mode) return '-';
        const map = {
            cash: 'Especes',
            mobile: 'Mobile',
            card: 'Carte',
            virement: 'Virement',
            cheque: 'Cheque'
        };
        return map[mode] || mode;
    }

    function cleanStatus(value) {
        return String(value || '-').replace(/_/g, ' ');
    }

    function labelTypeSejour(typeSejour) {
        if (typeSejour === 'passage') return 'Passage';
        if (typeSejour === 'nuit') return 'Nuitee';
        return typeSejour || '-';
    }

    function paymentRows(payments) {
        if (!payments || payments.length === 0) {
            return '<tr><td colspan="5" class="text-center text-muted">Aucun paiement.</td></tr>';
        }

        return payments.map(function(payment) {
            return `
                <tr>
                    <td>${escapeHtml(formatDate(payment.pay_date))}</td>
                    <td>${escapeHtml(labelMode(payment.mode))}</td>
                    <td>${escapeHtml(payment.mode_ref || '-')}</td>
                    <td class="text-end fw-600">${escapeHtml(formatAmount(payment.amount))} ${escapeHtml(payment.devise || '')}</td>
                    <td>${escapeHtml(payment.caissier || '-')}</td>
                </tr>
            `;
        }).join('');
    }

    function renderDetail(data) {
        const reservation = data.reservation || {};
        const client = data.client || {};
        const chambre = data.chambre || {};
        const facture = data.facture || null;
        const totaux = data.totaux || {};
        const devise = chambre.devise || facture?.devise || '';

        detailBody.innerHTML = `
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">Reservation</h6>
                        <p class="mb-1"><strong>ID:</strong> ${escapeHtml(reservation.id)}</p>
                        <p class="mb-1"><strong>Statut:</strong> ${escapeHtml(cleanStatus(reservation.statut))}</p>
                        <p class="mb-1"><strong>Type sejour:</strong> ${escapeHtml(labelTypeSejour(reservation.type_sejour))}</p>
                        <p class="mb-1"><strong>Debut:</strong> ${escapeHtml(formatDate(reservation.date_debut))}</p>
                        <p class="mb-1"><strong>Fin:</strong> ${escapeHtml(formatDate(reservation.date_fin))}</p>
                        <p class="mb-0"><strong>Creee le:</strong> ${escapeHtml(formatDate(reservation.created_at))}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">Client</h6>
                        <p class="mb-1"><strong>Nom:</strong> ${escapeHtml(client.nom || '-')}</p>
                        <p class="mb-1"><strong>Telephone:</strong> ${escapeHtml(client.telephone || '-')}</p>
                        <p class="mb-1"><strong>Email:</strong> ${escapeHtml(client.email || '-')}</p>
                        <p class="mb-1"><strong>Identite:</strong> ${escapeHtml(client.identite || '-')}</p>
                        <p class="mb-0"><strong>Type identite:</strong> ${escapeHtml(client.identite_type || '-')}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="mb-2">Chambre</h6>
                        <p class="mb-1"><strong>Numero:</strong> CH-${escapeHtml(chambre.numero || '-')}</p>
                        <p class="mb-1"><strong>Type:</strong> ${escapeHtml(chambre.type || '-')}</p>
                        <p class="mb-1"><strong>Emplacement:</strong> ${escapeHtml(chambre.emplacement || '-')}</p>
                        <p class="mb-1"><strong>Montant reservation:</strong> ${escapeHtml(formatAmount(totaux.montant_reservation))} ${escapeHtml(devise)}</p>
                        <p class="mb-1"><strong>Total paye:</strong> ${escapeHtml(formatAmount(totaux.total_paye))} ${escapeHtml(devise)}</p>
                        <p class="mb-0"><strong>Reste:</strong> ${escapeHtml(formatAmount(totaux.reste))} ${escapeHtml(devise)}</p>
                    </div>
                </div>

                <div class="col-12">
                    <div class="border rounded p-3">
                        <h6 class="mb-2">Facture</h6>
                        ${facture ? `
                            <div class="row">
                                <div class="col-md-3"><strong>Numero:</strong> ${escapeHtml(facture.numero_facture || '-')}</div>
                                <div class="col-md-3"><strong>Statut:</strong> ${escapeHtml(cleanStatus(facture.statut))}</div>
                                <div class="col-md-3"><strong>Total TTC:</strong> ${escapeHtml(formatAmount(facture.total_ttc))} ${escapeHtml(facture.devise || '')}</div>
                                <div class="col-md-3"><strong>Date:</strong> ${escapeHtml(formatDate(facture.date_facture))}</div>
                            </div>
                        ` : '<p class="text-muted mb-0">Aucune facture associee.</p>'}
                    </div>
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date paiement</th>
                                    <th>Mode</th>
                                    <th>Reference</th>
                                    <th class="text-end">Montant</th>
                                    <th>Caissier</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${paymentRows(data.payments || [])}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
    }

    document.querySelectorAll('.btn-view-reservation').forEach(function(button) {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            if (!id) return;

            detailBody.innerHTML = '<div class="text-center py-4"><span class="spinner-border"></span></div>';
            $('#reservationReportDetailModal').modal('show');

            fetch(`${baseUrl}/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(function(response) {
                if (!response.ok) throw new Error('Reponse invalide');
                return response.json();
            })
            .then(function(data) {
                if (!data.success) {
                    throw new Error(data.message || 'Erreur de chargement');
                }
                renderDetail(data);
            })
            .catch(function(error) {
                detailBody.innerHTML = `<p class="text-danger mb-0">Impossible de charger les details: ${escapeHtml(error.message)}</p>`;
            });
        });
    });
});
</script>
@endpush
