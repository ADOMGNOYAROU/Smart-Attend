@extends('layouts.app')

@section('title', 'Détails de la demande de permission')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    Détails de la demande de permission
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h5>Informations de l'employé</h5>
                        <p class="mb-1"><strong>Nom :</strong> {{ $permission->user->name }}</p>
                        <p class="mb-1"><strong>Email :</strong> {{ $permission->user->email }}</p>
                        <p class="mb-0"><strong>Département :</strong> {{ $permission->user->department ?? 'Non spécifié' }}</p>
                    </div>

                    <div class="mb-4">
                        <h5>Détails de la demande</h5>
                        <p class="mb-1">
                            <strong>Type :</strong>
                            @switch($permission->type)
                                @case('retard')
                                    <span class="badge bg-warning">Retard</span>
                                    @break
                                @case('absence')
                                    <span class="badge bg-danger">Absence</span>
                                    @break
                                @case('sortie_anticipee')
                                    <span class="badge bg-info">Sortie anticipée</span>
                                    @break
                                @case('teletravail')
                                    <span class="badge bg-primary">Télétravail</span>
                                    @break
                                @case('mission_exterieure')
                                    <span class="badge bg-secondary">Mission extérieure</span>
                                    @break
                            @endswitch
                        </p>
                        <p class="mb-1"><strong>Date de début :</strong> {{ $permission->start_date->format('d/m/Y H:i') }}</p>
                        <p class="mb-1"><strong>Date de fin :</strong> {{ $permission->end_date->format('d/m/Y H:i') }}</p>
                        <p class="mb-0"><strong>Durée :</strong> {{ $permission->start_date->diffForHumans($permission->end_date, true) }}</p>
                    </div>

                    <div class="mb-4">
                        <h5>Motif de la demande</h5>
                        <p class="bg-light p-3 rounded">
                            {{ $permission->reason }}
                        </p>
                    </div>

                    @if($permission->justification_file)
                        <div class="mb-4">
                            <h5>Fichier justificatif</h5>
                            <a href="{{ asset('storage/' . $permission->justification_file) }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Télécharger le justificatif
                            </a>
                        </div>
                    @endif

                    @if($permission->status === 'pending')
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                Traiter la demande
                            </div>
                            <div class="card-body">
                                <form action="{{ route('admin.permissions.respond', $permission) }}" method="POST">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="status" 
                                                   id="status_approved" value="approved" checked>
                                            <label class="form-check-label text-success fw-bold" for="status_approved">
                                                Approuver la demande
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="status" 
                                                   id="status_rejected" value="rejected">
                                            <label class="form-check-label text-danger fw-bold" for="status_rejected">
                                                Rejeter la demande
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3" id="rejection_reason_container" style="display: none;">
                                        <label for="admin_response" class="form-label">
                                            Motif du rejet <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('admin_response') is-invalid @enderror" 
                                                  name="admin_response" id="admin_response" rows="3" 
                                                  required></textarea>
                                        @error('admin_response')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left"></i> Retour
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Valider la décision
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-{{ $permission->status === 'approved' ? 'success' : 'danger' }}">
                            <h5 class="alert-heading">
                                Demande {{ $permission->status === 'approved' ? 'approuvée' : 'rejetée' }}
                                @if($permission->processed_by && $permission->processed_at)
                                    <small class="d-block">
                                        Par {{ $permission->processedBy->name }} 
                                        le {{ $permission->processed_at->format('d/m/Y à H:i') }}
                                    </small>
                                @endif
                            </h5>
                            @if($permission->status === 'rejected' && $permission->admin_response)
                                <p class="mb-0">
                                    <strong>Motif du rejet :</strong> {{ $permission->admin_response }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="text-end">
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rejectionRadio = document.getElementById('status_rejected');
        const rejectionReasonContainer = document.getElementById('rejection_reason_container');
        const adminResponseField = document.getElementById('admin_response');
        
        function toggleRejectionReason() {
            if (rejectionRadio.checked) {
                rejectionReasonContainer.style.display = 'block';
                adminResponseField.required = true;
            } else {
                rejectionReasonContainer.style.display = 'none';
                adminResponseField.required = false;
            }
        }
        
        // Initial state
        toggleRejectionReason();
        
        // Add event listeners to both radio buttons
        document.querySelectorAll('input[name="status"]').forEach(radio => {
            radio.addEventListener('change', toggleRejectionReason);
        });
    });
</script>
@endpush
@endsection
