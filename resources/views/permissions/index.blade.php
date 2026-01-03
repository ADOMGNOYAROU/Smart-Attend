@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Demande de permission</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('permissions.store') }}">
                        @csrf

                        <div class="form-group row mb-3">
                            <label for="type" class="col-md-4 col-form-label text-md-right">Type de demande</label>
                            <div class="col-md-6">
                                <select id="type" class="form-control @error('type') is-invalid @enderror" name="type" required>
                                    <option value="congé">Congé</option>
                                    <option value="absence">Absence</option>
                                    <option value="autre">Autre</option>
                                </select>

                                @error('type')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="start_date" class="col-md-4 col-form-label text-md-right">Date de début</label>
                            <div class="col-md-6">
                                <input id="start_date" type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" name="start_date" required>

                                @error('start_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="end_date" class="col-md-4 col-form-label text-md-right">Date de fin</label>
                            <div class="col-md-6">
                                <input id="end_date" type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" name="end_date" required>

                                @error('end_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="reason" class="col-md-4 col-form-label text-md-right">Motif</label>
                            <div class="col-md-6">
                                <textarea id="reason" class="form-control @error('reason') is-invalid @enderror" name="reason" rows="4" required></textarea>

                                @error('reason')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Envoyer la demande
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
