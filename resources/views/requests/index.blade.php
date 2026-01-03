@extends('layouts.app')

@section('title', 'Mes demandes')

@push('styles')
<style>
    .request-card {
        border-left: 4px solid;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .request-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .status-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35em 0.65em;
        border-radius: 50rem;
    }
    .request-type {
        font-weight: 600;
        color: #4e73df;
    }
    .request-date {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        background-color: #f8f9fc;
        border-radius: 0.35rem;
    }
    .empty-state i {
        font-size: 3rem;
        color: #dddfeb;
        margin-bottom: 1rem;
    }
    .filter-badge {
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-badge:hover {
        opacity: 0.8;
    }
    .filter-badge.active {
        background-color: #4e73df !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mes demandes</h1>
        <a href="{{ route('requests.create') }}" class="d-none d-sm-inline-block btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nouvelle demande
        </a>
    </div>

    <!-- Filtres -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="me-2">Filtrer :</span>
                <a href="{{ route('requests.index') }}" 
                   class="badge filter-badge {{ !request('status') ? 'bg-primary text-white active' : 'bg-light text-dark' }}">
                    Toutes
                </a>
                @foreach($statuses as $key => $label)
                    <a href="{{ route('requests.index', ['status' => $key]) }}" 
                       class="badge filter-badge {{ request('status') == $key ? 'bg-primary text-white active' : 'bg-light text-dark' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Liste des demandes -->
    @if(count($requests) > 0)
        <div class="row">
            @foreach($requests as $req)
                <div class="col-lg-6 mb-4">
                    <div class="card request-card h-100 border-0 shadow-sm" 
                         style="border-left-color: {{ $req['status'] == 'approved' ? '#1cc88a' : ($req['status'] == 'rejected' ? '#e74a3b' : '#f6c23e') }} !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="request-type mb-1">
                                        {{ $req['type'] }}
                                    </h5>
                                    <div class="request-date mb-2">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        {{ $req['start_date'] }} - {{ $req['end_date'] }}
                                    </div>
                                </div>
                                <span class="status-badge 
                                    {{ $req['status'] == 'approved' ? 'bg-success text-white' : 
                                       ($req['status'] == 'rejected' ? 'bg-danger text-white' : 'bg-warning text-dark') }}">
                                    {{ $statuses[strtolower($req['status'])] ?? $req['status'] }}
                                </span>
                            </div>
                            
                            <p class="card-text text-muted mb-3">
                                {{ Str::limit($req['reason'], 120) }}
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $req['created_at'] }}
                                </small>
                                <div>
                                    @if($req['has_justification'])
                                        <a href="#" class="btn btn-sm btn-outline-primary me-1" 
                                           title="Voir la pièce jointe">
                                            <i class="fas fa-paperclip"></i>
                                        </a>
                                    @endif
                                    @if($req['status'] == 'pending')
                                        <form action="{{ route('requests.cancel', $req['id']) }}" method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    title="Annuler la demande">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('requests.show', $req['id']) }}" 
                                       class="btn btn-sm btn-outline-secondary ms-1" 
                                       title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Pagination">
                <ul class="pagination">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Précédent</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Suivant</a>
                    </li>
                </ul>
            </nav>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="empty-state">
                    <i class="far fa-folder-open"></i>
                    <h5 class="mt-3">Aucune demande trouvée</h5>
                    <p class="text-muted">
                        Vous n'avez pas encore soumis de demande ou aucune demande ne correspond à votre filtre.
                    </p>
                    <a href="{{ route('requests.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i> Créer une demande
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
