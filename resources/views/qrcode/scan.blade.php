@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Scanner le code QR</div>

                <div class="card-body text-center">
                    <div id="scanner-container" class="mb-4">
                        <video id="qr-video" width="100%" style="max-width: 500px; border: 2px solid #ddd;"></video>
                    </div>
                    
                    <div id="result" class="alert d-none">
                        <i class="fas fa-spinner fa-spin"></i> Traitement en cours...
                    </div>
                    
                    <div id="success-message" class="alert alert-success d-none">
                        <i class="fas fa-check-circle"></i> <span id="success-text"></span>
                        <div class="mt-2">
                            <strong>Heure :</strong> <span id="check-time"></span><br>
                            <strong>Date :</strong> <span id="check-date"></span>
                        </div>
                    </div>
                    
                    <div id="error-message" class="alert alert-danger d-none">
                        <i class="fas fa-exclamation-circle"></i> <span id="error-text"></span>
                    </div>
                    
                    <a href="{{ route('home') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-left"></i> Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.getElementById('qr-video');
        const resultContainer = document.getElementById('result');
        const successMessage = document.getElementById('success-message');
        const successText = document.getElementById('success-text');
        const checkTime = document.getElementById('check-time');
        const checkDate = document.getElementById('check-date');
        const errorMessage = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        
        // Désactiver le bouton de retour après la soumission
        let isProcessing = false;
        
        // Démarrer la caméra
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(function(stream) {
                video.srcObject = stream;
                video.setAttribute("playsinline", true);
                video.play();
                requestAnimationFrame(tick);
            })
            .catch(function(err) {
                console.error("Erreur d'accès à la caméra : ", err);
                errorText.textContent = "Impossible d'accéder à la caméra. Veuillez vérifier les permissions.";
                errorMessage.classList.remove('d-none');
            });
        
        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA && !isProcessing) {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });
                
                if (code) {
                    processQRCode(code.data);
                }
            }
            
            if (!isProcessing) {
                requestAnimationFrame(tick);
            }
        }
        
        function processQRCode(url) {
            isProcessing = true;
            resultContainer.classList.remove('d-none');
            
            // Extraire le code du QR de l'URL
            const code = url.split('/').pop();
            
            // Envoyer la requête au serveur
            fetch(`/qrcode/scan/${code}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                resultContainer.classList.add('d-none');
                
                if (data.success) {
                    successText.textContent = data.message;
                    checkTime.textContent = data.time;
                    checkDate.textContent = data.date;
                    successMessage.classList.remove('d-none');
                    
                    // Arrêter la caméra après un scan réussi
                    const stream = video.srcObject;
                    const tracks = stream.getTracks();
                    tracks.forEach(track => track.stop());
                } else {
                    errorText.textContent = data.message;
                    errorMessage.classList.remove('d-none');
                    isProcessing = false; // Permettre une nouvelle tentative en cas d'erreur
                }
            })
            .catch(error => {
                console.error('Erreur :', error);
                errorText.textContent = 'Une erreur est survenue lors du traitement.';
                errorMessage.classList.remove('d-none');
                isProcessing = false; // Permettre une nouvelle tentative en cas d'erreur
            });
        }
    });
</script>
@endpush

@push('styles')
<style>
    #scanner-container {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }
    
    #scanner-container::before {
        content: "";
        display: block;
        padding-bottom: 100%; /* Format carré */
    }
    
    #qr-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .alert {
        margin-top: 1rem;
    }
</style>
@endpush
@endsection
