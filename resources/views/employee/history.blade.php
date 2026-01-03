@extends('layouts.app')

@section('title', 'Historique des pointages')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Historique des pointages</h5>
                    <a href="{{ route('employee.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Retour au tableau de bord
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                @forelse($attendance as $record)
                                    <tr>
                                        <td>{{ $record['date'] ?? '-' }}</td>
                                        <td>{{ $record['check_in'] ?? '-' }}</td>
                                        <td>{{ $record['check_out'] ?? '-' }}</td>
                                        <td>{{ $record['work_duration'] ?? '-' }}</td>
                                        <td>
                                            @if(isset($record['status']))
                                                @if($record['status'] === 'present')
                                                    <span class="badge bg-success">Présent</span>
                                                @elseif($record['status'] === 'late')
                                                    <span class="badge bg-warning">Retard ({{ $record['late_minutes'] ?? 0 }} min)</span>
                                                @else
                                                    <span class="badge bg-danger">Absent</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Non défini</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Aucun enregistrement de pointage trouvé</td>
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
