@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Générer un code de pointage</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('qrcode.generate') }}">
                        @csrf
                        
                        <div class="form-group row mb-3">
                            <label for="validity" class="col-md-4 col-form-label text-md-right">Durée de validité (minutes)</label>
                            <div class="col-md-6">
                                <input id="validity" 
                                       type="number" 
                                       class="form-control @error('validity') is-invalid @enderror" 
                                       name="validity" 
                                       value="15" 
                                       min="1" 
                                       max="1440" 
                                       required>
                                @error('validity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <small class="form-text text-muted">Durée pendant laquelle le code sera valable (1-1440 minutes)</small>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-qrcode"></i> Générer le code
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
