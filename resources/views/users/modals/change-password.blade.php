<!-- Modal de changement de mot de passe -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="changePasswordForm" action="{{ route('users.change-password', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="changePasswordModalLabel">
                        <i class="fas fa-key me-2"></i> Changer le mot de passe
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i> 
                        Le mot de passe doit contenir au moins 8 caractères, dont des lettres majuscules, minuscules, des chiffres et des caractères spéciaux.
                    </div>
                    
                    @if(auth()->user()->isAdmin() && auth()->id() !== $user->id)
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i> 
                            Vous êtes sur le point de modifier le mot de passe de {{ $user->name }}. 
                            L'utilisateur devra se reconnecter après cette modification.
                        </div>
                    @else
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password"
                                       {{ auth()->user()->isAdmin() && auth()->id() !== $user->id ? 'disabled' : 'required' }}>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('new_password') is-invalid @enderror" 
                                   id="new_password" 
                                   name="new_password" 
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                <i class="fas fa-eye"></i>
                            </button>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-text">
                            <div id="password-strength" class="mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="password-strength-text" class="d-block mt-1"></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirmer le nouveau mot de passe <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="new_password_confirmation" 
                                   name="new_password_confirmation" 
                                   required>
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password_confirmation">
                                <i class="fas fa-eye"></i>
                            </button>
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
        const changePasswordForm = document.getElementById('changePasswordForm');
        const newPasswordInput = document.getElementById('new_password');
        const passwordStrengthBar = document.getElementById('password-strength-bar');
        const passwordStrengthText = document.getElementById('password-strength-text');
        
        // Afficher/masquer le mot de passe
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    targetInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
        
        // Vérification de la force du mot de passe
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            
            // Mettre à jour la barre de progression
            passwordStrengthBar.style.width = strength.score * 25 + '%';
            
            // Changer la couleur en fonction de la force
            if (strength.score < 2) {
                passwordStrengthBar.className = 'progress-bar bg-danger';
                passwordStrengthText.textContent = 'Faible';
                passwordStrengthText.className = 'text-danger';
            } else if (strength.score < 4) {
                passwordStrengthBar.className = 'progress-bar bg-warning';
                passwordStrengthText.textContent = 'Moyen';
                passwordStrengthText.className = 'text-warning';
            } else {
                passwordStrengthBar.className = 'progress-bar bg-success';
                passwordStrengthText.textContent = 'Fort';
                passwordStrengthText.className = 'text-success';
            }
            
            // Afficher les exigences non remplies
            if (password.length > 0) {
                let feedback = [];
                
                if (password.length < 8) {
                    feedback.push('Au moins 8 caractères');
                }
                if (!strength.hasLower) {
                    feedback.push('Une lettre minuscule');
                }
                if (!strength.hasUpper) {
                    feedback.push('Une lettre majuscule');
                }
                if (!strength.hasNumber) {
                    feedback.push('Un chiffre');
                }
                if (!strength.hasSpecial) {
                    feedback.push('Un caractère spécial');
                }
                
                if (feedback.length > 0) {
                    passwordStrengthText.textContent = 'Le mot de passe doit contenir : ' + feedback.join(', ');
                }
            }
        });
        
        // Validation du formulaire
        changePasswordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('new_password_confirmation').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères.');
                return false;
            }
            
            const strength = checkPasswordStrength(newPassword);
            if (strength.score < 2) {
                e.preventDefault();
                alert('Veuillez choisir un mot de passe plus fort.');
                return false;
            }
            
            return true;
        });
        
        // Fonction pour vérifier la force du mot de passe
        function checkPasswordStrength(password) {
            let score = 0;
            let hasLower = /[a-z]/.test(password);
            let hasUpper = /[A-Z]/.test(password);
            let hasNumber = /[0-9]/.test(password);
            let hasSpecial = /[^A-Za-z0-9]/.test(password);
            
            // Ajouter des points pour chaque condition remplie
            if (password.length >= 8) score++;
            if (password.length >= 12) score++; // Mot de passe plus long = plus fort
            if (hasLower) score++;
            if (hasUpper) score++;
            if (hasNumber) score++;
            if (hasSpecial) score++;
            
            // Limiter le score à 4 pour la barre de progression (0-4)
            return {
                score: Math.min(4, score),
                hasLower,
                hasUpper,
                hasNumber,
                hasSpecial
            };
        }
    });
</script>
@endpush
