@extends('layouts.auth')

@section('content')
<div class="col-12">
    <div class="row justify-content-center g-0" id="App">
        <div class="col-lg-4 col-md-5 col-12">
            <div class="bg-white rounded10 shadow-lg loginBox">
                <div class="content-top-agile p-20 pb-0">
                    <h2 class="text-primary">Loyambo Resto & Lounge</h2>
                    <p class="mb-0">Identifiez-vous pour continuer</p>
                </div>
                <div class="p-40">
                    <form method="POST"  @submit.prevent="login" action="{{ route('login') }}">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-email text-primary"></i></span>
                                <input type="email" name="email" class="form-control ps-15 bg-transparent"
                                    placeholder="Email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-lock text-primary"></i></span>
                                <input type="password" class="form-control ps-15 bg-transparent"
                                    placeholder="Password" name="password" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="checkbox">
                                    <input type="checkbox" name="remember" id="basic_checkbox_1">
                                    <label for="basic_checkbox_1">Garder ma session</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="fog-pwd text-end">
                                    <a href="{{ route("password.request") }}" class="hover-warning"><i class="ion ion-locked me-1"></i>Mot de passe oublié ?</a><br>
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" :disabled="isLoading" style="width:100%;" class="waves-effect waves-light btn mb-5 btn-warning mt-10"> <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span> CONNECTER</button>
                                <a href="{{ route("register") }}" style="width:100%;" class="waves-effect waves-light btn btn-outline btn-primary mt-2 text-uppercase">Créer établissement</a>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push("scripts")
    <script type="module" src="{{ asset("assets/js/scripts/auth.js") }}"></script>
@endpush

