@extends('layouts.auth')

@section('content')
<div class="col-12">
    <div class="row justify-content-center g-0">
        <div class="col-lg-4 col-md-5 col-12">
            <div class="bg-white rounded10 shadow-lg">
                <div class="content-top-agile p-20 pb-0">
                    <h2 class="text-primary">Loyambo Resto & Lounge</h2>
                    <p class="mb-0">Créer un nouveau établissement !</p>
                </div>
                <div class="p-40">
                    <form action="/" method="get">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-flag text-primary"></i></span>
                                <input type="text" class="form-control ps-15 bg-transparent"
                                    placeholder="Nom de l'Ets...">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-flag-alt text-primary"></i></span>
                                <select class="form-control ps-15 bg-transparent">
                                    <option value="" selected hidden label="Type"></option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="lounge">Lounge</option>
                                    <option value="lounge">Hôtel</option>
                                    <option value="complexe">Complexe</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-location-pin text-primary"></i></span>
                                <input type="text" class="form-control ps-15 bg-transparent"
                                    placeholder="Adresse">
                            </div>
                        </div>

                        
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="fa fa-phone text-primary me-1"></i></span>
                                <input type="tel" class="form-control ps-15 bg-transparent"
                                    placeholder="Téléphone">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-email text-primary"></i></span>
                                <input type="email" class="form-control ps-15 bg-transparent"
                                    placeholder="Email">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-user text-primary"></i></span>
                                <input type="text" class="form-control ps-15 bg-transparent"
                                    placeholder="Administrateur">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text  bg-transparent"><i
                                        class="ti-lock text-primary"></i></span>
                                <input type="password" class="form-control ps-15 bg-transparent"
                                    placeholder="Mot de passe">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="checkbox">
                                    <input type="checkbox" id="basic_checkbox_1">
                                    <label for="basic_checkbox_1">Garder ma session</label>
                                </div>
                            </div>

                            <div class="col-12 text-center">
                                <button type="submit" style="width:100%;" class="waves-effect waves-light btn mb-5 btn-warning mt-10 text-uppercase">Créer</button>
                                <a href="{{ route("login") }}" style="width:100%;" class="waves-effect waves-light btn btn-outline btn-primary mt-2 text-uppercase">Connexion</a>
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
