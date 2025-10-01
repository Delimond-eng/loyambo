@extends('layouts.auth')

@section('content')
<div class="col-12">
    <div class="row justify-content-center g-0" id="App">
        <div class="col-lg-4 col-md-5 col-12">
            <div class="bg-white rounded10 shadow-lg loginBox">
                <div class="content-top-agile p-20 pb-0">
                    <h3 class="text-primary">Réinitialisation mot de passe</h3>
                </div>
                <div class="p-40">
                    <form method="POST" action="{{ route('password.email') }}">
                        {{-- Affichage des erreurs --}}
                        @if($errors->has('email'))
                            <div class="alert alert-danger">{{ $errors->first('email') }}</div>
                        @endif

                        {{-- Affichage du message de succès --}}
                        @if(session('status'))
                            <div class="alert alert-success">Nous avons envoyé votre lien de réinitialisation de mot de passe par e-mail !</div>
                        @endif

                        @csrf
                        <div class="form-group">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-transparent"><i
                                        class="fa fa-envelope-o text-primary"></i></span>
                                <input type="email" name="email" class="form-control ps-15 bg-transparent"
                                    placeholder="Entrer votre adresse email" required>
                            </div>
                        </div>

                        
                        <button type="submit" style="width:100%;" class="waves-effect waves-light btn btn-primary mt-2 text-uppercase">Réinitialiser</button>
                        <a href="{{ url("/login") }}" style="width:100%;" class="waves-effect waves-light btn btn-outline btn-dark mt-2 text-uppercase">Connexion</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

