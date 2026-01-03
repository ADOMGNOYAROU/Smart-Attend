<!-- Modal de modification des horaires de travail -->
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editScheduleForm" action="{{ route('users.update-schedule', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editScheduleModalLabel">
                        <i class="far fa-clock me-2"></i> Modifier les horaires de travail
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i> 
                        Définissez les heures de travail par défaut pour le calcul des retards et des heures supplémentaires.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="work_start_time" class="form-label">Heure de début <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control @error('work_start_time') is-invalid @enderror" 
                                       id="work_start_time" 
                                       name="work_start_time" 
                                       value="{{ old('work_start_time', $user->work_start_time ? $user->work_start_time->format('H:i') : '09:00') }}" 
                                       required>
                                @error('work_start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="work_end_time" class="form-label">Heure de fin <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control @error('work_end_time') is-invalid @enderror" 
                                       id="work_end_time" 
                                       name="work_end_time" 
                                       value="{{ old('work_end_time', $user->work_end_time ? $user->work_end_time->format('H:i') : '17:00') }}" 
                                       required>
                                @error('work_end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jours de travail</label>
                        <div class="row">
                            @php
                                $workDays = $user->work_days ?? [1, 2, 3, 4, 5]; // Par défaut: Lundi à Vendredi
                                $days = [
                                    1 => 'Lundi',
                                    2 => 'Mardi',
                                    3 => 'Mercredi',
                                    4 => 'Jeudi',
                                    5 => 'Vendredi',
                                    6 => 'Samedi',
                                    0 => 'Dimanche'
                                ];
                            @endphp
                            
                            @foreach($days as $key => $day)
                                <div class="col-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="work_days[]" 
                                               value="{{ $key }}" 
                                               id="work_day_{{ $key }}"
                                               {{ in_array($key, $workDays) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="work_day_{{ $key }}">
                                            {{ $day }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="flexible_hours" 
                               name="flexible_hours"
                               {{ $user->flexible_hours ? 'checked' : '' }}>
                        <label class="form-check-label" for="flexible_hours">
                            Horaires flexibles
                        </label>
                        <div class="form-text">
                            Si activé, les employés peuvent avoir des horaires variables selon les jours.
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editScheduleForm = document.getElementById('editScheduleForm');
        const workStartTime = document.getElementById('work_start_time');
        const workEndTime = document.getElementById('work_end_time');
        
        // Validation du formulaire
        editScheduleForm.addEventListener('submit', function(e) {
            const startTime = workStartTime.value;
            const endTime = workEndTime.value;
            
            if (startTime >= endTime) {
                e.preventDefault();
                alert('L\'heure de fin doit être postérieure à l\'heure de début.');
                return false;
            }
            
            // Vérifier qu'au moins un jour est sélectionné
            const checkedDays = document.querySelectorAll('input[name="work_days[]"]:checked').length;
            if (checkedDays === 0) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins un jour de travail.');
                return false;
            }
            
            return true;
        });
        
        // Gestion des cases à cocher pour les jours de travail
        const weekdays = [1, 2, 3, 4, 5]; // Lundi à Vendredi
        const weekend = [0, 6]; // Dimanche et Samedi
        
        // Sélectionner tous les jours de la semaine
        document.getElementById('select_weekdays')?.addEventListener('click', function(e) {
            e.preventDefault();
            weekdays.forEach(day => {
                document.getElementById(`work_day_${day}`).checked = true;
            });
            weekend.forEach(day => {
                document.getElementById(`work_day_${day}`).checked = false;
            });
        });
        
        // Sélectionner le week-end
        document.getElementById('select_weekend')?.addEventListener('click', function(e) {
            e.preventDefault();
            weekdays.forEach(day => {
                document.getElementById(`work_day_${day}`).checked = false;
            });
            weekend.forEach(day => {
                document.getElementById(`work_day_${day}`).checked = true;
            });
        });
        
        // Tout sélectionner
        document.getElementById('select_all_days')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('input[name="work_days[]"]').forEach(checkbox => {
                checkbox.checked = true;
            });
        });
        
        // Tout désélectionner
        document.getElementById('deselect_all_days')?.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('input[name="work_days[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    });
</script>
@endpush
