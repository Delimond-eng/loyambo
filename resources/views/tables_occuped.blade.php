

@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->	  
        <div class="content-header">
            <div class="d-flex align-items-center">
                <div class="me-auto">
                    <h3 class="page-title">Occupation des tables</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                               <li class="breadcrumb-item ms-1" aria-current="page">Les occupations des tables pour les emplacements restaurant,Lounge bar & chambres pour les hôtels</span></li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Main content -->
		<section class="content AppPlace" v-cloak>
			<div class="row">
                <div class="col-12" v-for="(data, index) in allEmplacements" :key="index">
                    <div class="box-body" v-if="data.tables.length">
                        <div class="box-header pb-3 d-grid d-lg-flex d-sm-grid justify-content-between align-items-center">
                            <h4 class="box-title text-primary mb-0 fw-600"><i class="ti-home me-15"></i> @{{ data.libelle }}</h4>
                            <!-- <div class="clearfix">
                                <button type="button" @click="setOperation('transfert')" class="waves-effect waves-light btn btn-sm btn-info mb-2">Transferer @{{  data.type !== 'hôtel' ? 'Table' : 'Chambre' }} <i class="mdi mdi-arrow-expand-left ms-2"></i></button>
                                <button v-if="data.type !== 'hôtel'" type="button" @click="setOperation('combiner')" class="waves-effect waves-light btn btn-sm btn-success mb-2">Combiner Table <i class="mdi mdi-link ms-2"></i></button>
                                <button type="button" @click="setOperation('')" class="waves-effect waves-light btn btn-rounded btn-info mb-2">Reservation <i class="mdi mdi-lock-outline ms-2"></i></button>
                                <button type="button" @click="setOperation('')" class="waves-effect waves-light btn btn-sm btn-danger mb-2">Annuler <i class="mdi mdi-cancel ms-2"></i></button>
                            </div> -->
                        </div>
                        <div class="row mt-25" v-if="data.tables">
                            <div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(table, i) in data.tables">
                                <a href="#" class="box b-3 box-shadowed border-primary">
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