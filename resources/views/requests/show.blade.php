@extends('layouts.app')

@section('title', 'Détails de la demande')

@push('styles')
<style>
    .detail-card {
        border-left: 4px solid;
    }
    .detail-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.25rem;
    }
    .detail-value {
        margin-bottom: 1rem;
        color: #4e73df;
        font-weight: 500;
    }
    .comment-box {
        background-color: #f8f9fc;
        border-left: 3px solid #4e73df;
        padding: 1rem;
        border-radius: 0.25rem;
    }
    .file-preview {
        max-width: 100%;
        height: auto;
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
    }
    .action-buttons .btn {
        min-width: 120px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Détails de la demande</h1>
        <a href="{{ route('requests.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Retour à la liste
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Demande #{{ $request['id'] }}
                    </h6>
                    <span class="badge 
                        {{ $request['status'] == 'approved' ? 'bg-success' : 
                           ($request['status'] == 'rejected' ? 'bg-danger' : 'bg-warning') }} text-white">
                        {{ ucfirst($request['status']) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="detail-label">Type de demande</div>
                            <div class="detail-value">
                                <i class="fas {{ $request['type_icon'] ?? 'fa-question-circle' }} me-2"></i>
                                {{ $request['type'] }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Date de soumission</div>
                            <div class="detail-value">
                                <i class="far fa-calendar-alt me-2"></i>
                                {{ $request['created_at'] }}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="detail-label">Période</div>
                            <div class="detail-value">
                                <i class="far fa-clock me-2"></i>
                                Du {{ $request['start_date'] }}<br>
                                au {{ $request['end_date'] }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Durée</div>
                            <div class="detail-value">
                                <i class="far fa-hourglass me-2"></i>
                                {{ $request['duration'] ?? 'Non spécifiée' }}
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="detail-label">Motif</div>
                        <div class="p-3 bg-light rounded">
                            {{ $request['reason'] }}
                        </div>
                    </div>

                    @if($request['has_justification'])
                        <div class="mb-4">
                            <div class="detail-label">Pièce justificative</div>
                            <div class="mt-2">
                                @if(str_ends_with(strtolower($request['justification_url']), '.pdf'))
                                    <div class="text-center">
                                        <i class="far fa-file-pdf fa-4x text-danger mb-2"></i>
                                        <p class="mb-0">Document PDF</p>
                                    </div>
                                @elseif(in_array(strtolower(pathinfo($request['justification_url'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']))
                                    <img src="{{ $request['justification_url'] }}" alt="Pièce jointe" class="img-fluid file-preview">
                                @else
                                    <i class="far fa-file-alt fa-2x text-muted me-2"></i>
                                    Fichier joint
                                @endif
                                <div class="mt-2">
                                    <a href="{{ $request['justification_url'] }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       target="_blank"
                                       download>
                                        <i class="fas fa-download me-1"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($request['status'] != 'pending' && !empty($request['admin_comment']))
                        <div class="mt-4">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="m-0">
                                    <i class="fas fa-comment-dots me-2"></i>
                                    Commentaire de l'administrateur
                                </h6>
                            </div>
                            <div class="comment-box">
                                {{ $request['admin_comment'] }}
                                @if(!empty($request['processed_at']))
                                    <div class="text-muted small mt-2">
                                        Traité le {{ $request['processed_at'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </a>
                    
                    <div class="action-buttons">
                        @if($request['status'] == 'pending')
                            <form action="{{ route('requests.cancel', $request['id']) }}" method="POST" class="d-inline" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette demande ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('requests.index') }}" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i> Voir toutes les demandes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
