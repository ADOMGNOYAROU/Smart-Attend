@extends('layouts.app')

@section('title', 'Historique des demandes de permission')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Historique des demandes traitées</span>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-sm btn-outline-primary">
                        Voir les demandes en attente
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($permissions->isEmpty())
                        <div class="alert alert-info">
                            Aucune demande de permission traitée.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Employé</th>
                                        <th>Type</th>
                                        <th>Période</th>
                                        <th>Statut</th>
                                        <th>Date de traitement</th>
                                        <th>Traîté par</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissions as $permission)
                                        <tr>
                                            <td>{{ $permission->user->name }}</td>
                                            <td>
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
                                            </td>
                                            <td>
                                                {{ $permission->start_date->format('d/m/Y H:i') }}<br>
                                                au {{ $permission->end_date->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                @if($permission->status === 'approved')
                                                    <span class="badge bg-success">Approuvé</span>
                                                @else
                                                    <span class="badge bg-danger">Rejeté</span>
                                                @endif
                                            </td>
                                            <td>{{ $permission->processed_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $permission->processedBy->name ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('admin.permissions.show', $permission) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    Voir
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $permissions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
