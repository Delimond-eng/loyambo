@extends("layouts.admin")

@section("content")
<div class="content-wrapper">
	<div class="container-full">

		<section class="content">
			<div class="row d-flex justify-content-center g-4">

				<div class="col-md-12">
					<div class="box shadow-none rounded-0">

						<div class="box-header d-flex justify-content-between align-items-center py-3 px-10 bg-primary text-white rounded-top">
							<h4 class="fw-bold text-uppercase">FICHE DE STOCK</h4>

							<div>
								<a href="{{ route('fiche_stock.pdf') }}" class="btn btn-light btn-xs me-2">
									<i class="fa fa-file-pdf-o me-1 text-danger fw-bold"></i> Exporter PDF
								</a>
								<a href="{{ route('fiche_stock.excel') }}" class="btn btn-light btn-xs">
									<i class="fa fa-file-excel-o me-1 text-success fw-bold"></i>Exporter Excel
								</a>
							</div>
						</div>

						<div class="box-body p-0">
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped align-middle text-center table-stock">
									<thead>
									<tr>
										<th>Produit</th>
										<th>Emplacement</th>
										<th class="bg-dark text-white">Stock Init.</th>
										<th>Entrée</th>
										<th>Sortie</th>
										<th>Transf. +</th>
										<th>Transf. -</th>
										<th>Vente</th>
										<th>Ajust. +</th>
										<th>Ajust. -</th>
										<th class="bg-success text-white">Stock Final</th>
									</tr>
									</thead>

									<tbody>
									@foreach($stocks as $s)
									<tr>
										<td>{{ $s->produit->libelle }}</td>
										<td>{{ $s->emplacement->libelle ?? '-' }}</td>
										<td class="bg-dark text-white fw-bold">{{ $s->stock_initial }}</td>
										<td class="bg-info-subtle">{{ $s->total_entree }}</td>
										<td class="bg-danger-subtle">{{ $s->total_sortie }}</td>
										<td class="bg-warning-subtle">{{ $s->total_transfert_entree }}</td>
										<td class="bg-secondary-subtle">{{ $s->total_transfert_sortie }}</td>
										<td class="bg-danger text-white fw-bold">{{ $s->total_vente }}</td>
										<td class="bg-primary-subtle">{{ $s->ajustement_plus }}</td>
										<td class="bg-dark text-white">{{ $s->ajustement_moins }}</td>
										<td class="fw-bold bg-success text-white">{{ $s->solde }}</td>
									</tr>
									@endforeach
									</tbody>
								</table>
							</div>
						</div>

					</div>
				</div>

			</div>
		</section>

	</div>
</div>
@endsection


@push("styles")
<style>
.table-stock thead th {
	position: sticky;
	top: 0;
	z-index: 2;
	font-size: 13px;
}

.table-stock td, .table-stock th {
	padding: 7px !important;
	font-size: 14px;
	vertical-align: middle !important;
	white-space: nowrap;
}

.bg-info-subtle { background: #d0ecff !important; }
.bg-danger-subtle { background: #ffd0d0 !important; }
.bg-warning-subtle { background: #ffe8b3 !important; }
.bg-secondary-subtle { background: #e2e2e2 !important; }
.bg-primary-subtle { background: #dbe4ff !important; }

.box {
	border-radius: 10px;
	overflow: hidden;
}
</style>
@endpush

