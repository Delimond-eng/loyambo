@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->

		<div class="content-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="me-auto">
                    <h3 class="page-title">Liste des serveurs</h3>
                    <div class="d-inline-block align-items-center">
                        <nav>
                            <ol class="breadcrumb">
                               <li class="breadcrumb-item ms-1" aria-current="page">Veuillez sélectionner un serveur !</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

		<!-- Main content -->
		<section class="content AppService" v-cloak>
			<div class="row">
                <div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(data, index) in allServeurs" :key="index">
					<a href="#" @click="goToUserOrderSession(data)" class="box box-body d-flex flex-column justify-content-center align-items-center py-30 box-inverse bg-indigo-500">
						<img class="avatar avatar-xxl" src="assets/images/serveur.png" alt="">
						<h5 class="mt-10 mb-1"><span class="text-white hover-danger fw-700">@{{ data.name }}</span></h5>
						<p class="text-white">--- @{{ data.role }} ---</p>
					</a>
				</div>
		    </div>
		</section>
		<!-- /.content -->
	  </div>
  </div>
  <!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush


