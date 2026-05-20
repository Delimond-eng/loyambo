@extends("layouts.admin")


@section('content')

 <!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->	  
        <div class="content-header">
            <div class="d-flex align-items-center justify-content-center">
                <h3 class="page-title">Paramètres</h3>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="row justify-content-center">
                <div class="col-xxxl-4 col-lg-8 col-12">
                    <div class="box">
                        <div class="box-body">
                            <div class="d-flex align-items-center">
                                <img class="me-10 rounded-circle avatar avatar-xl b-2 border-primary" src="assets/images/profil-2.png" alt="">
                                <div>
                                    <h4 class="mb-0">{{ Auth::user()->name }}</h4>
                                    <span class="fs-14 text-info">{{ Auth::user()->role }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="box-body border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-phone me-10 fs-24"></i>
                                <h4 class="mb-0">{{ Auth::user()->etablissement->telephone }}</h4>
                            </div>
                        </div>
                        <div class="box-body border-bottom">
                            <div class="d-flex align-items-center">
                                <i class="fa fa-map-marker me-10 fs-24"></i>
                                <h4 class="mb-0 text-black">{{ Auth::user()->etablissement->nom }},  {{ Auth::user()->etablissement->adresse }}</h4>
                            </div>
                        </div>
                        <div class="box-body border-bottom LicenceApp">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-key me-10 fs-24"></i>
                                    <h4 class="mb-0 text-black">Licence {{ auth()->user()->etablissement->licence->type }} -- <small class="text-primary">({{ now()->diffInDays(auth()->user()->etablissement->licence->date_fin, false) }} j restants)</small></h4>
                                </div>
                                <button  @click="openActiveAppModal" class="btn btn-xs btn-primary"> <i class="mdi mdi-key-plus me-1"></i> Activer </button>
                            </div>
                            <div class="modal fade app-active-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="myModalLabel">Activation Licence</h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-danger"><small>Veuillez renseigner le nombre de mois à activer !</small></p>
                                            <div class="input-group">
                                                <input type="number" v-model="months" class="form-control me-2" placeholder="Nombre des mois à activer.. ex:2">
                                                <button class="btn btn-primary btn-sm w-150" @click="activeApp"><i class="mdi mdi-key-plus me-1"></i>Activer <span v-if="isLoading" class="spinner-border spinner-border-sm ms-1"></span></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>
                            @include("components.modals.modal_licence")
                        </div>
                        <div class="box-body ConfigApp">
                            <h4 class="mb-10">Module de comptabilité  <small v-if="getMessage !== ''"><span class="badge ms-3" :class="getStatus === 1 ? 'badge-warning-light' :'badge-success-light'">@{{ getMessage }}</span></small></h4>
                            <div class="d-lg-flex d-xl-flex d-xxxl-flex d-xxl-flex d-grid overflow-scroll justify-content-between align-items-center">
                                <p class="text-success">{{ auth()->user()->etablissement->token }}</p>
                                <button v-if="getStatus === '' || getStatus === 1 " class="btn btn-xs" :class="getStatus === '' ? 'btn-success' : 'btn-warning'" @click="openConfigModal"> <i class="mdi mdi-link me-1"></i> Lier au module comptable </button>
                            </div>

                            <div class="modal fade config-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-modal="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="myModalLabel">Liaison avec la comptabilité   <small v-if="getMessage !== ''"><span class="badge ms-3" :class="getStatus === 1 ? 'badge-warning-light' :'badge-success-light'">@{{ getMessage }}</span></small></h4>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="text-danger"><small>Veuillez renseigner le code société fourni par l'application de la comptabilité !</small></p>
                                            <div class="input-group">
                                                <input type="text" v-model="code" class="form-control me-2" placeholder="Entrer le code société...">
                                                <button class="btn btn-primary btn-sm" @click="sendRequest" :disabled="isLoading"><i class="mdi mdi-link me-1"></i>Envoyer la demande <span v-if="isLoading" class="spinner-border spinner-border-sm ms-1"></span></button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
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

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/licence.js") }}"></script>
    <script type="module" src="{{ asset("assets/js/scripts/config.js") }}"></script>
@endpush