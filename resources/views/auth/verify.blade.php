

@extends('layouts.auth')

@section('content')
<div class="col-12">
    <div class="row justify-content-center g-0" id="App">
        <div class="col-lg-4 col-md-5 col-12">
            <div class="bg-white rounded10 shadow-lg loginBox">
                <div class="content-top-agile p-20 pb-0">
                    <h2 class="text-primary">Réinitialisation du mot de passe</h2>
                </div>
                <div class="p-40">
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="fa fa-envelope-o text-primary"></i></span>
                                <input type="email" name="name" class="form-control ps-15 bg-transparent"
                                    placeholder="Email" required>
                            </div>
                        </div>
                        <button type="submit" style="width:100%;" class="waves-effect waves-light btn btn-primary mt-2 text-uppercase">Réinitialiser</button>
                        <a href="{{ url("/login") }}" style="width:100%;" class="waves-effect waves-light btn btn-outline-dark btn-dark mt-2 text-uppercase">Connexion</a>
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

