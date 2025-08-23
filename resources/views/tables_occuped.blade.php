

@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->	  
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="me-auto">
                    <h3 class="page-title">Occupation des tables</h3>
                    <!-- <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="mdi mdi-home-outline"></i></a></li>
                                <li class="breadcrumb-item" aria-current="page">Tables</li>
                                <li class="breadcrumb-item active" aria-current="page">occupation</li>
                            </ol>
                        </nav>
                    </div> -->
                </div>
                
            </div>
        </div>

        <!-- Main content -->
		<section class="content AppPlace" v-cloak>
			<div class="row">
                <div class="col-12" v-for="(data, index) in allEmplacements" :key="index">
                    <div class="box-body" v-if="data.tables.length">
                        <h4 class="box-title text-primary mb-0 fw-600"><i class="ti-home me-15"></i> @{{ data.libelle }}</h4>
                        <hr class="my-15">
                        <div class="row" v-if="data.tables">
                            <div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(table, i) in data.tables">
                                <a href="#" class="box">
                                    <div class="box-body ribbon-box">
                                        <div class="ribbon-two" :class="{'ribbon-two-danger': table.statut==='occupée', 'ribbon-two-success':table.statut==='libre','ribbon-two-warning':table.statut==='réservée' }"><span>@{{ table.statut }}</span></div>
                                        <img v-if="data.type !== 'hôtel'" :src="table.statut==='libre' ? 'assets/images/table4.png' : 'assets/images/table-reseved.png'" class="img-fluid">
                                        <img v-else :src="table.statut==='libre' ? 'assets/images/bed-empty.png' : 'assets/images/bed-2.png'" class="img-fluid">
                                        <div style="position:absolute; left: 20px; bottom: 20px;" class="bg-primary fw-900 rounded-circle w-40 h-40 l-h-40 text-center">
                                            @{{ table.numero }}
                                        </div>
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