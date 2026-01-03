<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Attend - Gestion des présences</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: #343a40;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin: 5px 0;
            border-radius: 5px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border: none;
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
        }
        .btn-primary {
            background-color: #4e73df;
            border: none;
            padding: 8px 20px;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-present {
            background-color: #d4edda;
            color: #155724;
        }
        .status-late {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-absent {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="text-center my-4">
                    <h4 class="text-white">Smart Attend</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('attendance*') ? 'active' : '' }}" href="{{ url('/attendance') }}">
                            <i class="bi bi-clock-history"></i> Pointage
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('attendance/history*') ? 'active' : '' }}" href="{{ url('/attendance/history') }}">
                            <i class="bi bi-calendar-week"></i> Historique
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('permissions*') ? 'active' : '' }}" href="{{ url('/permissions') }}">
                            <i class="bi bi-envelope"></i> Demandes
                        </a>
                    </li>
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <li class="nav-item mt-3">
                        <span class="nav-link text-muted">ADMINISTRATION</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}" href="{{ url('/admin/dashboard') }}">
                            <i class="bi bi-shield-lock"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ url('/admin/users') }}">
                            <i class="bi bi-people"></i> Utilisateurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}" href="{{ url('/admin/reports') }}">
                            <i class="bi bi-file-earmark-bar-graph"></i> Rapports
                        </a>
                    </li>
                    @endif
                </ul>
            </div>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('title', 'Tableau de bord')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name ?? 'Utilisateur' }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Configuration d'Axios pour inclure le jeton CSRF dans les en-têtes
        document.addEventListener('DOMContentLoaded', function() {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Configuration d'Axios pour inclure le jeton CSRF
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            
            // Configuration pour gérer les erreurs 401 (non autorisé)
            axios.interceptors.response.use(
                response => response,
                error => {
                    if (error.response && error.response.status === 401) {
                        // Rediriger vers la page de connexion si l'utilisateur n'est pas authentifié
                        window.location.href = '/login';
                    }
                    return Promise.reject(error);
                }
            );
        });
    </script>
    @stack('scripts')
</body>
</html>
