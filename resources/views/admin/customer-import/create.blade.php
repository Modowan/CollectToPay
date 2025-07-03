@extends('layouts.admin')

@section('title', 'Nouvelle Importation de Clients')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- En-tête de la page -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-upload text-primary me-2"></i>
                        Nouvelle Importation de Clients
                    </h1>
                    <p class="text-muted mt-1">Importez vos données clients à partir d'un fichier Excel ou CSV</p>
                </div>
                <a href="{{ route('admin.customer-import.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Retour à la Liste
                </a>
            </div>

            <!-- Guide d'importation -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-left-info shadow">
                        <div class="card-header bg-info text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-info-circle me-2"></i>
                                Guide d'Importation Multi-Tenant
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="font-weight-bold text-info">Formats Acceptés :</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success me-2"></i>Fichiers Excel (.xlsx, .xls)</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Fichiers CSV (.csv)</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Taille maximale : 10 MB</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="font-weight-bold text-info">Colonnes Requises :</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-star text-warning me-2"></i>Prénom (first_name)</li>
                                        <li><i class="fas fa-star text-warning me-2"></i>Nom (last_name)</li>
                                        <li><i class="fas fa-star text-warning me-2"></i>Email</li>
                                        <li><i class="fas fa-info-circle text-info me-2"></i>Téléphone (optionnel)</li>
                                    </ul>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="font-weight-bold text-info">Processus Multi-Tenant :</h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-hotel text-primary me-2"></i>Sélection de l'hôtel</li>
                                        <li><i class="fas fa-building text-primary me-2"></i>Choix de la succursale</li>
                                        <li><i class="fas fa-database text-primary me-2"></i>Import dans la base tenant</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-info btn-sm me-2" onclick="downloadExcelTemplate()">
                                    <i class="fas fa-download me-1"></i>
                                    Télécharger le Modèle Excel
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="downloadCSVTemplate()">
                                    <i class="fas fa-download me-1"></i>
                                    Télécharger le Modèle CSV
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaire d'importation -->
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-file-upload me-2"></i>
                        Formulaire d'Importation Multi-Tenant
                    </h6>
                </div>
                
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Veuillez corriger les erreurs suivantes :</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.customer-import.upload') }}" 
                          method="POST" 
                          enctype="multipart/form-data" 
                          id="importForm"
                          class="needs-validation" 
                          novalidate>
                        @csrf
                        
                        <!-- Section 1: Sélection Tenant/Hôtel -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-hotel text-primary me-2"></i>
                                    Étape 1: Sélection de l'Hôtel et de la Succursale
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Sélection de l'hôtel -->
                                    <div class="col-md-6 mb-3">
                                        <label for="tenant_id" class="form-label">
                                            <i class="fas fa-hotel me-2 text-primary"></i>
                                            Hôtel <span class="text-danger">*</span>
                                        </label>
                                        <select name="tenant_id" 
                                                id="tenant_id" 
                                                class="form-select @error('tenant_id') is-invalid @enderror" 
                                                required>
                                            <option value="">Sélectionnez un hôtel</option>
                                            @foreach($tenants ?? [] as $tenant)
                                                <option value="{{ $tenant->id }}" 
                                                        data-domain="{{ $tenant->domain }}"
                                                        data-database="{{ $tenant->database_name }}"
                                                        {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                                    {{ $tenant->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('tenant_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Choisissez l'hôtel pour lequel vous souhaitez importer les clients
                                        </div>
                                        <div id="tenantInfo" class="mt-2 d-none">
                                            <small class="text-muted">
                                                <strong>Domaine:</strong> <span id="tenantDomain"></span><br>
                                                <strong>Base de données:</strong> <span id="tenantDatabase"></span>
                                            </small>
                                        </div>
                                    </div>

                                    <!-- Sélection de la succursale -->
                                    <div class="col-md-6 mb-3">
                                        <label for="branch_id" class="form-label">
                                            <i class="fas fa-building me-2 text-primary"></i>
                                            Succursale <span class="text-danger">*</span>
                                        </label>
                                        <select name="branch_id" 
                                                id="branch_id" 
                                                class="form-select @error('branch_id') is-invalid @enderror" 
                                                required disabled>
                                            <option value="">Sélectionnez d'abord un hôtel</option>
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            La succursale sera automatiquement chargée selon l'hôtel sélectionné
                                        </div>
                                        <div id="branchInfo" class="mt-2 d-none">
                                            <small class="text-muted">
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                <span id="branchCount">0</span> succursale(s) disponible(s)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Configuration d'importation -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-cogs text-primary me-2"></i>
                                    Étape 2: Configuration de l'Importation
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Type d'importation -->
                                    <div class="col-md-6 mb-3">
                                        <label for="import_type" class="form-label">
                                            <i class="fas fa-cogs me-2 text-primary"></i>
                                            Type d'Importation <span class="text-danger">*</span>
                                        </label>
                                        <select name="import_type" 
                                                id="import_type" 
                                                class="form-select @error('import_type') is-invalid @enderror" 
                                                required>
                                            <option value="">Sélectionnez le type</option>
                                            <option value="new_customers" {{ old('import_type') == 'new_customers' ? 'selected' : '' }}>
                                                Nouveaux Clients Uniquement
                                            </option>
                                            <option value="update_existing" {{ old('import_type') == 'update_existing' ? 'selected' : '' }}>
                                                Mettre à Jour les Existants
                                            </option>
                                            <option value="mixed" {{ old('import_type') == 'mixed' ? 'selected' : '' }}>
                                                Mixte (Nouveau + Mise à Jour)
                                            </option>
                                        </select>
                                        @error('import_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Options d'importation -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-sliders-h me-2 text-primary"></i>
                                            Options d'Importation
                                        </label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="skip_duplicates" id="skip_duplicates" 
                                                   {{ old('skip_duplicates') ? 'checked' : 'checked' }}>
                                            <label class="form-check-label" for="skip_duplicates">
                                                Ignorer les doublons (basé sur l'email)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="send_notifications" id="send_notifications" 
                                                   {{ old('send_notifications') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="send_notifications">
                                                Envoyer des emails de création de mot de passe
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="validate_emails" id="validate_emails" 
                                                   {{ old('validate_emails') ? 'checked' : 'checked' }}>
                                            <label class="form-check-label" for="validate_emails">
                                                Valider les adresses email
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="use_tenant_connection" id="use_tenant_connection" 
                                                   checked disabled>
                                            <label class="form-check-label" for="use_tenant_connection">
                                                <i class="fas fa-database me-1"></i>
                                                Utiliser la connexion tenant (automatique)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Upload de fichier -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-file-upload text-primary me-2"></i>
                                    Étape 3: Sélection du Fichier
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Zone de téléchargement de fichier -->
                                <div class="mb-3">
                                    <label for="import_file" class="form-label">
                                        <i class="fas fa-file-upload me-2 text-primary"></i>
                                        Fichier à Importer <span class="text-danger">*</span>
                                    </label>
                                    <div class="upload-area border-2 border-dashed border-primary rounded p-4 text-center" 
                                         id="uploadArea"
                                         ondrop="dropHandler(event);" 
                                         ondragover="dragOverHandler(event);"
                                         ondragenter="dragEnterHandler(event);"
                                         ondragleave="dragLeaveHandler(event);">
                                        <div class="upload-content">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                            <h5 class="text-primary">Glissez-déposez votre fichier ici</h5>
                                            <p class="text-muted mb-3">ou cliquez pour sélectionner un fichier</p>
                                            <input type="file" 
                                                   name="import_file" 
                                                   id="import_file" 
                                                   class="form-control d-none @error('import_file') is-invalid @enderror" 
                                                   accept=".xlsx,.xls,.csv"
                                                   required>
                                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('import_file').click()">
                                                <i class="fas fa-folder-open me-2"></i>
                                                Parcourir les Fichiers
                                            </button>
                                        </div>
                                        <div class="file-info d-none" id="fileInfo">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="fas fa-file-excel fa-2x text-success me-3"></i>
                                                <div class="text-start">
                                                    <div class="fw-bold" id="fileName"></div>
                                                    <small class="text-muted" id="fileSize"></small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger ms-3" onclick="removeFile()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @error('import_file')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Format requis : first_name, last_name, email. Formats acceptés : CSV (.csv), Excel (.xlsx, .xls). Taille maximale : 10 MB
                                    </div>
                                </div>

                                <!-- Description optionnelle -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-comment me-2 text-primary"></i>
                                        Description (Optionnel)
                                    </label>
                                    <textarea name="description" 
                                              id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" 
                                              placeholder="Ajoutez une description pour cette importation...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Actions -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-play text-primary me-2"></i>
                                    Étape 4: Validation et Importation
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Boutons d'action -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="button" 
                                                id="previewBtn" 
                                                class="btn btn-info me-2" 
                                                disabled>
                                            <i class="fas fa-eye me-2"></i>
                                            <span class="btn-text">Aperçu</span>
                                            <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                                        </button>
                                        
                                        <button type="button" 
                                                id="uploadBtn" 
                                                class="btn btn-success" 
                                                disabled>
                                            <i class="fas fa-upload me-2"></i>
                                            <span class="btn-text">Importer</span>
                                            <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                                        </button>
                                    </div>
                                    
                                    <div class="text-muted">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            Utilisez l'aperçu pour vérifier vos données avant l'importation
                                        </small>
                                    </div>
                                </div>

                                <!-- Indicateur de progression -->
                                <div id="progressContainer" class="mt-3 d-none">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">Progression de l'importation</small>
                                        <small class="text-muted" id="progressText">0%</small>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" 
                                             id="progressBar"
                                             style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Zone de résultats -->
                    <div id="results" class="mt-4"></div>

                    <!-- Logs de debug -->
                    <div class="mt-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="m-0">
                                    <i class="fas fa-terminal me-2"></i>
                                    Logs de Debug Multi-Tenant
                                    <button type="button" class="btn btn-sm btn-outline-secondary float-end" onclick="clearLogs()">
                                        <i class="fas fa-trash me-1"></i>Effacer
                                    </button>
                                </h6>
                            </div>
                            <div class="card-body p-2">
                                <div id="debugLogs" style="max-height: 200px; overflow-y: auto; background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; font-family: monospace; font-size: 12px;">
                                    <div class="text-info">[INFO] Page chargée à {{ now()->toDateTimeString() }}</div>
                                    <div class="text-info">[INFO] Mode multi-tenant activé</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conseils et erreurs courantes -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-success">
                                <i class="fas fa-lightbulb me-2"></i>
                                Conseils pour une Importation Multi-Tenant Réussie
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <li><i class="fas fa-check text-success me-2"></i>Vérifiez que l'hôtel et la succursale sont corrects</li>
                                <li><i class="fas fa-check text-success me-2"></i>Assurez-vous que toutes les colonnes requises sont présentes</li>
                                <li><i class="fas fa-check text-success me-2"></i>Validez les emails avant l'importation</li>
                                <li><i class="fas fa-check text-success me-2"></i>Testez avec un petit échantillon d'abord</li>
                                <li><i class="fas fa-check text-success me-2"></i>Vérifiez les logs pour suivre le processus</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-left-warning">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Erreurs Courantes Multi-Tenant à Éviter
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <li><i class="fas fa-times text-danger me-2"></i>Oublier de sélectionner l'hôtel ou la succursale</li>
                                <li><i class="fas fa-times text-danger me-2"></i>Importer dans le mauvais tenant</li>
                                <li><i class="fas fa-times text-danger me-2"></i>Colonnes manquantes ou mal nommées</li>
                                <li><i class="fas fa-times text-danger me-2"></i>Emails en double entre différents tenants</li>
                                <li><i class="fas fa-times text-danger me-2"></i>Fichiers corrompus ou trop volumineux</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.upload-area {
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-area:hover {
    border-color: #0056b3 !important;
    background-color: rgba(37, 99, 235, 0.05);
}

.upload-area.dragover {
    border-color: #0056b3 !important;
    background-color: rgba(37, 99, 235, 0.1);
    transform: scale(1.02);
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.form-check-input:checked {
    background-color: #2563eb;
    border-color: #2563eb;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.card-header.bg-light {
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #dee2e6;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

#tenantInfo, #branchInfo {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Configuration globale
    const DEBUG = true;
    const CSRF_TOKEN = '{{ csrf_token() }}';
    
    // Fonction de log améliorée
    function debugLog(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const logElement = $('#debugLogs');
        const colorClass = type === 'error' ? 'text-danger' : type === 'success' ? 'text-success' : type === 'warning' ? 'text-warning' : 'text-info';
        const icon = type === 'error' ? 'fas fa-exclamation-circle' : type === 'success' ? 'fas fa-check-circle' : type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
        
        logElement.append(`<div class="${colorClass}"><i class="${icon} me-1"></i>[${timestamp}] ${message}</div>`);
        logElement.scrollTop(logElement[0].scrollHeight);
        
        if (DEBUG) {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    // Configuration AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    });

    debugLog('jQuery chargé et CSRF configuré');
    debugLog('Mode multi-tenant activé');

    // Gestion du changement d'hôtel pour charger les succursales
    $('#tenant_id').on('change', function() {
        const tenantId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        const branchSelect = $('#branch_id');
        
        debugLog(`Changement d'hôtel: ${tenantId}`, 'info');
        
        // Affichage des informations du tenant
        if (tenantId) {
            const domain = selectedOption.data('domain');
            const database = selectedOption.data('database');
            
            $('#tenantDomain').text(domain);
            $('#tenantDatabase').text(database);
            $('#tenantInfo').removeClass('d-none');
            
            debugLog(`Tenant sélectionné: ${selectedOption.text()}`, 'info');
            debugLog(`Domaine: ${domain}`, 'info');
            debugLog(`Base de données: ${database}`, 'info');
        } else {
            $('#tenantInfo').addClass('d-none');
            $('#branchInfo').addClass('d-none');
        }
        
        // Réinitialisation et chargement des branches
        branchSelect.html('<option value="">Chargement...</option>').prop('disabled', true);
        
        if (tenantId) {
            debugLog('Chargement des succursales...', 'info');
            
            $.ajax({
                url: '{{ route("admin.customer-import.branches") }}',
                method: 'GET',
                data: { tenant_id: tenantId },
                success: function(response) {
                    debugLog(`Réponse reçue pour les succursales`, 'success');
                    
                    branchSelect.html('<option value="">Sélectionnez une succursale</option>').prop('disabled', false);
                    
                    if (response.success && response.branches && response.branches.length > 0) {
                        response.branches.forEach(function(branch) {
                            branchSelect.append(`<option value="${branch.id}">${branch.name}</option>`);
                        });
                        
                        $('#branchCount').text(response.branches.length);
                        $('#branchInfo').removeClass('d-none');
                        
                        debugLog(`${response.branches.length} succursale(s) chargée(s)`, 'success');
                    } else {
                        debugLog('Aucune succursale trouvée', 'warning');
                        branchSelect.append('<option value="">Aucune succursale disponible</option>');
                        $('#branchInfo').addClass('d-none');
                    }
                },
                error: function(xhr, status, error) {
                    debugLog(`Erreur chargement succursales: ${error}`, 'error');
                    debugLog(`Status: ${status}, Response: ${xhr.responseText}`, 'error');
                    
                    branchSelect.html('<option value="">Erreur de chargement</option>').prop('disabled', false);
                    $('#branchInfo').addClass('d-none');
                }
            });
        } else {
            branchSelect.html('<option value="">Sélectionnez d\'abord un hôtel</option>').prop('disabled', true);
        }
        
        checkFormValidity();
    });

    // Gestion du changement de succursale
    $('#branch_id').on('change', function() {
        const branchId = $(this).val();
        const branchName = $(this).find('option:selected').text();
        
        if (branchId) {
            debugLog(`Succursale sélectionnée: ${branchName} (ID: ${branchId})`, 'info');
        }
        
        checkFormValidity();
    });

    // Gestion du fichier
    $('#import_file').on('change', function() {
        handleFileSelect(this.files[0]);
    });

    // Validation du formulaire
    function checkFormValidity() {
        const tenantId = $('#tenant_id').val();
        const branchId = $('#branch_id').val();
        const importType = $('#import_type').val();
        const file = $('#import_file')[0].files[0];
        
        const isValid = tenantId && branchId && importType && file;
        
        $('#previewBtn, #uploadBtn').prop('disabled', !isValid);
        
        debugLog(`Validation formulaire: Tenant=${tenantId}, Branch=${branchId}, Type=${importType}, File=${file ? file.name : 'None'}, Valid=${isValid}`, isValid ? 'success' : 'warning');
        
        return isValid;
    }

    // Écouter les changements sur tous les champs requis
    $('#tenant_id, #branch_id, #import_type').on('change', checkFormValidity);

    // Gestion du fichier
    function handleFileSelect(file) {
        if (file) {
            debugLog(`Fichier sélectionné: ${file.name} (${formatFileSize(file.size)})`, 'info');
            
            // Validation du fichier
            const allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
            const maxSize = 10 * 1024 * 1024; // 10 MB
            
            if (!allowedTypes.includes(file.type) && !file.name.toLowerCase().endsWith('.csv')) {
                debugLog(`Type de fichier non supporté: ${file.type}`, 'error');
                alert('Type de fichier non supporté. Veuillez sélectionner un fichier Excel (.xlsx, .xls) ou CSV (.csv)');
                $('#import_file').val('');
                return;
            }
            
            if (file.size > maxSize) {
                debugLog(`Fichier trop volumineux: ${formatFileSize(file.size)}`, 'error');
                alert('Le fichier est trop volumineux. Taille maximale autorisée : 10 MB');
                $('#import_file').val('');
                return;
            }
            
            // Affichage des informations du fichier
            $('#fileName').text(file.name);
            $('#fileSize').text(formatFileSize(file.size));
            $('.upload-content').addClass('d-none');
            $('#fileInfo').removeClass('d-none');
            
            debugLog(`Fichier validé avec succès`, 'success');
        }
        
        checkFormValidity();
    }

    // Fonction pour formater la taille du fichier
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Fonction pour supprimer le fichier
    window.removeFile = function() {
        $('#import_file').val('');
        $('.upload-content').removeClass('d-none');
        $('#fileInfo').addClass('d-none');
        debugLog('Fichier supprimé', 'info');
        checkFormValidity();
    };

    // Gestion du drag & drop
    window.dragOverHandler = function(ev) {
        ev.preventDefault();
        $('#uploadArea').addClass('dragover');
    };

    window.dragEnterHandler = function(ev) {
        ev.preventDefault();
        $('#uploadArea').addClass('dragover');
    };

    window.dragLeaveHandler = function(ev) {
        ev.preventDefault();
        $('#uploadArea').removeClass('dragover');
    };

    window.dropHandler = function(ev) {
        ev.preventDefault();
        $('#uploadArea').removeClass('dragover');
        
        const files = ev.dataTransfer.files;
        if (files.length > 0) {
            $('#import_file')[0].files = files;
            handleFileSelect(files[0]);
        }
    };

    // BOUTON APERÇU - VERSION CORRIGÉE MULTI-TENANT
    $('#previewBtn').on('click', function() {
        debugLog('=== DÉBUT APERÇU MULTI-TENANT ===', 'info');
        
        if (!checkFormValidity()) {
            debugLog('ERREUR: Formulaire invalide', 'error');
            alert('Veuillez remplir tous les champs requis');
            return;
        }
        
        const tenantId = $('#tenant_id').val();
        const branchId = $('#branch_id').val();
        const fileInput = $('#import_file')[0];
        
        debugLog(`Tenant ID: ${tenantId}`, 'info');
        debugLog(`Branch ID: ${branchId}`, 'info');
        debugLog(`Fichier: ${fileInput.files[0].name}`, 'info');
        
        // Préparation des données
        const formData = new FormData();
        formData.append('tenant_id', tenantId);
        formData.append('branch_id', branchId);
        formData.append('import_type', $('#import_type').val());
        formData.append('import_file', fileInput.files[0]);
        formData.append('skip_duplicates', $('#skip_duplicates').is(':checked') ? '1' : '0');
        formData.append('validate_emails', $('#validate_emails').is(':checked') ? '1' : '0');
        formData.append('_token', CSRF_TOKEN);
        
        const btn = $(this);
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        
        // Requête AJAX
        $.ajax({
            url: '{{ route('admin.customer-import.preview') }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                debugLog('Envoi de la requête d\'aperçu...', 'info');
                btn.prop('disabled', true);
                btnText.text('Chargement...');
                spinner.removeClass('d-none');
            },
            success: function(response) {
                debugLog('=== APERÇU RÉUSSI ===', 'success');
                debugLog(`Clients valides: ${response.summary ? response.summary.valid_count : 'N/A'}`, 'success');
                
                // Affichage des résultats
                let html = '<div class="alert alert-success"><h5><i class="fas fa-check-circle me-2"></i>Aperçu généré avec succès !</h5>';
                
                if (response.summary) {
                    html += `<div class="row mt-3">`;
                    html += `<div class="col-md-3"><div class="text-center"><h4 class="text-primary">${response.summary.total_rows}</h4><small>Total lignes</small></div></div>`;
                    html += `<div class="col-md-3"><div class="text-center"><h4 class="text-success">${response.summary.valid_count}</h4><small>Valides</small></div></div>`;
                    html += `<div class="col-md-3"><div class="text-center"><h4 class="text-danger">${response.summary.error_count}</h4><small>Erreurs</small></div></div>`;
                    html += `<div class="col-md-3"><div class="text-center"><h4 class="text-info">${response.summary.estimated_import}</h4><small>À importer</small></div></div>`;
                    html += `</div>`;
                }
                
                // Informations du tenant
                if (response.tenant_info) {
                    html += `<div class="mt-3 p-3 bg-light rounded">`;
                    html += `<h6><i class="fas fa-database me-2"></i>Informations de destination</h6>`;
                    html += `<div class="row">`;
                    html += `<div class="col-md-6"><small><strong>Hôtel:</strong> ${response.tenant_info.tenant_name}</small></div>`;
                    html += `<div class="col-md-6"><small><strong>Succursale:</strong> ${response.tenant_info.branch_name}</small></div>`;
                    html += `<div class="col-md-6"><small><strong>Base de données:</strong> ${response.tenant_info.database_name}</small></div>`;
                    html += `<div class="col-md-6"><small><strong>Connexion:</strong> ${response.tenant_info.connection_status}</small></div>`;
                    html += `</div></div>`;
                }
                
                if (response.preview_data && response.preview_data.length > 0) {
                    html += '<h6 class="mt-4">Aperçu des données :</h6>';
                    html += '<div class="table-responsive">';
                    html += '<table class="table table-sm table-striped">';
                    html += '<thead class="table-dark"><tr><th>Ligne</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Statut</th></tr></thead>';
                    html += '<tbody>';
                    
                    response.preview_data.forEach(function(row) {
                        const statusClass = row.status === 'valid' ? 'text-success' : 'text-danger';
                        const statusIcon = row.status === 'valid' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
                        const statusText = row.status === 'valid' ? 'Valide' : 'Erreur';
                        
                        html += `<tr>`;
                        html += `<td>${row.row_number}</td>`;
                        html += `<td>${row.data.first_name || '-'}</td>`;
                        html += `<td>${row.data.last_name || '-'}</td>`;
                        html += `<td>${row.data.email || '-'}</td>`;
                        html += `<td class="${statusClass}"><i class="${statusIcon} me-1"></i>${statusText}</td>`;
                        html += `</tr>`;
                        
                        if (row.errors && row.errors.length > 0) {
                            html += `<tr><td colspan="5" class="text-danger small">Erreurs: ${row.errors.join(', ')}</td></tr>`;
                        }
                    });
                    
                    html += '</tbody></table>';
                    html += '</div>';
                }
                
                html += '</div>';
                $('#results').html(html);
            },
            error: function(xhr, status, error) {
                debugLog('=== ERREUR APERÇU ===', 'error');
                debugLog(`Erreur: ${error}`, 'error');
                debugLog(`Réponse: ${xhr.responseText}`, 'error');
                
                $('#results').html(`
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-circle me-2"></i>Erreur lors de l'aperçu</h5>
                        <p><strong>Erreur :</strong> ${error}</p>
                        <p><strong>Statut :</strong> ${xhr.status}</p>
                        <details class="mt-2">
                            <summary>Détails de l'erreur</summary>
                            <pre class="mt-2">${xhr.responseText}</pre>
                        </details>
                    </div>
                `);
            },
            complete: function() {
                btn.prop('disabled', false);
                btnText.text('Aperçu');
                spinner.addClass('d-none');
                debugLog('=== FIN APERÇU ===', 'info');
                checkFormValidity();
            }
        });
    });

    // BOUTON UPLOAD - VERSION CORRIGÉE MULTI-TENANT
    $('#uploadBtn').on('click', function() {
        debugLog('=== DÉBUT UPLOAD MULTI-TENANT ===', 'info');
        
        if (!checkFormValidity()) {
            debugLog('ERREUR: Formulaire invalide', 'error');
            alert('Veuillez remplir tous les champs requis');
            return;
        }
        
        if (!confirm('Êtes-vous sûr de vouloir importer ces données ? Cette action ne peut pas être annulée.')) {
            debugLog('Upload annulé par l\'utilisateur', 'warning');
            return;
        }
        
        debugLog('Validation réussie, envoi de l\'upload...', 'info');
        
        // Préparation des données
        const formData = new FormData($('#importForm')[0]);
        
        const btn = $(this);
        const btnText = btn.find('.btn-text');
        const spinner = btn.find('.spinner-border');
        const progressContainer = $('#progressContainer');
        const progressBar = $('#progressBar');
        const progressText = $('#progressText');
        
        // Requête AJAX pour upload
        $.ajax({
            url: '{{ route('admin.customer-import.upload') }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                debugLog('Envoi de la requête d\'upload...', 'info');
                btn.prop('disabled', true);
                btnText.text('Importation...');
                spinner.removeClass('d-none');
                progressContainer.removeClass('d-none');
                progressBar.css('width', '0%');
                progressText.text('0%');
            },
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        progressBar.css('width', percentComplete + '%');
                        progressText.text(percentComplete + '%');
                        debugLog(`Progression upload: ${percentComplete}%`, 'info');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                debugLog('=== UPLOAD RÉUSSI ===', 'success');
                debugLog(`Clients importés: ${response.summary ? response.summary.imported_count : 'N/A'}`, 'success');
                
                progressBar.css('width', '100%');
                progressText.text('100%');
                
                // Affichage des résultats
                let html = '<div class="alert alert-success"><h5><i class="fas fa-check-circle me-2"></i>Importation terminée avec succès !</h5>';
                
                if (response.summary) {
                    html += `<div class="row mt-3">`;
                    html += `<div class="col-md-3"><div class="text-center"><h4 class="text-primary">${response.summary.total_processed}</h4><small>Total traité</small></div></div>`;
                    html += `<div class="col-md-3"><div class="text-center"><h4 class="text-success">${response.summary.imported_count}</h4><small>Importés</small></div></div>`;
                    html += `<div class="col-md-3"><div class="text-center"><h4 class="text-warning">${response.summary.updated_count}</h4><small>Mis à jour</small></div></div>`;
                    html += `<div class="col-md-3"><div class="text-center"><h4 class="text-danger">${response.summary.failed_count}</h4><small>Échecs</small></div></div>`;
                    html += `</div>`;
                }
                
                if (response.import_id) {
                    html += `<div class="mt-3">`;
                    html += `<a href="{{ route('admin.customer-import.show', '') }}/${response.import_id}" class="btn btn-primary btn-sm me-2">`;
                    html += `<i class="fas fa-eye me-1"></i>Voir les détails`;
                    html += `</a>`;
                    html += `<a href="{{ route('admin.customer-import.index') }}" class="btn btn-secondary btn-sm">`;
                    html += `<i class="fas fa-list me-1"></i>Retour à la liste`;
                    html += `</a>`;
                    html += `</div>`;
                }
                
                html += '</div>';
                $('#results').html(html);
                
                // Réinitialisation du formulaire
                setTimeout(function() {
                    if (confirm('Voulez-vous effectuer une nouvelle importation ?')) {
                        location.reload();
                    }
                }, 3000);
            },
            error: function(xhr, status, error) {
                debugLog('=== ERREUR UPLOAD ===', 'error');
                debugLog(`Erreur: ${error}`, 'error');
                debugLog(`Réponse: ${xhr.responseText}`, 'error');
                
                $('#results').html(`
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-circle me-2"></i>Erreur lors de l'importation</h5>
                        <p><strong>Erreur :</strong> ${error}</p>
                        <p><strong>Statut :</strong> ${xhr.status}</p>
                        <details class="mt-2">
                            <summary>Détails de l'erreur</summary>
                            <pre class="mt-2">${xhr.responseText}</pre>
                        </details>
                    </div>
                `);
            },
            complete: function() {
                btn.prop('disabled', false);
                btnText.text('Importer');
                spinner.addClass('d-none');
                setTimeout(function() {
                    progressContainer.addClass('d-none');
                }, 2000);
                debugLog('=== FIN UPLOAD ===', 'info');
                checkFormValidity();
            }
        });
    });

    // Fonction pour effacer les logs
    window.clearLogs = function() {
        $('#debugLogs').html('<div class="text-info">[INFO] Logs effacés</div>');
        debugLog('Logs effacés', 'info');
    };

    // Fonctions de téléchargement de modèles
    window.downloadExcelTemplate = function() {
        debugLog('Téléchargement du modèle Excel', 'info');
        
        // Création d'un fichier Excel simple
        const csvContent = "first_name,last_name,email,phone,address,city,country,postal_code,date_of_birth,gender,nationality\n" +
                          "Jean,Dupont,jean.dupont@example.com,+33123456789,123 Rue de la Paix,Paris,France,75001,1985-03-15,male,Française\n" +
                          "Marie,Martin,marie.martin@example.com,+33987654321,456 Avenue des Champs,Lyon,France,69001,1990-07-22,female,Française";
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'modele_import_clients.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        debugLog('Modèle Excel téléchargé', 'success');
    };

    window.downloadCSVTemplate = function() {
        debugLog('Téléchargement du modèle CSV', 'info');
        
        // Création d'un fichier CSV
        const csvContent = "first_name,last_name,email,phone,address,city,country,postal_code,date_of_birth,gender,nationality\n" +
                          "Jean,Dupont,jean.dupont@example.com,+33123456789,123 Rue de la Paix,Paris,France,75001,1985-03-15,male,Française\n" +
                          "Marie,Martin,marie.martin@example.com,+33987654321,456 Avenue des Champs,Lyon,France,69001,1990-07-22,female,Française\n" +
                          "Ahmed,Hassan,ahmed.hassan@example.com,+33456789123,789 Boulevard Saint-Germain,Marseille,France,13001,1988-12-10,male,Française";
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'modele_import_clients.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        debugLog('Modèle CSV téléchargé', 'success');
    };

    // Validation initiale
    checkFormValidity();
});
</script>
@endpush

