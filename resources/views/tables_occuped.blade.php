

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
		<section class="content">
			<div class="row">
                <div class="col-12">
                    <div class="box-body">
                        <h4 class="box-title text-primary mb-0 fw-600"><i class="ti-location-pin me-15"></i> PAVILLON 01</h4>
                        <hr class="my-15">
                        <div class="row">
                            @for($i=0; $i<5; $i++)
                            <div class="col-md-6 col-sm-3 col-lg-2 col-6">
                                <a href="#" class="box">
                                    <div class="box-body ribbon-box">
                                        <div class="ribbon {{  $i%2 ? 'ribbon-danger' : 'ribbon-success' }}"><span>{{ $i%2 ? "Occupée" : "Libre" }}</span></div>
                                        <img src="{{ $i%2 ? 'assets/images/table5.png' : 'assets/images/table4.avif' }}" class="img-fluid">
                                        <div style="position:absolute; left: 20px; bottom: 20px;" class="bg-primary fw-900 rounded-circle w-40 h-40 l-h-40 text-center">
                                            0{{ $i+1 }}
                                        </div>
                                    </div> <!-- end box-body-->
                                </a>
                            </div>
                            @endfor
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
    <script src="assets/js/pages/order.js"></script>	
@endpush
