@extends('layouts.app')

@section('title', 'Historique des pointages')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-radius: 10px;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
    }
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .day-off {
        background-color: rgba(248, 249, 250, 0.7);
    }
    .time-cell {
        font-family: 'Courier New', monospace;
        font-weight: bold;
    }
    .export-btn {
        min-width: 120px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Historique des pointages</h1>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
            <button class="btn btn-primary export-btn" id="exportPdf">
                <i class="bi bi-file-earmark-pdf me-1"></i> Exporter en PDF
            </button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employee.history') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="month" class="form-label">Mois</label>
                    <select name="month" id="month" class="form-select">
                        @foreach($months as $key => $name)
                            <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="year" class="form-label">Année</label>
                    <select name="year" id="year" class="form-select">
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel"></i> Filtrer
                    </button>
                    <a href="{{ route('employee.history') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">Jours présents</h6>
                            <h2 class="mb-0">{{ $statistics->present_days ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-dark-50 mb-1">Retards</h6>
                            <h2 class="mb-0">{{ $statistics->late_days ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">Absences</h6>
                            <h2 class="mb-0">{{ $statistics->absent_days ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-1">Heures totales</h6>
                            <h2 class="mb-0">{{ $statistics->total_hours ?? 0 }}<small class="fs-6">h</small></h2>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des pointages -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <i class="bi bi-calendar3 me-2"></i>
                {{ $monthName }} {{ $year }}
            </h5>
            <div class="d-flex align-items-center">
                <div class="input-group input-group-sm me-3" style="max-width: 250px;">
                    <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Rechercher...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="attendanceTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Jour</th>
                            <th>Arrivée</th>
                            <th>Départ</th>
                            <th>Durée</th>
                            <th>Statut</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            @php
                                $date = \Carbon\Carbon::createFromFormat('d/m/Y', $attendance['date']);
                                $isWeekend = $date->isWeekend();
                                $isToday = $date->isToday();
                                $rowClass = $isWeekend ? 'day-off' : '';
                                $rowClass .= $isToday ? ' table-active' : '';
                            @endphp
                            
                            <tr class="{{ trim($rowClass) }}">
                                <td class="ps-3 fw-medium">
                                    @if($isToday)
                                        <span class="badge bg-primary me-2">Aujourd'hui</span>
                                    @endif
                                    {{ $attendance['date'] }}
                                </td>
                                <td>
                                    <span class="d-inline-flex align-items-center">
                                        @if($isWeekend)
                                            <i class="bi bi-umbrella me-2 text-primary"></i>
                                        @else
                                            <i class="bi bi-briefcase me-2 text-muted"></i>
                                        @endif
                                        {{ $date->isoFormat('dddd') }}
                                    </span>
                                </td>
                                <td class="time-cell">
                                    @if(isset($attendance['check_in']))
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>
                                            {{ $attendance['check_in'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">--:--</span>
                                    @endif
                                </td>
                                <td class="time-cell">
                                    @if(isset($attendance['check_out']))
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-box-arrow-right me-1"></i>
                                            {{ $attendance['check_out'] }}
                                        </span>
                                    @elseif(isset($attendance['check_in']))
                                        <span class="text-warning">En cours...</span>
                                    @else
                                        <span class="text-muted">--:--</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($attendance['work_duration']))
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-clock-history me-1"></i>
                                            {{ $attendance['work_duration'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">--:--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance['status'] === 'present')
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="bi bi-check-circle me-1"></i> Présent
                                        </span>
                                    @elseif($attendance['status'] === 'late')
                                        <span class="badge bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-exclamation-triangle me-1"></i> 
                                            Retard ({{ $attendance['late_minutes'] ?? 0 }} min)
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">
                                            <i class="bi bi-x-circle me-1"></i> Absent
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if(isset($attendance['check_in']) || isset($attendance['check_out']))
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                data-bs-target="#detailsModal" data-date="{{ $attendance['date'] }}">
                                            <i class="bi bi-eye"></i> Détails
                                        </button>
                                    @else
                                        <span class="text-muted">Aucune action</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                                        <h5 class="text-muted">Aucun pointage enregistré</h5>
                                        <p class="text-muted mb-0">Aucun pointage trouvé pour la période sélectionnée.</p>
                                        @if($hasSearched)
                                            <a href="{{ route('employee.history') }}" class="btn btn-sm btn-outline-primary mt-3">
                                                Voir le mois en cours
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
        @if(isset($pagination) && $pagination->total > 0)
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Affichage de <span class="fw-semibold">{{ $pagination->from ?? 0 }}</span> à <span class="fw-semibold">{{ $pagination->to ?? 0 }}</span> sur <span class="fw-semibold">{{ $pagination->total ?? 0 }}</span> entrées
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            @if($pagination->current_page > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $pagination->currentPage - 1]) }}" aria-label="Précédent">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @endif
                            
                            @for($i = 1; $i <= $pagination->lastPage; $i++)
                                <li class="page-item {{ $i == $pagination->currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                </li>
                            @endfor
                            
                            @if($pagination->currentPage < $pagination->lastPage)
                                <li class="page-item">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $pagination->currentPage + 1]) }}" aria-label="Suivant">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                    <div class="text-muted small">
                        Page <span class="fw-semibold">{{ $pagination->current_page }}</span> sur <span class="fw-semibold">{{ $pagination->last_page }}</span>
                    </div>
                </div>
            </div>
        @endif

<!-- Modal Détails -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Détails du pointage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Contenu chargé dynamiquement -->
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary">
                    <i class="bi bi-printer me-1"></i> Imprimer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Recherche dans le tableau
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#attendanceTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // Pagination simple
    let currentPage = 1;
    const rowsPerPage = 10;
    const rows = document.querySelectorAll('#attendanceTable tbody tr');
    
    function showPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        rows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });
        
        // Mise à jour des boutons de pagination
        document.getElementById('prevPage').disabled = (page === 1);
        document.getElementById('nextPage').disabled = (end >= rows.length);
    }
    
    // Écouteurs d'événements pour les boutons de pagination
    document.getElementById('prevPage')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
        }
    });
    
    document.getElementById('nextPage')?.addEventListener('click', () => {
        if ((currentPage * rowsPerPage) < rows.length) {
            currentPage++;
            showPage(currentPage);
        }
    });
    
    // Initialisation
    if (rows.length > 0) {
        showPage(1);
    }
    
    // Gestion du modal de détails
    const detailsModal = document.getElementById('detailsModal');
    if (detailsModal) {
        detailsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const date = button.getAttribute('data-date');
            const modalContent = document.getElementById('detailsContent');
            
            // Simulation de chargement des détails
            // Dans une vraie application, vous feriez une requête AJAX ici
            setTimeout(() => {
                modalContent.innerHTML = `
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Date</h6>
                        <p class="mb-0">${date}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Heures travaillées</h6>
                        <p class="h4">8h 15min</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Détails</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-box-arrow-in-right text-success me-2"></i>
                                Arrivée: 08:15
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-cup-hot text-info me-2"></i>
                                Pause déjeuner: 12:00 - 13:30
                            </li>
                            <li>
                                <i class="bi bi-box-arrow-right text-danger me-2"></i>
                                Départ: 17:30
                            </li>
                        </ul>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Retard de 15 minutes enregistré ce jour.
                    </div>
                `;
            }, 500);
        });
    }
    
    // Export PDF
    document.getElementById('exportPdf')?.addEventListener('click', function() {
        // Ici, vous pourriez utiliser une bibliothèque comme jsPDF
        // ou faire une requête vers une route qui génère un PDF côté serveur
        alert('Fonctionnalité d\'export PDF à implémenter');
    });
</script>
@endpush
