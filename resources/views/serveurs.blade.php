@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full AppService">
        <!-- Content Header (Page header) -->
        <div class="data-loading" v-if="isDataLoading">
            <img src="{{ asset("assets/images/loading.gif") }}" alt="loading">
            <h4 class="mt-2">Chargement...</h4>
        </div>

        <div class="content" v-if="!isDataLoading">
            <div class="row d-flex justify-content-center align-items-center">
                <div class="col-xl-12">
                    @include("components.menus.serveurs")
                </div>
                <div class="col-12">
                    <div class="container">
                        <div class="box-body">
                            <div class="box-header border-0 pb-3 d-flex flex-column d-lg-flex justify-content-center align-items-center">
                                <h4 class="box-title text-primary mb-0 fw-600">Liste des serveurs</h4>
                                <h6 class="box-subtitle">Veuillez sélectionner un serveur.</h6>
                            </div>
                            <div class="row d-flex justify-content-center">
                                <div class="col-md-6 col-sm-3 col-lg-2 col-6" v-for="(data, index) in allServeurs" :key="index">
                                    <a href="#" @click="goToUserOrderSession(data)" class="box box-body d-flex flex-column justify-content-center align-items-center py-30 box-inverse bg-indigo-500">
                                        <img class="avatar avatar-xxl" src="assets/images/serveur.png" alt="">
                                        <h5 class="mt-10 mb-1"><span class="text-white hover-danger fw-700">@{{ data.name }}</span></h5>
                                        <p class="text-white">--- @{{ data.role }} ---</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/service.js") }}"></script>
@endpush


