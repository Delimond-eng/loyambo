

@extends("layouts.admin")

@section("content")
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Content Header (Page header) -->
		<div class="content-header">
			<div class="d-flex align-items-center">
				<div class="me-auto">
					<h3 class="page-title">Liste des utilisateurs</h3>
					<!-- <div class="d-inline-block align-items-center">
						<nav>
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route("home") }}"><i class="mdi mdi-home-outline"></i></a></li>
								<li class="breadcrumb-item" aria-current="page">utilisateurs</li>
								<li class="breadcrumb-item active" aria-current="page">Liste</li>
							</ol>
						</nav>
					</div> -->
				</div>

			</div>
		</div>

		<!-- Main content -->
		<section class="content" id="AppAdmin" v-cloak>
			<div class="row">
				<div class="col-lg-9 col-md-8">
					<div class="box">
                        <div class="box-header d-flex justify-content-between align-items-center" style="padding: 1.5rem;">
                            <h4 class="box-title align-items-start flex-column">
                                Les utilisateurs
                                <small class="subtitle">Actifs et non actifs</small>
                            </h4>
                            @can("creer-utilisateurs")
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#myModal" class="btn btn-primary text-center btn-rounded">+ Créer nouveau utilisateur</a>
                            @endcan
                        </div>
                        <div class="box-body py-0">
                            <div class="table-responsive">
                                <table class="table no-border">
                                    <thead>
                                        <tr class="text-start">
                                            <th style="width: 50px">Utilisateur</th>
                                            <th style="min-width: 200px"></th>
                                            <th style="min-width: 150px">Emplacement</th>
                                            <th style="min-width: 150px">Status</th>
                                            <th style="min-width: 150px">Dernière activité</th>
                                            <th class="text-end" style="min-width: 150px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr v-for="(data, index) in allUsers" :key="index">
                                            <td>
                                                <div class="bg-lightest h-50 w-50 l-h-50 rounded text-center overflow-hidden">
                                                    <div class="avatar avatar-lg status-warning">
                                                        <img src="assets/images/avatar/avatar-1.png" class="h-50 align-self-end " alt="">
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="#" class="text-dark fw-600 hover-primary fs-16">@{{ data.name }}</a>
                                                <span class="text-fade d-block">@{{ data.role }}</span>
                                            </td>
                                            <td>
                                                <span v-if="data.emplacement" class="text-dark fw-600 d-block fs-16">
                                                    @{{ data.emplacement.libelle }}
                                                </span>
                                            </td>
                                            <td>
                                                <span v-if="data.last_log" class="badge badge-pill" :class="data.last_log.status === 'online' ? 'badge-success-light' : 'badge-danger-light'">@{{ data.last_log.status }}</span>
                                                <span v-else class="badge badge-pill badge-danger-light">offline</span>
                                            </td>
                                            <td>
                                                <div v-if="data.last_log">
                                                    <span v-if="data.last_log.status==='online'">
                                                        @{{ formateDate(data.last_log.logged_in_at) }},<span class="fs-12"> @{{ formateTime(data.last_log.logged_in_at) }}</span>
                                                    </span>
                                                    <span v-else>
                                                        @{{ formateDate(data.last_log.logged_out_at) }},<span class="fs-12"> @{{ formateTime(data.last_log.logged_out_at) }}</span>
                                                    </span>
                                                </div>
                                                <div v-else>
                                                    Aucune activité
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <a href="#" v-if="data.role !=='admin'" @click="openPermissionsModal(data)" data-bs-toggle="modal" data-bs-target="#modalPermissions" class="waves-effect waves-light btn btn-info-light btn-circle"><span class="icon-Plus fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-primary-light btn-circle mx-5" @click="editUser(data)"  data-bs-toggle="modal" data-bs-target="#myModal"><span class="icon-Write"><span class="path1"></span><span class="path2"></span></span></a>
                                                <a href="#" class="waves-effect waves-light btn btn-danger-light btn-circle"><span class="icon-Trash1 fs-18"><span class="path1"></span><span class="path2"></span></span></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
				</div>
			</div>
            <!-- Popup Model Plase Here -->
            <div id="myModal" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content"  @submit.prevent="createOrUpdateUser">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Création compte utilisateur</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-horizontal">
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-user text-primary"></i></span>
                                        <input v-model="form.name" type="text" class="form-control ps-15 bg-transparent"
                                            placeholder="Nom d'utilisateur" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-lock text-primary"></i></span>
                                        <input v-model="form.password" type="password" class="form-control ps-15 bg-transparent"
                                            placeholder="Mot de passe" required>
                                    </div>
                                </div>
            
                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-money text-primary"></i></span>
                                        <input v-model="form.salaire" type="text" class="form-control ps-15 bg-transparent"
                                            placeholder="Salaire en CDF/Heure">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-user text-primary"></i></span>
                                        <select v-model="form.role" class="form-control ps-15 bg-transparent" required>
                                            <option value="" selected hidden label="Sélectionnez rôle"></option>
                                            <option value="admin">Administateur</option>
                                            <option value="caissier">Caissier</option>
                                            <option value="serveur">Serveur</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i
                                                class="ti-location-pin text-primary"></i></span>
                                        <select v-model="form.emplacement_id" class="form-control ps-15 bg-transparent">
                                            <option value="" selected hidden label="Sélectionnez un emplacement."></option>
                                            @foreach ($emplacements as $place)
                                            <option value="{{ $place->id }}">{{ $place->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex g-2">
                            <button type="submit" class="btn btn-success" :disabled="isLoading"><span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>Enregistrer</button>
                            <button type="button" class="btn btn-danger float-end" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </form>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /Popup Model Plase Here -->

            <!-- Popup Model Plase Here -->
            <div id="modalPermissions" class="modal fade in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Attribution accès</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" v-if="selectedUserPermissions">
                            <div class="box-body">
                                <div class="demo-checkbox">		
                                    <template v-for="perm in permissions">
                                        <input
                                            type="checkbox"
                                            class="filled-in chk-col-success"
                                            :id="'md_checkbox_' + perm.id"
                                            :value="perm.id"
                                            v-model="selectedPermissionIds"
                                            >
                                        <label :for="'md_checkbox_' + perm.id">@{{ perm.name }}</label>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex g-2">
                            <button type="button" @click="submitPermissions" class="btn btn-success" :disabled="isLoading"><span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span> Valider les modifications</button>
                            <button type="button" class="btn btn-danger float-end" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /Popup Model Plase Here -->
		</section>
		<!-- /.content -->
	  </div>
    </div>
  <!-- /.content-wrapper -->
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/admin.js") }}"></script>
@endpush
