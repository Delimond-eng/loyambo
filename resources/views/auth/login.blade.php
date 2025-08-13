@extends('layouts.auth')

@section('content')
<div class="col-12">
    <div class="row justify-content-center g-0">
        <div class="col-lg-4 col-md-5 col-12">
            <div class="bg-white rounded10 shadow-lg">
                <div class="content-top-agile p-20 pb-0">
                    <h2 class="text-primary">Loyambo Resto & Lounge</h2>
                    <p class="mb-0">Identifiez-vous pour continuer</p>
                </div>
                <div class="p-40">
                    <form action="/" method="get">
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="ti-user text-primary"></i></span>
                                <input type="text" class="form-control ps-15 bg-transparent"
                                    placeholder="Username">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text  bg-transparent"><i
                                        class="ti-lock text-primary"></i></span>
                                <input type="password" class="form-control ps-15 bg-transparent"
                                    placeholder="Password">
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
                                <button type="submit" style="width:100%;" class="waves-effect waves-light btn mb-5 btn-warning mt-10">CONNECTER</button>
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

