@extends('layouts.auth')

@section('content')
<div class="col-12">
    <div class="row justify-content-center g-0" id="App">
        <div class="col-lg-4 col-md-5 col-12">
            <div class="bg-white rounded10 shadow-lg loginBox">
                <div class="content-top-agile p-20 pb-0">
                    <h3 class="text-primary">Réinitialiser votre mot de passe</h3>
                </div>
                <div class="p-40">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        {{-- Token caché --}}
                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Affichage des erreurs --}}
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Email --}}
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i class="fa fa-envelope-o text-primary"></i></span>
                                <input type="email" name="email" class="form-control ps-15 bg-transparent"
                                    placeholder="Adresse email" value="{{ old('email', $email) }}" required autofocus>
                            </div>
                        </div>

                        {{-- Nouveau mot de passe --}}
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i class="fa fa-lock text-primary"></i></span>
                                <input type="password" name="password" class="form-control ps-15 bg-transparent"
                                    placeholder="Nouveau mot de passe" required>
                            </div>
                        </div>

                        {{-- Confirmation mot de passe --}}
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i class="fa fa-lock text-primary"></i></span>
                                <input type="password" name="password_confirmation" class="form-control ps-15 bg-transparent"
                                    placeholder="Confirmez le mot de passe" required>
                            </div>
                        </div>

                        <button type="submit" style="width:100%;" class="waves-effect waves-light btn btn-primary mt-2 text-uppercase">Réinitialiser le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

