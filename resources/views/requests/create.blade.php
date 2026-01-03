@extends('layouts.app')

@section('title', 'Nouvelle demande')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .form-section {
        background: #fff;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    .form-label {
        font-weight: 600;
        color: #4e73df;
    }
    .btn-submit {
        padding: 0.75rem 2rem;
        font-weight: 600;
    }
    .file-upload {
        border: 2px dashed #d1d3e2;
        border-radius: 0.35rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    .file-upload:hover {
        border-color: #b7b9cc;
        background-color: #f8f9fc;
    }
    .file-upload i {
        font-size: 2.5rem;
        color: #dddfeb;
        margin-bottom: 1rem;
    }
    .file-name {
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #6e707e;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Nouvelle demande</h1>
        <a href="{{ route('requests.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="form-section">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data" id="requestForm">
                    @csrf
                    
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">Type de demande</h5>
                        <div class="row g-3">
                            @foreach($types as $value => $label)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type" id="type{{ $value }}" 
                                               value="{{ $value }}" {{ old('type') == $value ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="type{{ $value }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Date de début</label>
                                <input type="text" class="form-control datetimepicker" id="start_date" 
                                       name="start_date" value="{{ old('start_date') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Date de fin</label>
                                <input type="text" class="form-control datetimepicker" id="end_date" 
                                       name="end_date" value="{{ old('end_date') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="reason" class="form-label">Motif de la demande</label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" 
                                  required>{{ old('reason') }}</textarea>
                        <div class="form-text">Veuillez décrire brièvement la raison de votre demande.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Pièce justificative (optionnel)</label>
                        <div class="file-upload" id="fileUploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p class="mb-1">Glissez-déposez votre fichier ici ou cliquez pour sélectionner</p>
                            <p class="text-muted small mb-0">Formats acceptés: PDF, JPG, PNG (max. 2 Mo)</p>
                            <input type="file" name="justification_file" id="justification_file" 
                                   class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div id="fileInfo" class="file-name"></div>
                    </div>

                    <div class="d-flex justify-content-end mt-5">
                        <button type="submit" class="btn btn-primary btn-submit">
                            <i class="fas fa-paper-plane me-2"></i> Envoyer la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/fr.js"></script>
<script>
    // Initialisation du datepicker
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".datetimepicker", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            locale: "fr",
            minDate: "today",
            defaultHour: 9,
            defaultMinute: 0,
        });

        // Gestion du champ de fichier
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('justification_file');
        const fileInfo = document.getElementById('fileInfo');

        fileUploadArea.addEventListener('click', function() {
            fileInput.click();
        });

        fileUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-primary');
        });

        fileUploadArea.addEventListener('dragleave', function() {
            this.classList.remove('border-primary');
        });

        fileUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-primary');
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileName();
            }
        });

        fileInput.addEventListener('change', updateFileName);

        function updateFileName() {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                fileInfo.innerHTML = `
                    <i class="fas fa-file-alt me-2"></i>
                    ${file.name} (${formatFileSize(file.size)})
                    <button type="button" class="btn-close ms-2" id="removeFile"></button>
                `;
                
                // Ajouter l'écouteur d'événements pour le bouton de suppression
                document.getElementById('removeFile').addEventListener('click', function(e) {
                    e.stopPropagation();
                    fileInput.value = '';
                    fileInfo.textContent = '';
                });
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Validation du formulaire
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (endDate < startDate) {
                e.preventDefault();
                alert('La date de fin doit être postérieure à la date de début.');
                return false;
            }
            
            return true;
        });
    });
</script>
@endpush
