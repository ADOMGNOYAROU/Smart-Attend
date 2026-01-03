@extends('layouts.app')

@section('title', 'Modifier l\'utilisateur')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        border: 1px solid #d1d3e2;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem + 2px);
    }
    .avatar-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    .work-hours {
        background-color: #f8f9fc;
        border-radius: 0.35rem;
        padding: 1.5rem;
    }
    .time-input {
        max-width: 120px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier l'utilisateur</h1>
        <a href="{{ route('users.show', $user) }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour au profil
        </a>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informations de l'utilisateur</h6>
                    <div>
                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-eye me-1"></i> Voir
                        </a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash me-1"></i> Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data" id="userForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4">
                            <div class="col-md-3 text-center">
                                <div class="mb-3">
                                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('img/default-avatar.png') }}" 
                                         alt="Avatar" 
                                         class="avatar-preview mb-3" 
                                         id="avatarPreview">
                                    <div class="d-grid">
                                        <label class="btn btn-outline-primary btn-sm" for="avatar">
                                            <i class="fas fa-upload me-1"></i> Changer la photo
                                            <input type="file" 
                                                   class="d-none" 
                                                   id="avatar" 
                                                   name="avatar"
                                                   accept="image/*">
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Taille max: 2MB</small>
                                    @error('avatar')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active"
                                           {{ $user->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Compte actif
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name', $user->name) }}" 
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Adresse email <span class="text-danger">*</span></label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', $user->email) }}" 
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="matricule" class="form-label">Matricule <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('matricule') is-invalid @enderror" 
                                               id="matricule" 
                                               name="matricule" 
                                               value="{{ old('matricule', $user->matricule) }}" 
                                               required>
                                        @error('matricule')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <input type="tel" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone', $user->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                                        <select class="form-select @error('role') is-invalid @enderror" 
                                                id="role" 
                                                name="role" 
                                                required>
                                            @foreach($roles as $value => $label)
                                                <option value="{{ $value }}" {{ old('role', $user->role) == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="department" class="form-label">Département <span class="text-danger">*</span></label>
                                        <select class="form-select @error('department') is-invalid @enderror" 
                                                id="department" 
                                                name="department" 
                                                required>
                                            @foreach($departments as $value => $label)
                                                <option value="{{ $value }}" {{ old('department', $user->department) == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="send_notification" 
                                                   name="send_notification">
                                            <label class="form-check-label" for="send_notification">
                                                Envoyer une notification à l'utilisateur concernant cette mise à jour
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3">Horaires de travail</h5>
                                <div class="work-hours">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="work_start_time" class="form-label">Heure de début <span class="text-danger">*</span></label>
                                            <input type="time" 
                                                   class="form-control time-input @error('work_start_time') is-invalid @enderror" 
                                                   id="work_start_time" 
                                                   name="work_start_time" 
                                                   value="{{ old('work_start_time', $user->work_start_time ? $user->work_start_time->format('H:i') : '09:00') }}" 
                                                   required>
                                            @error('work_start_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="work_end_time" class="form-label">Heure de fin <span class="text-danger">*</span></label>
                                            <input type="time" 
                                                   class="form-control time-input @error('work_end_time') is-invalid @enderror" 
                                                   id="work_end_time" 
                                                   name="work_end_time" 
                                                   value="{{ old('work_end_time', $user->work_end_time ? $user->work_end_time->format('H:i') : '17:00') }}" 
                                                   required>
                                            @error('work_end_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Jours de travail <span class="text-danger">*</span></label>
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="select_weekdays">
                                                    Jours de semaine
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="select_weekend">
                                                    Week-end
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="select_all_days">
                                                    Tous les jours
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect_all_days">
                                                    Aucun jour
                                                </button>
                                            </div>
                                            
                                            <div class="row">
                                                @php
                                                    $workDays = old('work_days', $user->work_days ?? [1, 2, 3, 4, 5]);
                                                    $days = [
                                                        1 => 'Lundi',
                                                        2 => 'Mardi',
                                                        3 => 'Mercredi',
                                                        4 => 'Jeudi',
                                                        5 => 'Vendredi',
                                                        6 => 'Samedi',
                                                        0 => 'Dimanche'
                                                    ];
                                                @endphp
                                                
                                                @foreach($days as $key => $day)
                                                    <div class="col-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   name="work_days[]" 
                                                                   value="{{ $key }}" 
                                                                   id="work_day_{{ $key }}"
                                                                   {{ in_array($key, (array)$workDays) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="work_day_{{ $key }}">
                                                                {{ $day }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('work_days')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="mb-3">Réinitialiser le mot de passe</h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Laissez ces champs vides pour conserver le mot de passe actuel.
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Nouveau mot de passe</label>
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Minimum 8 caractères</div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                        <input type="password" 
                                               class="form-control" 
                                               id="password_confirmation" 
                                               name="password_confirmation">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Annuler
                            </a>
                            <div>
                                <button type="reset" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-undo me-1"></i> Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialisation de Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        
        // Prévisualisation de l'avatar
        $('#avatar').change(function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#avatarPreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Gestion des cases à cocher pour les jours de travail
        const weekdays = [1, 2, 3, 4, 5]; // Lundi à Vendredi
        const weekend = [0, 6]; // Dimanche et Samedi
        
        // Sélectionner tous les jours de la semaine
        $('#select_weekdays').on('click', function() {
            weekdays.forEach(day => {
                $(`#work_day_${day}`).prop('checked', true);
            });
            weekend.forEach(day => {
                $(`#work_day_${day}`).prop('checked', false);
            });
        });
        
        // Sélectionner le week-end
        $('#select_weekend').on('click', function() {
            weekdays.forEach(day => {
                $(`#work_day_${day}`).prop('checked', false);
            });
            weekend.forEach(day => {
                $(`#work_day_${day}`).prop('checked', true);
            });
        });
        
        // Tout sélectionner
        $('#select_all_days').on('click', function() {
            $('input[name="work_days[]"]').prop('checked', true);
        });
        
        // Tout désélectionner
        $('#deselect_all_days').on('click', function() {
            $('input[name="work_days[]"]').prop('checked', false);
        });
        
        // Validation du formulaire
        $('#userForm').on('submit', function(e) {
            const password = $('#password').val();
            const confirmPassword = $('#password_confirmation').val();
            
            if (password || confirmPassword) {
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas.');
                    return false;
                }
                
                if (password.length < 8) {
                    e.preventDefault();
                    alert('Le mot de passe doit contenir au moins 8 caractères.');
                    return false;
                }
            }
            
            const startTime = $('#work_start_time').val();
            const endTime = $('#work_end_time').val();
            
            if (startTime >= endTime) {
                e.preventDefault();
                alert('L\'heure de fin doit être postérieure à l\'heure de début.');
                return false;
            }
            
            const checkedDays = $('input[name="work_days[]"]:checked').length;
            if (checkedDays === 0) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins un jour de travail.');
                return false;
            }
            
            return true;
        });
    });
</script>
@endpush
