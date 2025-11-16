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
                        <div class="box-body border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-key me-10 fs-24"></i>
                                    <h4 class="mb-0 text-black">Licence {{ auth()->user()->etablissement->licence->type }} -- <small class="text-primary">({{ now()->diffInDays(auth()->user()->etablissement->licence->date_fin, false) }} j restants)</small></h4>
                                </div>
                                <a href="{{ route('licence.payment', ['ets_id' => auth()->user()->ets_id]) }}" class="btn btn-xs btn-primary"> <i class="mdi mdi-key-plus me-1"></i> Activer </a>
                            </div>
                        </div>
                        <div class="box-body">
                            <h4 class="mb-10">Module de comptabilité </h4>
                            <div class="d-lg-flex d-xl-flex d-xxxl-flex d-xxl-flex d-grid overflow-scroll justify-content-between align-items-center">
                                <p class="text-success">{{ auth()->user()->etablissement->token }}</p>
                                <button class="btn btn-xs btn-success"> <i class="mdi mdi-link me-1"></i> Lier au module comptable </button>
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