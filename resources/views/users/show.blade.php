@extends('layouts.app')

@section('title', 'Détails de l\'utilisateur')

@push('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        padding: 2rem 0;
        margin-bottom: 2rem;
        border-radius: 0.35rem;
    }
    .profile-avatar {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 5px solid rgba(255, 255, 255, 0.2);
        object-fit: cover;
        margin-bottom: 1rem;
    }
    .stat-card {
        border-left: 4px solid #4e73df;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .nav-tabs .nav-link {
        color: #6e707e;
        font-weight: 600;
    }
    .nav-tabs .nav-link.active {
        color: #4e73df;
        border-bottom: 3px solid #4e73df;
    }
    .activity-item {
        position: relative;
        padding-left: 2rem;
        padding-bottom: 1.5rem;
        border-left: 2px solid #eaecf4;
    }
    .activity-item:last-child {
        padding-bottom: 0;
        border-left-color: transparent;
    }
    .activity-item::before {
        content: '';
        position: absolute;
        left: -7px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #4e73df;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- En-tête du profil -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('img/default-avatar.png') }}" 
                         alt="{{ $user->name }}" 
                         class="profile-avatar">
                </div>
                <div class="col-md-6">
                    <h2 class="mb-1">{{ $user->name }}</h2>
                    <p class="mb-2">
                        <i class="fas fa-envelope me-2"></i> {{ $user->email }}
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @php
                            $roleClasses = [
                                'admin' => 'bg-danger',
                                'manager' => 'bg-primary',
                                'employee' => 'bg-secondary'
                            ][$user->role] ?? 'bg-secondary';
                            
                            $roleLabels = [
                                'admin' => 'Administrateur',
                                'manager' => 'Manager',
                                'employee' => 'Employé'
                            ];
                        @endphp
                        <span class="badge {{ $roleClasses }} me-2">
                            {{ $roleLabels[$user->role] ?? $user->role }}
                        </span>
                        
                        @if($user->is_active)
                            <span class="badge bg-success">
                                <i class="fas fa-circle-check me-1"></i> Actif
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                <i class="fas fa-circle-pause me-1"></i> Inactif
                            </span>
                        @endif
                        
                        @if($user->email_verified_at)
                            <span class="badge bg-info">
                                <i class="fas fa-check-circle me-1"></i> Email vérifié
                            </span>
                        @endif
                    </div>
                    
                    <div class="d-flex flex-wrap gap-3 text-white-50 small">
                        @if($user->department)
                            <div>
                                <i class="fas fa-building me-1"></i> {{ $user->department }}
                            </div>
                        @endif
                        
                        @if($user->matricule)
                            <div>
                                <i class="fas fa-id-card me-1"></i> {{ $user->matricule }}
                            </div>
                        @endif
                        
                        @if($user->phone)
                            <div>
                                <i class="fas fa-phone me-1"></i> {{ $user->phone }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group">
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-light">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-outline-light ms-2"
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                    <i class="fas fa-trash me-1"></i> Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Colonne de gauche - Statistiques -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Statistiques</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Membre depuis</div>
                            <div class="fw-bold">
                                {{ $user->created_at->format('d/m/Y') }}
                                <small class="text-muted">({{ $user->created_at->diffForHumans() }})</small>
                            </div>
                        </div>
                        
                        @if($user->last_login_at)
                            <div class="mb-3">
                                <div class="text-muted small mb-1">Dernière connexion</div>
                                <div class="fw-bold">
                                    {{ \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') }}
                                    <small class="text-muted">({{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }})</small>
                                </div>
                                @if($user->last_login_ip)
                                <div class="small text-muted">
                                    <i class="fas fa-globe me-1"></i> {{ $user->last_login_ip }}
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning small mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Cet utilisateur ne s'est jamais connecté
                            </div>
                        @endif
                        
                        <hr>
                        
                        <div class="mb-3">
                            <div class="text-muted small mb-2">Horaires de travail</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="far fa-clock me-1 text-primary"></i>
                                    {{ $user->work_start_time ? $user->work_start_time->format('H:i') : 'Non défini' }}
                                    - 
                                    {{ $user->work_end_time ? $user->work_end_time->format('H:i') : 'Non défini' }}
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editScheduleModal">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="text-muted small mb-2">Statut du compte</div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="accountStatus" 
                                       {{ $user->is_active ? 'checked' : '' }} 
                                       onchange="updateAccountStatus({{ $user->id }}, this.checked)">
                                <label class="form-check-label" for="accountStatus">
                                    {{ $user->is_active ? 'Compte actif' : 'Compte désactivé' }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Activité récente</h6>
                        <a href="#" class="btn btn-sm btn-outline-primary">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <div class="activity-item">
                            <div class="small text-muted">Aujourd'hui, 14:32</div>
                            <p class="mb-0">A effectué un pointage de sortie</p>
                        </div>
                        <div class="activity-item">
                            <div class="small text-muted">Aujourd'hui, 08:15</div>
                            <p class="mb-0">A effectué un pointage d'entrée</p>
                        </div>
                        <div class="activity-item">
                            <div class="small text-muted">Hier, 17:45</div>
                            <p class="mb-0">A effectué un pointage de sortie</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Colonne de droite - Détails -->
            <div class="col-lg-8">
                <!-- Navigation par onglets -->
                <ul class="nav nav-tabs mb-4" id="userTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" 
                                data-bs-target="#profile" type="button" role="tab" 
                                aria-controls="profile" aria-selected="true">
                            <i class="fas fa-user-circle me-1"></i> Profil
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" 
                                data-bs-target="#attendance" type="button" role="tab" 
                                aria-controls="attendance" aria-selected="false">
                            <i class="fas fa-calendar-check me-1"></i> Pointages
                            <span class="badge bg-primary rounded-pill ms-1">{{ $user->attendances_count ?? 0 }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="permissions-tab" data-bs-toggle="tab" 
                                data-bs-target="#permissions" type="button" role="tab" 
                                aria-controls="permissions" aria-selected="false">
                            <i class="fas fa-clipboard-list me-1"></i> Demandes
                            <span class="badge bg-primary rounded-pill ms-1">{{ $user->permissions_count ?? 0 }}</span>
                        </button>
                    </li>
                </ul>
                
                <!-- Contenu des onglets -->
                <div class="tab-content" id="userTabsContent">
                    <!-- Onglet Profil -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Informations personnelles</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small">Nom complet</div>
                                        <div class="mb-3">{{ $user->name }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">Adresse email</div>
                                        <div class="mb-3">
                                            {{ $user->email }}
                                            @if($user->email_verified_at)
                                                <span class="badge bg-success ms-2">
                                                    <i class="fas fa-check-circle me-1"></i> Vérifié
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark ms-2">
                                                    <i class="fas fa-exclamation-circle me-1"></i> Non vérifié
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small">Téléphone</div>
                                        <div class="mb-3">
                                            {{ $user->phone ?? 'Non renseigné' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">Matricule</div>
                                        <div class="mb-3">
                                            {{ $user->matricule ?? 'Non défini' }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="text-muted small">Département</div>
                                        <div class="mb-3">
                                            {{ $user->department ?? 'Non défini' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">Rôle</div>
                                        <div class="mb-3">
                                            @php
                                                $roleClasses = [
                                                    'admin' => 'danger',
                                                    'manager' => 'primary',
                                                    'employee' => 'secondary'
                                                ][$user->role] ?? 'secondary';
                                                
                                                $roleLabels = [
                                                    'admin' => 'Administrateur',
                                                    'manager' => 'Manager',
                                                    'employee' => 'Employé'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $roleClasses }}">
                                                {{ $roleLabels[$user->role] ?? $user->role }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h6 class="mb-3">Sécurité</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#changePasswordModal">
                                            <i class="fas fa-key me-1"></i> Changer le mot de passe
                                        </button>
                                        
                                        @if(!$user->email_verified_at)
                                            <button class="btn btn-outline-secondary btn-sm"
                                                    onclick="resendVerificationEmail({{ $user->id }})">
                                                <i class="fas fa-envelope me-1"></i> Renvoyer l'email de vérification
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Pointages -->
                    <div class="tab-pane fade" id="attendance" role="tabpanel" aria-labelledby="attendance-tab">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0">Historique des pointages</h5>
                                    <div>
                                        <button class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i> Exporter
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Pointage entrée</th>
                                                <th>Pointage sortie</th>
                                                <th>Durée</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($user->attendances()->latest('date')->take(5)->get() as $attendance)
                                                <tr>
                                                    <td>{{ $attendance->date->format('d/m/Y') }}</td>
                                                    <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}</td>
                                                    <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}</td>
                                                    <td>
                                                        @if($attendance->check_in && $attendance->check_out)
                                                            {{ $attendance->check_out->diffInHours($attendance->check_in) }}h
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusClasses = [
                                                                'present' => 'success',
                                                                'late' => 'warning',
                                                                'absent' => 'danger',
                                                                'holiday' => 'info',
                                                                'sick_leave' => 'primary',
                                                            ][$attendance->status] ?? 'secondary';
                                                            
                                                            $statusLabels = [
                                                                'present' => 'Présent',
                                                                'late' => 'En retard',
                                                                'absent' => 'Absent',
                                                                'holiday' => 'Congé',
                                                                'sick_leave' => 'Maladie',
                                                            ];
                                                        @endphp
                                                        <span class="badge bg-{{ $statusClasses }}">
                                                            {{ $statusLabels[$attendance->status] ?? $attendance->status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-secondary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#attendanceDetailsModal"
                                                                data-id="{{ $attendance->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                                            <p class="mb-0">Aucun pointage enregistré</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($user->attendances_count > 5)
                                    <div class="text-center mt-3">
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            Voir tout l'historique
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Demandes -->
                    <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0">Demandes de permission</h5>
                                    <div>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-plus me-1"></i> Nouvelle demande
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Période</th>
                                                <th>Durée</th>
                                                <th>Statut</th>
                                                <th>Date de demande</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($user->permissions()->latest()->take(5)->get() as $permission)
                                                <tr>
                                                    <td>
                                                        @php
                                                            $typeIcons = [
                                                                'vacation' => 'umbrella-beach',
                                                                'sick_leave' => 'procedures',
                                                                'personal' => 'user-clock',
                                                                'other' => 'ellipsis-h',
                                                            ];
                                                        @endphp
                                                        <i class="fas fa-{{ $typeIcons[$permission->type] ?? 'question-circle' }} me-1"></i>
                                                        {{ ucfirst($permission->type) }}
                                                    </td>
                                                    <td>
                                                        {{ $permission->start_date->format('d/m/Y') }}
                                                        @if($permission->end_date && !$permission->start_date->isSameDay($permission->end_date))
                                                            <br>
                                                            au {{ $permission->end_date->format('d/m/Y') }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($permission->start_date->isSameDay($permission->end_date))
                                                            1 jour
                                                        @else
                                                            {{ $permission->start_date->diffInDays($permission->end_date) + 1 }} jours
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusClasses = [
                                                                'pending' => 'warning',
                                                                'approved' => 'success',
                                                                'rejected' => 'danger',
                                                            ][$permission->status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $statusClasses }}">
                                                            {{ ucfirst($permission->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-secondary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#permissionDetailsModal"
                                                                data-id="{{ $permission->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                                            <p class="mb-0">Aucune demande trouvée</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                @if($user->permissions_count > 5)
                                    <div class="text-center mt-3">
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            Voir toutes les demandes
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('users.modals.change-password')
@include('users.modals.edit-schedule')

@endsection

@push('scripts')
<script>
    // Mettre à jour le statut du compte
    function updateAccountStatus(userId, isActive) {
        fetch(`/admin/users/${userId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                is_active: isActive
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Statut du compte mis à jour avec succès');
            } else {
                toastr.error('Une erreur est survenue lors de la mise à jour du statut');
                // Revert the switch
                document.getElementById('accountStatus').checked = !isActive;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Une erreur est survenue');
            // Revert the switch
            document.getElementById('accountStatus').checked = !isActive;
        });
    }
    
    // Renvoyer l'email de vérification
    function resendVerificationEmail(userId) {
        fetch(`/admin/users/${userId}/resend-verification`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Email de vérification envoyé avec succès');
            } else {
                toastr.error('Une erreur est survenue lors de l\'envoi de l\'email');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Une erreur est survenue');
        });
    }
    
    // Initialisation des tooltips
    document.addEventListener('DOMContentLoaded', function() {
        // Activer les tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Gestion des onglets avec persistance dans l'URL
        if (location.hash) {
            const triggerEl = document.querySelector(`[href="${location.hash}"]`);
            if (triggerEl) {
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }
        
        // Mettre à jour l'URL lors du changement d'onglet
        const tabLinks = document.querySelectorAll('#userTabs .nav-link');
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const target = this.getAttribute('data-bs-target');
                history.pushState(null, '', `${window.location.pathname}${target}`);
            });
        });
    });
</script>
@endpush
