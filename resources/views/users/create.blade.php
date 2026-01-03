@extends('layouts.app')

@section('title', 'Créer un nouvel utilisateur')

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
        <h1 class="h3 mb-0 text-gray-800">Créer un nouvel utilisateur</h1>
        <a href="{{ route('users.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations de l'utilisateur</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" id="userForm">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-3 text-center">
                                <div class="mb-3">
                                    <img src="{{ asset('img/default-avatar.png') }}" 
                                         alt="Avatar" 
                                         class="avatar-preview mb-3" 
                                         id="avatarPreview">
                                    <div class="d-grid">
                                        <label class="btn btn-outline-primary btn-sm" for="avatar">
                                            <i class="fas fa-upload me-1"></i> Choisir une photo
                                            <input type="file" 
                                                   class="d-none" 
                                                   id="avatar" 
                                                   name="avatar"
                                                   accept="image/*">
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Taille max: 2MB</small>
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
                                               value="{{ old('name') }}" 
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
                                               value="{{ old('email') }}" 
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
                                               value="{{ old('matricule') }}" 
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
                                               value="{{ old('phone') }}">
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
                                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Sélectionner un rôle</option>
                                            @foreach($roles as $value => $label)
                                                <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
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
                                            <option value="" disabled {{ old('department') ? '' : 'selected' }}>Sélectionner un département</option>
                                            @foreach($departments as $value => $label)
                                                <option value="{{ $value }}" {{ old('department') == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('department')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                                                   value="{{ old('work_start_time', '09:00') }}" 
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
                                                   value="{{ old('work_end_time', '17:00') }}" 
                                                   required>
                                            @error('work_end_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum 8 caractères</div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-undo me-1"></i> Réinitialiser
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
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
        
        // Validation du formulaire
        $('#userForm').on('submit', function(e) {
            const password = $('#password').val();
            const confirmPassword = $('#password_confirmation').val();
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }
            
            const startTime = $('#work_start_time').val();
            const endTime = $('#work_end_time').val();
            
            if (startTime >= endTime) {
                e.preventDefault();
                alert('L\'heure de fin doit être postérieure à l\'heure de début.');
                return false;
            }
            
            return true;
        });
    });
</script>
@endpush
