

<?php $__env->startSection('title', 'Mon Profil'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-3">
    <!-- En-tête avec progression -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">
                                <i class="fas fa-user-edit me-2"></i>
                                Compléter mon profil
                            </h1>
                            <p class="mb-0 opacity-90">
                                Aidez-nous à mieux vous connaître en complétant vos informations personnelles
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <!-- Indicateur de progression circulaire -->
                            <div class="progress-circle" id="progressCircle">
                                <div class="progress-circle-inner">
                                    <span class="progress-percentage" id="progressPercentage">0%</span>
                                    <small class="progress-label">Complété</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de profil -->
    <form id="profileForm" method="POST" action="<?php echo e(route('customer.profile.update')); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        
        <!-- Section 1: Informations personnelles -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-user me-2"></i>
                    1. Informations personnelles
                    <span class="badge bg-danger ms-2">Obligatoire</span>
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Prénom -->
                    <div class="col-md-6">
                        <label for="first_name" class="form-label fw-semibold">
                            Prénom <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="first_name" 
                               name="first_name" 
                               value="<?php echo e(old('first_name', $customer->first_name ?? '')); ?>"
                               required
                               minlength="2"
                               placeholder="Votre prénom">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Nom -->
                    <div class="col-md-6">
                        <label for="last_name" class="form-label fw-semibold">
                            Nom de famille <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="last_name" 
                               name="last_name" 
                               value="<?php echo e(old('last_name', $customer->last_name ?? '')); ?>"
                               required
                               minlength="2"
                               placeholder="Votre nom de famille">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">
                            Adresse email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control form-control-lg" 
                               id="email" 
                               name="email" 
                               value="<?php echo e(old('email', $customer->email ?? '')); ?>"
                               required
                               placeholder="votre@email.com">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Téléphone -->
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-semibold">
                            Téléphone <span class="text-danger">*</span>
                        </label>
                        <input type="tel" 
                               class="form-control form-control-lg" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo e(old('phone', $customer->phone ?? '')); ?>"
                               required
                               placeholder="+33 1 23 45 67 89">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Date de naissance -->
                    <div class="col-md-6">
                        <label for="date_of_birth" class="form-label fw-semibold">
                            Date de naissance
                        </label>
                        <input type="date" 
                               class="form-control form-control-lg" 
                               id="date_of_birth" 
                               name="date_of_birth" 
                               value="<?php echo e(old('date_of_birth', $customer->date_of_birth ?? '')); ?>"
                               max="<?php echo e(date('Y-m-d', strtotime('-16 years'))); ?>">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Genre -->
                    <div class="col-md-6">
                        <label for="gender" class="form-label fw-semibold">Genre</label>
                        <select class="form-select form-select-lg" id="gender" name="gender">
                            <option value="">Sélectionner...</option>
                            <option value="male" <?php echo e(old('gender', $customer->gender ?? '') == 'male' ? 'selected' : ''); ?>>Homme</option>
                            <option value="female" <?php echo e(old('gender', $customer->gender ?? '') == 'female' ? 'selected' : ''); ?>>Femme</option>
                            <option value="other" <?php echo e(old('gender', $customer->gender ?? '') == 'other' ? 'selected' : ''); ?>>Autre</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Adresse -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    2. Adresse
                    <span class="badge bg-danger ms-2">Obligatoire</span>
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Adresse -->
                    <div class="col-12">
                        <label for="address" class="form-label fw-semibold">
                            Adresse complète <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="address" 
                               name="address" 
                               value="<?php echo e(old('address', $customer->address ?? '')); ?>"
                               required
                               minlength="10"
                               placeholder="123 Rue de la Paix">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Code postal -->
                    <div class="col-md-4">
                        <label for="postal_code" class="form-label fw-semibold">Code postal</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="postal_code" 
                               name="postal_code" 
                               value="<?php echo e(old('postal_code', $customer->postal_code ?? '')); ?>"
                               placeholder="75001">
                    </div>
                    
                    <!-- Ville -->
                    <div class="col-md-4">
                        <label for="city" class="form-label fw-semibold">
                            Ville <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="city" 
                               name="city" 
                               value="<?php echo e(old('city', $customer->city ?? '')); ?>"
                               required
                               minlength="2"
                               placeholder="Paris">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Pays -->
                    <div class="col-md-4">
                        <label for="country" class="form-label fw-semibold">
                            Pays <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg" id="country" name="country" required>
                            <option value="">Sélectionner...</option>
                            <option value="France" <?php echo e(old('country', $customer->country ?? '') == 'France' ? 'selected' : ''); ?>>France</option>
                            <option value="Belgique" <?php echo e(old('country', $customer->country ?? '') == 'Belgique' ? 'selected' : ''); ?>>Belgique</option>
                            <option value="Suisse" <?php echo e(old('country', $customer->country ?? '') == 'Suisse' ? 'selected' : ''); ?>>Suisse</option>
                            <option value="Canada" <?php echo e(old('country', $customer->country ?? '') == 'Canada' ? 'selected' : ''); ?>>Canada</option>
                            <option value="Maroc" <?php echo e(old('country', $customer->country ?? '') == 'Maroc' ? 'selected' : ''); ?>>Maroc</option>
                            <option value="Algérie" <?php echo e(old('country', $customer->country ?? '') == 'Algérie' ? 'selected' : ''); ?>>Algérie</option>
                            <option value="Tunisie" <?php echo e(old('country', $customer->country ?? '') == 'Tunisie' ? 'selected' : ''); ?>>Tunisie</option>
                            <option value="Autre" <?php echo e(old('country', $customer->country ?? '') == 'Autre' ? 'selected' : ''); ?>>Autre</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Nationalité -->
                    <div class="col-md-6">
                        <label for="nationality" class="form-label fw-semibold">Nationalité</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="nationality" 
                               name="nationality" 
                               value="<?php echo e(old('nationality', $customer->nationality ?? '')); ?>"
                               placeholder="Française">
                    </div>
                    
                    <!-- Numéro d'identité -->
                    <div class="col-md-6">
                        <label for="id_number" class="form-label fw-semibold">Numéro d'identité</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="id_number" 
                               name="id_number" 
                               value="<?php echo e(old('id_number', $customer->id_number ?? '')); ?>"
                               placeholder="Carte d'identité, passeport...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Contact d'urgence -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 text-warning">
                    <i class="fas fa-phone-alt me-2"></i>
                    3. Contact d'urgence
                    <span class="badge bg-warning ms-2">Recommandé</span>
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Nom du contact -->
                    <div class="col-md-6">
                        <label for="emergency_contact_name" class="form-label fw-semibold">Nom du contact</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="emergency_contact_name" 
                               name="emergency_contact_name" 
                               value="<?php echo e(old('emergency_contact_name', $customer->emergency_contact_name ?? '')); ?>"
                               placeholder="Nom et prénom">
                    </div>
                    
                    <!-- Téléphone du contact -->
                    <div class="col-md-6">
                        <label for="emergency_contact_phone" class="form-label fw-semibold">Téléphone du contact</label>
                        <input type="tel" 
                               class="form-control form-control-lg" 
                               id="emergency_contact_phone" 
                               name="emergency_contact_phone" 
                               value="<?php echo e(old('emergency_contact_phone', $customer->emergency_contact_phone ?? '')); ?>"
                               placeholder="+33 1 23 45 67 89">
                    </div>
                    
                    <!-- Relation -->
                    <div class="col-md-6">
                        <label for="emergency_contact_relation" class="form-label fw-semibold">Relation</label>
                        <select class="form-select form-select-lg" id="emergency_contact_relation" name="emergency_contact_relation">
                            <option value="">Sélectionner...</option>
                            <option value="Conjoint(e)" <?php echo e(old('emergency_contact_relation', $customer->emergency_contact_relation ?? '') == 'Conjoint(e)' ? 'selected' : ''); ?>>Conjoint(e)</option>
                            <option value="Parent" <?php echo e(old('emergency_contact_relation', $customer->emergency_contact_relation ?? '') == 'Parent' ? 'selected' : ''); ?>>Parent</option>
                            <option value="Enfant" <?php echo e(old('emergency_contact_relation', $customer->emergency_contact_relation ?? '') == 'Enfant' ? 'selected' : ''); ?>>Enfant</option>
                            <option value="Frère/Sœur" <?php echo e(old('emergency_contact_relation', $customer->emergency_contact_relation ?? '') == 'Frère/Sœur' ? 'selected' : ''); ?>>Frère/Sœur</option>
                            <option value="Ami(e)" <?php echo e(old('emergency_contact_relation', $customer->emergency_contact_relation ?? '') == 'Ami(e)' ? 'selected' : ''); ?>>Ami(e)</option>
                            <option value="Collègue" <?php echo e(old('emergency_contact_relation', $customer->emergency_contact_relation ?? '') == 'Collègue' ? 'selected' : ''); ?>>Collègue</option>
                            <option value="Autre" <?php echo e(old('emergency_contact_relation', $customer->emergency_contact_relation ?? '') == 'Autre' ? 'selected' : ''); ?>>Autre</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3.5: Options de paiement -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 text-success">
                    <i class="fas fa-credit-card me-2"></i>
                    4. Options de paiement
                    <span class="badge bg-success ms-2">Sécurisé</span>
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Acceptation de la tokenisation -->
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="accept_tokenisation" name="accept_tokenisation" value="1" 
                                   <?php echo e((old('accept_tokenisation', $customer->accept_tokenisation ?? false)) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="accept_tokenisation">
                                J'accepte la tokenisation sécurisée de ma carte bancaire pour faciliter mes paiements futurs
                            </label>
                        </div>
                        <div class="form-text mt-2">
                            <i class="fas fa-shield-alt text-success me-1"></i>
                            La tokenisation est un processus sécurisé qui permet de stocker les informations de votre carte bancaire sous forme cryptée. 
                            Aucune donnée sensible n'est conservée, seul un jeton unique est stocké pour faciliter vos paiements futurs. 
                            Vous pouvez retirer votre consentement à tout moment.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Préférences -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h5 class="mb-0 text-info">
                    <i class="fas fa-cog me-2"></i>
                    5. Préférences et demandes spéciales
                    <span class="badge bg-info ms-2">Optionnel</span>
                </h5>
            </div>
            <div class="card-body p-4">
                <!-- Préférences de communication -->
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Préférences de communication</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="pref_email" name="preferences[communication_email]" value="1" 
                                       <?php echo e((old('preferences.communication_email', $preferences['communication_email'] ?? false)) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="pref_email">
                                    <i class="fas fa-envelope me-1"></i> Recevoir des emails
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="pref_sms" name="preferences[communication_sms]" value="1"
                                       <?php echo e((old('preferences.communication_sms', $preferences['communication_sms'] ?? false)) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="pref_sms">
                                    <i class="fas fa-sms me-1"></i> Recevoir des SMS
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="pref_phone" name="preferences[communication_phone]" value="1"
                                       <?php echo e((old('preferences.communication_phone', $preferences['communication_phone'] ?? false)) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="pref_phone">
                                    <i class="fas fa-phone me-1"></i> Appels téléphoniques
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Préférences de séjour -->
                <div class="mb-4">
                    <h6 class="fw-semibold mb-3">Préférences de séjour</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="room_preference" class="form-label">Type de chambre préféré</label>
                            <select class="form-select" id="room_preference" name="preferences[room_preference]">
                                <option value="">Aucune préférence</option>
                                <option value="quiet" <?php echo e(old('preferences.room_preference', $preferences['room_preference'] ?? '') == 'quiet' ? 'selected' : ''); ?>>Chambre calme</option>
                                <option value="view" <?php echo e(old('preferences.room_preference', $preferences['room_preference'] ?? '') == 'view' ? 'selected' : ''); ?>>Avec vue</option>
                                <option value="high_floor" <?php echo e(old('preferences.room_preference', $preferences['room_preference'] ?? '') == 'high_floor' ? 'selected' : ''); ?>>Étage élevé</option>
                                <option value="ground_floor" <?php echo e(old('preferences.room_preference', $preferences['room_preference'] ?? '') == 'ground_floor' ? 'selected' : ''); ?>>Rez-de-chaussée</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="special_diet" class="form-label">Régime alimentaire</label>
                            <select class="form-select" id="special_diet" name="preferences[special_diet]">
                                <option value="">Aucun régime spécial</option>
                                <option value="vegetarian" <?php echo e(old('preferences.special_diet', $preferences['special_diet'] ?? '') == 'vegetarian' ? 'selected' : ''); ?>>Végétarien</option>
                                <option value="vegan" <?php echo e(old('preferences.special_diet', $preferences['special_diet'] ?? '') == 'vegan' ? 'selected' : ''); ?>>Végétalien</option>
                                <option value="halal" <?php echo e(old('preferences.special_diet', $preferences['special_diet'] ?? '') == 'halal' ? 'selected' : ''); ?>>Halal</option>
                                <option value="kosher" <?php echo e(old('preferences.special_diet', $preferences['special_diet'] ?? '') == 'kosher' ? 'selected' : ''); ?>>Casher</option>
                                <option value="gluten_free" <?php echo e(old('preferences.special_diet', $preferences['special_diet'] ?? '') == 'gluten_free' ? 'selected' : ''); ?>>Sans gluten</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Demandes spéciales -->
                <div class="mb-3">
                    <label for="special_requests" class="form-label fw-semibold">Demandes spéciales</label>
                    <textarea class="form-control" 
                              id="special_requests" 
                              name="special_requests" 
                              rows="3" 
                              maxlength="1000"
                              placeholder="Décrivez vos demandes spéciales ou besoins particuliers..."><?php echo e(old('special_requests', $customer->special_requests ?? '')); ?></textarea>
                    <div class="form-text">Maximum 1000 caractères</div>
                </div>

                <!-- Notes -->
                <div class="mb-3">
                    <label for="notes" class="form-label fw-semibold">Notes additionnelles</label>
                    <textarea class="form-control" 
                              id="notes" 
                              name="notes" 
                              rows="2" 
                              maxlength="500"
                              placeholder="Informations complémentaires..."><?php echo e(old('notes', $customer->notes ?? '')); ?></textarea>
                    <div class="form-text">Maximum 500 caractères</div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-info-circle text-info"></i>
                            </div>
                            <div>
                                <small class="text-muted">
                                    Vos informations sont sécurisées et ne seront utilisées que pour améliorer votre expérience.
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-outline-secondary me-2" onclick="resetForm()">
                            <i class="fas fa-undo me-1"></i> Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" id="saveButton">
                            <i class="fas fa-save me-1"></i> Sauvegarder
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Styles CSS -->
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.progress-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: conic-gradient(#28a745 0deg, #28a745 0deg, #e9ecef 0deg, #e9ecef 360deg);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.progress-circle-inner {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.progress-percentage {
    font-size: 14px;
    font-weight: bold;
    color: #333;
    line-height: 1;
}

.progress-label {
    font-size: 10px;
    color: #666;
    line-height: 1;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-1px);
}

.invalid-feedback {
    display: block;
}

.is-invalid {
    border-color: #dc3545;
}

.is-valid {
    border-color: #28a745;
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileForm');
    const progressCircle = document.getElementById('progressCircle');
    const progressPercentage = document.getElementById('progressPercentage');
    
    // Champs obligatoires
    const requiredFields = [
        'first_name', 'last_name', 'email', 'phone', 
        'address', 'city', 'country'
    ];
    
    // Champs importants
    const importantFields = [
        'postal_code', 'date_of_birth', 'gender', 'nationality',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation'
    ];
    
    // Champs optionnels
    const optionalFields = [
        'id_number', 'special_requests', 'notes', 'accept_tokenisation',
        'preferences[communication_email]', 'preferences[communication_sms]', 
        'preferences[communication_phone]', 'preferences[room_preference]', 'preferences[special_diet]'
    ];
    
    // Calculer la progression
    function calculateProgress() {
        let completedRequired = 0;
        let completedImportant = 0;
        let completedOptional = 0;
        
        // Vérifier les champs obligatoires
        requiredFields.forEach(field => {
            const element = document.querySelector(`[name="${field}"]`);
            if (element && element.value.trim() !== '') {
                completedRequired++;
            }
        });
        
        // Vérifier les champs importants
        importantFields.forEach(field => {
            const element = document.querySelector(`[name="${field}"]`);
            if (element && element.value.trim() !== '') {
                completedImportant++;
            }
        });
        
        // Vérifier les champs optionnels
        optionalFields.forEach(field => {
            const element = document.querySelector(`[name="${field}"]`);
            if (element) {
                if (element.type === 'checkbox') {
                    if (element.checked) completedOptional++;
                } else if (element.value.trim() !== '') {
                    completedOptional++;
                }
            }
        });
        
        // Calcul pondéré
        const requiredWeight = 60; // 60%
        const importantWeight = 30; // 30%
        const optionalWeight = 10; // 10%
        
        const requiredProgress = (completedRequired / requiredFields.length) * requiredWeight;
        const importantProgress = (completedImportant / importantFields.length) * importantWeight;
        const optionalProgress = (completedOptional / optionalFields.length) * optionalWeight;
        
        const totalProgress = Math.round(requiredProgress + importantProgress + optionalProgress);
        
        updateProgressCircle(totalProgress);
        return totalProgress;
    }
    
    // Mettre à jour le cercle de progression
    function updateProgressCircle(percentage) {
        const degrees = (percentage / 100) * 360;
        let color = '#dc3545'; // Rouge
        
        if (percentage >= 91) color = '#28a745'; // Vert
        else if (percentage >= 71) color = '#007bff'; // Bleu
        else if (percentage >= 41) color = '#fd7e14'; // Orange
        
        progressCircle.style.background = `conic-gradient(${color} ${degrees}deg, #e9ecef ${degrees}deg)`;
        progressPercentage.textContent = `${percentage}%`;
    }
    
    // Validation en temps réel
    function validateField(field) {
        const value = field.value.trim();
        const isRequired = requiredFields.includes(field.name);
        
        field.classList.remove('is-valid', 'is-invalid');
        
        if (isRequired && value === '') {
            field.classList.add('is-invalid');
            field.nextElementSibling.textContent = 'Ce champ est obligatoire';
            return false;
        }
        
        if (field.minLength && value.length < field.minLength) {
            field.classList.add('is-invalid');
            field.nextElementSibling.textContent = `Minimum ${field.minLength} caractères`;
            return false;
        }
        
        if (field.type === 'email' && value !== '') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                field.classList.add('is-invalid');
                field.nextElementSibling.textContent = 'Email invalide';
                return false;
            }
        }
        
        if (field.type === 'tel' && value !== '') {
            const phoneRegex = /^[+]?[(]?[0-9]{1,4}[)]?[-\s.]?[0-9]{1,3}[-\s.]?[0-9]{1,4}[-\s.]?[0-9]{1,4}$/;
            if (!phoneRegex.test(value)) {
                field.classList.add('is-invalid');
                field.nextElementSibling.textContent = 'Téléphone invalide';
                return false;
            }
        }
        
        if (value !== '') {
            field.classList.add('is-valid');
        }
        
        return true;
    }
    
    // Réinitialiser le formulaire
    window.resetForm = function() {
        if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ?')) {
            form.reset();
            document.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                el.classList.remove('is-valid', 'is-invalid');
            });
            calculateProgress();
        }
    };
    
    // Validation du formulaire
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Valider tous les champs
        requiredFields.forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field && !validateField(field)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire.');
        }
    });
    
    // Validation en temps réel
    form.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
            calculateProgress();
        });
        
        field.addEventListener('change', function() {
            calculateProgress();
        });
    });
    
    // Calculer la progression initiale
    calculateProgress();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.customer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/customer/profile.blade.php ENDPATH**/ ?>