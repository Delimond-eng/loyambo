@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
    <div class="container-full">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="d-flex align-items-center">
            <div class="me-auto">
                <h3 class="page-title">Licences</h3>
                <!-- <div class="d-inline-block align-items-center">
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                            <li class="breadcrumb-item" aria-current="page">Factures</li>
                            <li class="breadcrumb-item active" aria-current="page">Liste des factures</li>
                        </ol>
                    </nav>
                </div> -->
            </div>

        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="row gap-y text-center">
                <div class="col-md-6 col-lg-3">
                    <div class="box shadow-1 hover-shadow-3 transition-5s bg-white b-1 border-success">
                        <p class="text-uppercase fw-400 bb-1 py-10 ls-2">Free</p>
                        <br>
                        <h2 class="fw-500 fs-60"><span class="price-dollar">$</span> 0</h2>
                        <br>
                        <small><b>1</b> Agent</small><br>
                        <small><b>1</b> Item Support</small><br>
                        <br>
                        <div class="d-block">
                            <a class="btn btn-success" href="#">Get Started</a>
                        </div>
                        <br><br>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="box shadow-1 hover-shadow-3 transition-5s bg-white b-1 border-info">
                        <p class="text-uppercase fw-400 bb-1 py-10 ls-2">Personal</p>
                        <br>
                        <h2 class="fw-500 fs-60"><span class="price-dollar">$</span> 9 <span class="price-interval">/mo</span>
                        </h2>
                        <br>

                        <small><b>1</b> Agent</small><br>
                        <small><b>10</b> Item Support</small><br>
                        <br>
                        <div class="d-block">
                            <a class="btn btn-info" href="#">Get Started</a>
                        </div>
                        <br><br>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="box shadow-1 hover-shadow-3 transition-5s bg-white b-1 border-primary">
                        <p class="text-uppercase fw-400 bb-1 py-10 ls-2">Team</p>
                        <br>
                        <h2 class="fw-500 fs-60"><span class="price-dollar">$</span> 39 <span class="price-interval">/mo</span>
                        </h2>
                        <br>

                        <small><b>5</b> Agent</small><br>
                        <small><b>25</b> Item Support</small><br>
                        <br>
                        <div class="d-block">
                            <a class="btn btn-primary" href="#">Get Started</a>
                        </div>
                        <br><br>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="box shadow-1 hover-shadow-3 transition-5s bg-white b-1 border-warning">
                        <p class="text-uppercase fw-400 bb-1 py-10 ls-2">Business</p>
                        <br>
                        <h2 class="fw-500 fs-60"><span class="price-dollar">$</span> 89 <span class="price-interval">/mo</span>
                        </h2>
                        <br>

                        <small><b>50</b> Agent</small><br>
                        <small><b>250</b> Item Support</small><br>

                        <br>
                        <div class="d-block">
                            <a class="btn btn-warning" href="#">Get Started</a>
                        </div>
                        <br><br>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
    </div>
</div>
@endsection

