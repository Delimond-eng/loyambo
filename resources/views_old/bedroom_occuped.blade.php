

@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->	  

        <!-- Main content -->
		<section class="content AppPlace" v-cloak>
			<div class="row d-flex justify-content-center g-4">
                <div class="col-xl-12">
                    @include("components.menus.emplacements")
                </div>
                <div class="col-12" v-for="(data, index) in allEmplacements" :key="index">
                    <div class="box-body" v-if="data.chambres.length">
                        <div class="box-header pb-3 d-grid d-lg-flex d-sm-grid justify-content-center align-items-center">
                            <h4 class="box-title text-primary mb-0 fw-600"><i class="ti-home me-15"></i> @{{ data.libelle }}</h4>
                        </div>
                        <div class="row mt-25 d-flex justify-content-center" v-if="data.chambres">
                            <div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(chambre, i) in data.chambres">
                                <a href="#" class="box box-shadowed b-3 border-primary">
                                    <div class="box-body ribbon-box">
                                        <div class="ribbon-two" :class="{'ribbon-two-danger': chambre.statut==='occupée', 'ribbon-two-success':chambre.statut==='libre','ribbon-two-warning':chambre.statut==='réservée' }"><span>@{{ chambre.statut }}</span></div>
                                        <img :src="chambre.statut==='libre' ? 'assets/images/bed-empty.png' : 'assets/images/bed-2.png'" class="img-fluid">
                                        <div style="position:absolute; left: 20px; bottom: 20px;" class="bg-primary fw-900 rounded-circle w-40 h-40 l-h-40 text-center">
                                            @{{ chambre.numero }}
                                        </div>
                                        <span style="position:absolute; right: 20px; top: 20px;" class="badge badge-pill badge-primary-light">@{{ chambre.type }}</span>
                                    </div> <!-- end box-body-->
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
		    </div>
		</section>
		<!-- /.content -->
    </div>
</div>
@endsection
@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/places.js") }}"></script>	
@endpush