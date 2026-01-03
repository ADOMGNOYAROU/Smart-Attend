<!-- resources/views/auth/login.blade.php -->
@extends('layouts.auth')

@section('content')
<div class="container-fluid">
    <div class="row min-vh-100">
        <!-- Colonne de gauche avec l'image de fond -->
        <div class="col-lg-8 d-none d-lg-block">
            <div class="auth-bg">
                <div class="auth-overlay"></div>
                <div class="auth-content">
                    <h1 class="text-white">Bienvenue sur Smart Attend</h1>
                    <p class="text-white-50">Gérez facilement vos présences et vos congés</p>
                </div>
            </div>
        </div>

        <!-- Colonne de droite avec le formulaire -->
        <div class="col-lg-4 d-flex align-items-center justify-content-center p-5">
            <div class="w-100">
                <div class="text-center mb-5">
                    <h2>Connexion</h2>
                    <p class="text-muted">Entrez vos identifiants pour accéder à votre espace</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label">Adresse email</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="Entrez votre email" required autocomplete="email" autofocus>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label">Mot de passe</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                    Mot de passe oublié ?
                                </a>
                            @endif
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="••••••••" 
                                   required autocomplete="current-password">
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" 
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                        Se connecter
                    </button>

                    <div class="text-center mt-4">
                        <p class="mb-0">Vous n'avez pas de compte ? 
                            <a href="#" class="text-decoration-none">Contactez l'administrateur</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .auth-bg {
        background: url('/images/auth-bg.jpg') no-repeat center center;
        background-size: cover;
        height: 100%;
        position: relative;
    }
    
    .auth-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
    }
    
    .auth-content {
        position: relative;
        z-index: 1;
        color: white;
        padding: 2rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .input-group-text {
        background-color: #f8f9fa;
    }
</style>
@endsection