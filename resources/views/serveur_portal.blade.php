@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
	<div class="container-full AppService" v-cloak data-rate="@lastRate">
        <!-- Loader Moderne -->
		<div class="data-loading text-center py-100" v-if="isDataLoading">
            <div class="spinner-box mx-auto mb-3">
                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
            </div>
			<h4 class="text-muted fw-600">Analyse du plan de salle...</h4>
		</div>

	 	<div class="content-header" v-if="!isDataLoading">

            <div class="d-md-flex align-items-center justify-content-between mb-4">
                <div class="me-auto">
					@if (Auth::user()->role === "serveur")
                    <h3 class="page-title fw-bold">Bonjour, <span class="text-primary">{{ Auth::user()->name }}</span></h3>
					@else
                    <h3 class="page-title fw-bold">Session de <span class="text-primary" v-if="userSession">@{{ userSession.name }}</span></h3>
					@endif
					<p class="text-muted mb-0 small"><i class="fa fa-home me-1 text-primary"></i> {{ Auth::user()->etablissement->nom }}</p>
                </div>

				<div class="d-flex gap-2 mt-3 mt-lg-0">
					<button type="button" @click="setOperation('transfert')" class="btn btn-info-light btn-sm rounded-pill px-3 fw-600" :class="{'bg-info text-white shadow': operation === 'transfert'}">
                        <i class="mdi mdi-swap-horizontal me-1"></i> Transférer
                    </button>
					<button type="button" @click="setOperation('combiner')" class="btn btn-success-light btn-sm rounded-pill px-3 fw-600" :class="{'bg-success text-white shadow': operation === 'combiner'}">
                        <i class="mdi mdi-link me-1"></i> Combiner
                    </button>
					<button type="button" v-if="operation" @click="setOperation('')" class="btn btn-danger-light btn-sm rounded-pill px-3 fw-600">
                        <i class="mdi mdi-cancel me-1"></i> Annuler
                    </button>
				</div>
            </div>
        </div>

        <div v-if="operation" class="alert alert-dismissible shadow-sm border-0 my-3 animate-fadeInDown"
             :class="operation === 'transfert' ? 'alert-info bg-info text-white' : 'alert-success bg-success text-white'">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="me-3 fs-24">
                        <i class="mdi" :class="operation === 'transfert' ? 'mdi-swap-horizontal' : 'mdi-link'"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Mode @{{ operation.toUpperCase() }} activé</h5>
                        <p class="mb-0 small" v-if="selectedTables.length === 0">Veuillez sélectionner la <strong>table source</strong> (Occupée).</p>
                        <p class="mb-0 small" v-if="selectedTables.length === 1">Table source : <strong>@{{ selectedTables[0].numero }}</strong>. Sélectionnez maintenant la <strong>table cible</strong>.</p>
                    </div>
                </div>
                <button type="button" @click="setOperation('')" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                    <i class="mdi mdi-close me-1"></i> ANNULER
                </button>
            </div>
        </div>

		<section class="content pt-0" v-if="!isDataLoading">
            <!-- Parcours des emplacements groupés -->
            <template v-if="Object.keys(groupedTables).length > 0">
                <div v-for="(group, emplacement) in groupedTables" :key="emplacement" class="mb-5">
                    <div class="d-flex align-items-center mb-4">
                        <h5 class="fw-bold text-dark mb-0 text-uppercase letter-spacing-2 fs-13">@{{ emplacement }}</h5>
                        <div class="flex-grow-1 border-bottom ms-3 opacity-10"></div>
                    </div>

                    <div class="row g-4">
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2 col-xl-1" v-for="table in group" :key="table.id">
                            <div class="card h-100 luxury-table-card shadow-xs border-0 transition-all"
                                 :class="[getTableOperationColorClass(table), {'table-occupied-active': table.statut==='occupée'}]"
                                 @click="goToOrderPannel(table)">

                                <!-- Status Badge Flottant -->
                                <div class="status-floating-badge" :class="table.statut">
                                    @{{ table.statut === 'libre' ? 'LIBRE' : (table.statut === 'occupée' ? 'OCCUPÉE' : 'RÉSERVÉE') }}
                                </div>

                                <div class="card-body p-3 text-center d-flex flex-column align-items-center justify-content-between min-h-150">
                                    <div class="position-absolute top-0 end-0 m-2 mt-4" v-if="table.commandes.length > 0 && checkServiceStatus(table.commandes)">
                                        <div class="pulse-green"><i class="mdi mdi-bell-ring text-success fs-18"></i></div>
                                    </div>

                                    <div class="svg-wrapper mt-3">
                                        <template v-if="table.emplacement.type !== 'hôtel'">
                                            <svg v-if="table.statut === 'libre'" width="50" height="50" viewBox="0 0 64 64">
                                                <circle cx="32" cy="32" r="16" fill="#ffffff" stroke="#2EC4B6" stroke-width="2.5"/>
                                                <rect x="29" y="2" width="6" height="8" rx="2" fill="#2EC4B6" opacity="0.3"/>
                                                <rect x="29" y="54" width="6" height="8" rx="2" fill="#2EC4B6" opacity="0.3"/>
                                                <rect x="2" y="29" width="8" height="6" rx="2" fill="#2EC4B6" opacity="0.3"/>
                                                <rect x="54" y="29" width="8" height="6" rx="2" fill="#2EC4B6" opacity="0.3"/>
                                            </svg>
                                            <svg v-else width="50" height="50" viewBox="0 0 64 64" class="svg-occupied">
                                                <circle cx="32" cy="32" r="16" fill="#FFF5F5" stroke="#E71D36" stroke-width="2.5"/>
                                                <circle cx="32" cy="10" r="4" fill="#E71D36"/>
                                                <circle cx="32" cy="54" r="4" fill="#E71D36"/>
                                                <circle cx="10" cy="32" r="4" fill="#E71D36"/>
                                                <circle cx="54" cy="32" r="4" fill="#E71D36"/>
                                            </svg>
                                        </template>
                                        <template v-else>
                                            <svg width="50" height="50" viewBox="0 0 64 64" fill="none">
                                                <rect x="8" y="24" width="48" height="28" rx="4" stroke="currentColor" :class="table.statut === 'libre' ? 'text-primary' : 'text-danger'" stroke-width="2.5"/>
                                                <path d="M12 24v-8h40v8" stroke="currentColor" :class="table.statut === 'libre' ? 'text-primary' : 'text-danger'" stroke-width="2"/>
                                            </svg>
                                        </template>
                                    </div>

                                    <div class="table-info-wrapper mt-2">
                                        <span class="table-label">TABLE</span>
                                        <div class="table-number">@{{ table.numero }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Message Vide -->
            <div v-else class="empty-state-container text-center py-100">
                <div class="empty-icon-box bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                    <i class="mdi mdi-map-marker-off fa-4x text-muted opacity-30"></i>
                </div>
                <h3 class="fw-bold text-dark">Plan de salle vide</h3>
                <p class="text-muted fs-16">Aucun emplacement ou table n'est configuré.</p>
            </div>
		</section>

		@include('components.modals.portal_modals')
	</div>
</div>

@if (Auth::user()->role === 'serveur')
     <button class="fixed-btn" onclick="location.href='/orders'">
		<div class="btn-badge AppDashboard" v-cloak>@{{ counts.pendings ?? 0 }}</div>
		<i class="mdi mdi-basket fs-18"></i>
	</button>
@endif
@endsection

@push("styles")
	<style>
        .luxury-table-card { border-radius: 10px; cursor: pointer; border: 1px solid rgba(0,0,0,0.05); background: #ffffff; min-height: 150px; margin-top: 10px; position: relative; }
        .luxury-table-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.07) !important; border-color: #4361ee; }

        .table-selected-op { border: 2px solid #fff !important; box-shadow: 0 0 0 3px #4361ee !important; }

        .status-floating-badge { position: absolute; top: 0; left: 50%; transform: translate(-50%, -50%); padding: 4px 12px; border-radius: 50px; font-size: 8px; font-weight: 800; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 10; background: #fff; }
        .status-floating-badge.libre { color: #2EC4B6; border: 1px solid #2EC4B6; }
        .status-floating-badge.occupée { color: #E71D36; border: 1px solid #E71D36; }

        .table-info-wrapper { display: flex; flex-direction: column; align-items: center; }
        .table-label { font-size: 9px; font-weight: 700; color: #adb5bd; letter-spacing: 2px; margin-bottom: -5px; }
        .table-number { font-size: 26px; font-weight: 900; color: #343a40; }

        .animate-fadeInDown { animation: fadeInDown 0.4s both; }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        .svg-occupied { animation: heartbeat 2s ease-in-out infinite; }
        @keyframes heartbeat { 0% { transform: scale(1); } 10% { transform: scale(1.05); } 20% { transform: scale(1); } }
        .pulse-green { animation: shadow-pulse 2s infinite; border-radius: 50%; }
        @keyframes shadow-pulse { 0% { box-shadow: 0 0 0 0px rgba(46, 196, 182, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(46, 196, 182, 0); } 100% { box-shadow: 0 0 0 0px rgba(46, 196, 182, 0); } }
    </style>
@endpush
