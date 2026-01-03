@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Pointage</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <h4>{{ now()->format('l d F Y') }}</h4>
                        <div id="current-time" class="h2 font-weight-bold">
                            {{ now()->format('H:i:s') }}
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <form id="check-in-form" action="{{ route('attendance.check-in') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg btn-block py-3 w-100">
                                    <i class="fas fa-sign-in-alt"></i> Pointage d'arrivée
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form id="check-out-form" action="{{ route('attendance.check-out') }}" method="POST" class="d-inline w-100">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-lg btn-block py-3 w-100">
                                    <i class="fas fa-sign-out-alt"></i> Pointage de départ
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Mon pointage aujourd'hui</span>
                                <a href="{{ route('attendance.history') }}" class="btn btn-sm btn-outline-primary">
                                    Voir l'historique
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="mb-2">Arrivée</div>
                                    <div class="h4">
                                        {{ $todayAttendance->check_in ?? '--:--' }}
                                        @if(isset($todayAttendance) && $todayAttendance->status === 'late')
                                            <span class="badge bg-warning">En retard</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-2">Départ</div>
                                    <div class="h4">
                                        {{ $todayAttendance->check_out ?? '--:--' }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <div class="mb-1">Temps de travail</div>
                                <div class="h5">
                                    {{ $todayAttendance->formattedWorkDuration() ?? '--' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Mise à jour de l'heure en temps réel
    function updateClock() {
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                        now.getMinutes().toString().padStart(2, '0') + ':' + 
                        now.getSeconds().toString().padStart(2, '0');
        document.getElementById('current-time').textContent = timeStr;
    }

    // Gestion de la soumission des formulaires
    document.addEventListener('DOMContentLoaded', function() {
        const checkInForm = document.getElementById('check-in-form');
        const checkOutForm = document.getElementById('check-out-form');
        
        if (checkInForm) {
            checkInForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitAttendanceForm(this, 'check-in');
            });
        }
        
        if (checkOutForm) {
            checkOutForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitAttendanceForm(this, 'check-out');
            });
        }
    });

    // Fonction pour soumettre le formulaire en AJAX
    function submitAttendanceForm(form, type) {
        const button = form.querySelector('button[type="submit"]');
        const originalText = button.innerHTML;
        const url = form.getAttribute('action');
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Désactiver le bouton et afficher un indicateur de chargement
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...';
        
        // Envoyer la requête AJAX
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: new URLSearchParams(new FormData(form))
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            // Afficher le message de succès
            showAlert('success', data.message || 'Pointage enregistré avec succès');
            
            // Recharger la page après 1,5 secondes pour afficher les mises à jour
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        })
        .catch(error => {
            // Afficher le message d'erreur
            const errorMessage = error.message || 'Une erreur est survenue lors de l\'enregistrement du pointage';
            showAlert('danger', errorMessage);
            
            // Réactiver le bouton
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
    
    // Fonction pour afficher les messages d'alerte
    function showAlert(type, message) {
        // Supprimer les alertes existantes
        const existingAlerts = document.querySelectorAll('.alert-dynamic');
        existingAlerts.forEach(alert => alert.remove());
        
        // Créer et afficher la nouvelle alerte
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dynamic alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        `;
        
        // Insérer l'alerte en haut de la carte
        const card = document.querySelector('.card-body');
        if (card) {
            card.insertBefore(alertDiv, card.firstChild);
        }
    }

    // Mettre à jour l'heure toutes les secondes
    setInterval(updateClock, 1000);
    updateClock(); // Appel initial
</script>
@endpush
@endsection
