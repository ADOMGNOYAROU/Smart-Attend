<!-- resources/views/dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Carte de bienvenue -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Bienvenue, {{ $user['name'] }} !</h5>
                    <p class="card-text">Gérez facilement vos présences et demandes de congés.</p>
                </div>
            </div>
        </div>

        <!-- Pointage du jour -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Pointage du jour</span>
                    <span class="badge bg-primary">{{ now()->format('d/m/Y') }}</span>
                </div>
                <div class="card-body text-center">
                    @if($todayAttendance)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Arrivée :</span>
                                <strong>{{ $todayAttendance['check_in'] ?? 'Non pointé' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Départ :</span>
                                <strong>{{ $todayAttendance['check_out'] ?? 'Non pointé' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Statut :</span>
                                <span class="badge bg-{{ $todayAttendance['status'] === 'present' ? 'success' : 'warning' }}">
                                    {{ $todayAttendance['status'] === 'present' ? 'À l\'heure' : 'En retard' }}
                                </span>
                            </div>
                        </div>

                        @if(!$todayAttendance['has_checked_out'])
                            <form action="{{ route('attendance.check-out') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-box-arrow-right"></i> Pointer la sortie
                                </button>
                            </form>
                        @else
                            <button class="btn btn-secondary" disabled>
                                <i class="bi bi-check-circle"></i> Journée terminée
                            </button>
                        @endif
                    @else
                        <p class="text-muted">Aucun pointage pour aujourd'hui</p>
                        <form action="{{ route('attendance.check-in') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Pointer l'arrivée
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistiques du mois -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    Statistiques du mois
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="h2 text-primary">{{ $history['statistics']['present_days'] ?? 0 }}</div>
                            <div class="text-muted">Présences</div>
                        </div>
                        <div class="col-4">
                            <div class="h2 text-warning">{{ $history['statistics']['late_days'] ?? 0 }}</div>
                            <div class="text-muted">Retards</div>
                        </div>
                        <div class="col-4">
                            <div class="h2 text-danger">{{ $history['statistics']['absent_days'] ?? 0 }}</div>
                            <div class="text-muted">Absences</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Derniers pointages -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Historique récent</span>
                    <a href="{{ route('employee.history') }}" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Arrivée</th>
                                    <th>Départ</th>
                                    <th>Durée</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($history['attendances'] ?? []) as $attendance)
                                    <tr>
                                        <td>{{ $attendance['date'] }}</td>
                                        <td>{{ $attendance['check_in'] ?? '-' }}</td>
                                        <td>{{ $attendance['check_out'] ?? '-' }}</td>
                                        <td>{{ $attendance['work_duration'] ?? '-' }}</td>
                                        <td>
                                            @if($attendance['status'] === 'present')
                                                <span class="badge bg-success">Présent</span>
                                            @elseif($attendance['status'] === 'late')
                                                <span class="badge bg-warning">Retard ({{ $attendance['late_minutes'] ?? 0 }} min)</span>
                                            @else
                                                <span class="badge bg-danger">Absent</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Aucun pointage enregistré</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection