<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smart Attend - Gestion des présences</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }
        .welcome-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .welcome-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 2rem 0;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #4f46e5;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background-color: #4f46e5;
            border: none;
            padding: 10px 25px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #4338ca;
        }
        .btn-outline-primary {
            color: #4f46e5;
            border-color: #4f46e5;
            font-weight: 600;
        }
        .btn-outline-primary:hover {
            background-color: #4f46e5;
            color: white;
        }
    </style>
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8">
                    <div class="welcome-card">
                        <div class="welcome-header text-center">
                            <h1 class="display-4 fw-bold mb-3">Bienvenue sur Smart Attend</h1>
                            <p class="lead">Gérez facilement les présences de votre équipe</p>
                        </div>
                        
                        <div class="card-body p-5">
                            <div class="text-center mb-5">
                                <h2 class="mb-4">Commencez dès maintenant</h2>
                                <p class="text-muted mb-4">
                                    Connectez-vous pour accéder à votre tableau de bord et gérer les présences
                                </p>
                                
                                <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                                    @if (Route::has('login'))
                                        @auth
                                            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-4 me-sm-3">
                                                <i class="bi bi-speedometer2 me-2"></i>Tableau de bord
                                            </a>
                                        @else
                                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4 me-sm-3">
                                                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                                            </a>

                                            @if (Route::has('register'))
                                                <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg px-4">
                                                    <i class="bi bi-person-plus me-2"></i>S'inscrire
                                                </a>
                                            @endif
                                        @endauth
                                    @endif
                                </div>
                            </div>

                            <div class="row g-4 mt-4">
                                <div class="col-md-4">
                                    <div class="text-center p-4 h-100">
                                        <div class="feature-icon">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                        <h5>Pointage facile</h5>
                                        <p class="text-muted">Enregistrez vos heures d'arrivée et de départ en un clic</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-4 h-100">
                                        <div class="feature-icon">
                                            <i class="bi bi-graph-up"></i>
                                        </div>
                                        <h5>Suivi en temps réel</h5>
                                        <p class="text-muted">Visualisez les statistiques et l'historique des présences</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-4 h-100">
                                        <div class="feature-icon">
                                            <i class="bi bi-shield-lock"></i>
                                        </div>
                                        <h5>Sécurisé</h5>
                                        <p class="text-muted">Protection avancée de vos données</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-white text-center py-3">
                            <p class="text-muted mb-0">© {{ date('Y') }} Smart Attend. Tous droits réservés.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
