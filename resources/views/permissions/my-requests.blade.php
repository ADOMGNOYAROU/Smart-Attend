@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Mes demandes de permission</span>
                    <a href="{{ route('permissions.index') }}" class="btn btn-primary btn-sm">
                        Nouvelle demande
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($permissions->isEmpty())
                        <div class="alert alert-info">
                            Vous n'avez aucune demande de permission pour le moment.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Début</th>
                                        <th>Fin</th>
                                        <th>Motif</th>
                                        <th>Statut</th>
                                        <th>Date de demande</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissions as $permission)
                                        <tr>
                                            <td>{{ ucfirst($permission->type) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($permission->start_date)->format('d/m/Y H:i') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($permission->end_date)->format('d/m/Y H:i') }}</td>
                                            <td>{{ Str::limit($permission->reason, 30) }}</td>
                                            <td>
                                                @php
                                                    $statusClass = [
                                                        'en_attente' => 'badge bg-warning',
                                                        'approuvée' => 'badge bg-success',
                                                        'rejetée' => 'badge bg-danger',
                                                    ][$permission->status] ?? 'badge bg-secondary';
                                                @endphp
                                                <span class="{{ $statusClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $permission->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $permission->created_at->format('d/m/Y H:i') }}</td>
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
