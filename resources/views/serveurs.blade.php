@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->

		<!-- Main content -->
		<section class="content AppService" v-cloak>
			<div class="row">
			  <div class="col-12">
                    <div class="box box-primary">
                        <div class="box-header d-flex align-items-center justify-content-center" style="padding: 1rem">
                            <h4 class="box-title fw-900"><span class="fw-900 text-white text-uppercase">Sélectionnez votre compte</span></h4>
                        </div>
                    </div>
			    </div>
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


