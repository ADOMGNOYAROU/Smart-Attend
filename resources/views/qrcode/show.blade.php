@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Code de pointage généré</div>

                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Votre code de pointage a été généré avec succès.
                    </div>
                    
                    <div class="mb-4">
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <!-- Code QR généré via une API en ligne -->
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($scanUrl) }}" 
                                     alt="Code QR de pointage" 
                                     class="img-fluid">
                            </div>
                            <p class="mb-0">
                                <i class="fas fa-clock"></i> Valable jusqu'à <strong>{{ $expiresAt }}</strong>
                            </p>
                        </div>
                        
                        <div class="form-group">
                            <label for="scan-url">URL de pointage :</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="scan-url" value="{{ $scanUrl }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard()">
                                    <i class="far fa-copy"></i> Copier
                                </button>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Les utilisateurs peuvent utiliser cette URL avec l'application mobile 
                                pour pointer leur arrivée ou leur départ.
                            </small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('qrcode.generate.form') }}" class="btn btn-outline-primary">
                            <i class="fas fa-redo"></i> Générer un nouveau code
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard() {
        const copyText = document.getElementById("scan-url");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        
        // Afficher un message de confirmation
        const button = event.target;
        const originalHTML = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-check"></i> Copié !';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }
</script>
@endpush
@endsection
