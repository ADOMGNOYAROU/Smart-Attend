@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')

@push('styles')
<style>
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }
    .role-badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 0.35em 0.65em;
        border-radius: 50rem;
    }
    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }
    .user-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des utilisateurs</h1>
        <a href="{{ route('users.create') }}" class="d-none d-sm-inline-block btn btn-primary shadow-sm">
            <i class="fas fa-user-plus fa-sm text-white-50"></i> Nouvel utilisateur
        </a>
    </div>

    <!-- Filtres et recherche -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('users.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Nom, email, matricule...">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="role" class="form-label">Rôle</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">Tous les rôles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                        <option value="manager" {{ request('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="employee" {{ request('role') == 'employee' ? 'selected' : '' }}>Employé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="department" class="form-label">Département</label>
                    <select class="form-select" id="department" name="department">
                        <option value="">Tous les départements</option>
                        @foreach(['IT', 'RH', 'Comptabilité', 'Commercial', 'Direction', 'Autre'] as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                {{ $dept }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter me-1"></i> Filtrer
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Département</th>
                            <th>Matricule</th>
                            <th>Dernière connexion</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('img/default-avatar.png') }}" 
                                             alt="{{ $user->name }}" class="user-avatar me-2">
                                        <div>
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $roleClasses = [
                                            'admin' => 'bg-danger',
                                            'manager' => 'bg-primary',
                                            'employee' => 'bg-secondary'
                                        ][$user->role] ?? 'bg-secondary';
                                        
                                        $roleLabels = [
                                            'admin' => 'Admin',
                                            'manager' => 'Manager',
                                            'employee' => 'Employé'
                                        ];
                                    @endphp
                                    <span class="badge {{ $roleClasses }}">
                                        {{ $roleLabels[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td>{{ $user->department ?? 'Non défini' }}</td>
                                <td>{{ $user->matricule ?? 'N/A' }}</td>
                                <td>
                                    @if($user->last_login_at)
                                        {{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}
                                        @if($user->last_login_ip)
                                        <div class="text-muted small">
                                            {{ $user->last_login_ip }}
                                        </div>
                                        @endif
                                    @else
                                        <span class="text-muted">Jamais connecté</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = $user->is_active ? 'bg-success' : 'bg-secondary';
                                        $statusText = $user->is_active ? 'Actif' : 'Inactif';
                                    @endphp
                                    <span class="status-dot {{ $statusClass }}"></span>
                                    {{ $statusText }}
                                </td>
                                <td class="text-end user-actions">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('users.show', $user) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user) }}" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $user) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users-slash fa-2x mb-2"></i>
                                        <p class="mb-0">Aucun utilisateur trouvé</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($users->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }} utilisateurs
                </div>
                <div>
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Activer les tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
