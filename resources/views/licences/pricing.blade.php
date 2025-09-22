@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
    <!-- Content Header (Page header) -->
    <!-- <div class="content-header">
        <div class="d-flex align-items-center justify-content-center">
            <div class="me-auto">
                <h3 class="page-title">Licences</h3>
               
            </div>

        </div>
    </div> -->

    <!-- Main content -->
    <section class="content">
        <div class="row gap-y text-center d-flex justify-content-center">
            <div class="col-md-6 col-lg-3">
                <div class="box shadow-1 hover-shadow-3 transition-5s bg-white b-1 border-warning">
                        <p class="text-uppercase fw-400 bb-1 py-10 ls-2">Plan gratuit</p>
                    <div class="box-body">
                        <h2 class="fw-500 fs-60"><span class="price-dollar">$</span> 0</h2>
                        <p>Gestion de restaurent & lounge bar complet pour plus de 5 utilisateurs. durée 15 jours</p>
                        <div class="d-block">
                            <a class="btn btn-warning btn-sm" href="#">Obtenir l'activation</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="box shadow-1 hover-shadow-3 transition-5s bg-white b-1 border-success">
                        <p class="text-uppercase fw-400 bb-1 py-10 ls-2">Plan basic</p>
                    <div class="box-body">
                        <h2 class="fw-500 fs-60"><span class="price-dollar">$</span> 10</h2>
                        <p class="text-success">Gestion de restaurent & lounge bar complet pour plus de 10 utilisateurs</p>
                        <div class="d-block">
                            <a class="btn btn-success btn-sm" href="#">Obtenir l'activation</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="box shadow-1 hover-shadow-3 transition-5s bg-white b-1 border-primary">
                        <p class="text-uppercase fw-400 bb-1 py-10 ls-2">Plan Business</p>
                    <div class="box-body">
                        <h2 class="fw-500 fs-60"><span class="price-dollar">$</span> 30</h2>
                        <p class="text-primary">Gestion de restaurent & lounge bar complet & module de Gestion d'hôtel</p>
                        <div class="d-block">
                            <a class="btn btn-primary btn-sm" href="#">Obtenir l'activation</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="box shadow-1 hover-shadow-3 transition-5s bg-white b-1 border-primary">
                        <p class="text-uppercase fw-400 bb-1 py-10 ls-2">Plan Entreprise</p>
                    <div class="box-body">
                        <h2 class="fw-500 fs-60"><span class="price-dollar">$</span> 50</h2>
                        <p class="text-primary">Gestion de restaurent & lounge bar complet & module de Gestion d'hôtel + la comptabilité</p>
                        <div class="d-block">
                            <a class="btn btn-primary btn-sm" href="#">Obtenir l'activation</a>
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

