@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="container-full">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="d-flex align-items-center">
               <div class="col-xl-12">
                    @include("components.menus.reports")
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-12">
                    <div class="box">
                        <div class="box-header with-border p-5 text-center">
                            <h4 class="box-title mt-5">Sélectionnez un emplacement</h4>
                            <p class="text-muted">Veuillez choisir un emplacement pour afficher les rapports de ventes.</p>
                        </div>
                        <div class="box-body">
                            <!-- Boutons des emplacements -->
                            <div class="emplacement-buttons-container">
                                <div class="d-flex flex-wrap justify-content-center gap-2" id="emplacementButtons">
                                    @foreach($emplacements as $emplacement)
                                        
                                            <a href="{{ route('reports.service_sales.emplacement', ['emplacement_id' => $emplacement->id]) }}" 
                                                class="btn btn-outline-primary emplacement-btn"
                                                style="min-width: 150px; margin: 5px; ">
                                            {{ $emplacement->libelle }}
                                            <br>
                                            <small class="text-muted">{{ $emplacement->type }}</small>
                                        </a>
                                      
                                    @endforeach
                                </div>
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

@push('styles')
<style>
.emplacement-buttons-container {
    max-width: 100%;
    overflow: hidden;
}

/* Style pour les boutons d'emplacement */
.emplacement-btn {
    transition: all 0.3s ease;
    border: 2px solid #e0e0e0;
    padding: 12px 8px;
    text-align: center;
}

.emplacement-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-color: #007bff;
    color: white !important;
    background-color: #007bff;
}

/* Cibler spécifiquement le texte à l'intérieur du bouton au survol */
.emplacement-btn:hover,
.emplacement-btn:hover small {
    color: white !important;
}

.emplacement-btn.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.emplacement-btn.active small {
    color: rgba(255, 255, 255, 0.8) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .emplacement-btn {
        min-width: 140px !important;
        font-size: 14px;
        padding: 10px 6px;
    }
}

@media (max-width: 576px) {
    .emplacement-buttons-container {
        text-align: center;
    }
    
    .emplacement-btn {
        min-width: 120px !important;
        margin: 3px !important;
    }
}
</style>
@endpush
