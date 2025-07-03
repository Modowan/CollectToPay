<?php $__env->startSection('title', 'Historique des Importations'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- En-tête de la page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-history text-primary me-2"></i>
                Historique des Importations
            </h1>
            <p class="text-muted mt-1">Consultez et gérez toutes vos importations de clients</p>
        </div>
        <a href="<?php echo e(route('admin.customer-import.create')); ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i>
            Nouvelle Importation
        </a>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Importations
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">47</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-import fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Clients Importés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">2,847</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Ce Mois
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">8</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Taux de Succès
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">94%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Recherche -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>
                Filtres et Recherche
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.customer-import.index')); ?>">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Nom de fichier, hôtel..." value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hotel" class="form-label">Hôtel</label>
                        <select class="form-select" id="hotel" name="hotel">
                            <option value="">Tous les hôtels</option>
                            <option value="hilton" <?php echo e(request('hotel') == 'hilton' ? 'selected' : ''); ?>>Hôtel Hilton Paris</option>
                            <option value="marriott" <?php echo e(request('hotel') == 'marriott' ? 'selected' : ''); ?>>Hôtel Marriott Lyon</option>
                            <option value="accor" <?php echo e(request('hotel') == 'accor' ? 'selected' : ''); ?>>Hôtel Accor Nice</option>
                            <option value="sofitel" <?php echo e(request('hotel') == 'sofitel' ? 'selected' : ''); ?>>Hôtel Sofitel Marseille</option>
                            <option value="ibis" <?php echo e(request('hotel') == 'ibis' ? 'selected' : ''); ?>>Hôtel Ibis Toulouse</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tous les statuts</option>
                            <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Terminé</option>
                            <option value="processing" <?php echo e(request('status') == 'processing' ? 'selected' : ''); ?>>En cours</option>
                            <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Échoué</option>
                            <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>En attente</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="date_range" class="form-label">Période</label>
                        <select class="form-select" id="date_range" name="date_range">
                            <option value="">Toutes les dates</option>
                            <option value="today" <?php echo e(request('date_range') == 'today' ? 'selected' : ''); ?>>Aujourd'hui</option>
                            <option value="week" <?php echo e(request('date_range') == 'week' ? 'selected' : ''); ?>>Cette semaine</option>
                            <option value="month" <?php echo e(request('date_range') == 'month' ? 'selected' : ''); ?>>Ce mois</option>
                            <option value="quarter" <?php echo e(request('date_range') == 'quarter' ? 'selected' : ''); ?>>Ce trimestre</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>
                            Rechercher
                        </button>
                        <a href="<?php echo e(route('admin.customer-import.index')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des Importations -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>
                Liste des Importations
            </h6>
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-1"></i>
                    Exporter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="importsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Fichier</th>
                            <th>Hôtel</th>
                            <th>Succursale</th>
                            <th>Clients</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Import 1 -->
                        <tr>
                            <td><span class="badge bg-primary">#001</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-excel text-success me-2"></i>
                                    <div>
                                        <div class="fw-bold">clients_hilton_2024.xlsx</div>
                                        <small class="text-muted">2.3 MB</small>
                                    </div>
                                </div>
                            </td>
                            <td>Hôtel Hilton Paris</td>
                            <td>Succursale Centre</td>
                            <td>
                                <div class="text-center">
                                    <div class="fw-bold text-success">150</div>
                                    <small class="text-muted">importés</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>
                                    Terminé
                                </span>
                            </td>
                            <td>
                                <div>08/06/2024</div>
                                <small class="text-muted">14:30</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?php echo e(route('admin.customer-import.show', 1)); ?>" class="btn btn-outline-primary btn-sm" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-success btn-sm" title="Télécharger le rapport">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Import 2 -->
                        <tr>
                            <td><span class="badge bg-primary">#002</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-csv text-info me-2"></i>
                                    <div>
                                        <div class="fw-bold">nouveaux_clients.csv</div>
                                        <small class="text-muted">1.8 MB</small>
                                    </div>
                                </div>
                            </td>
                            <td>Hôtel Marriott Lyon</td>
                            <td>Succursale Nord</td>
                            <td>
                                <div class="text-center">
                                    <div class="fw-bold text-warning">89/95</div>
                                    <small class="text-muted">6 erreurs</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Avec erreurs
                                </span>
                            </td>
                            <td>
                                <div>08/06/2024</div>
                                <small class="text-muted">12:15</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?php echo e(route('admin.customer-import.show', 2)); ?>" class="btn btn-outline-primary btn-sm" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-success btn-sm" title="Télécharger le rapport">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Import 3 -->
                        <tr>
                            <td><span class="badge bg-primary">#003</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-excel text-success me-2"></i>
                                    <div>
                                        <div class="fw-bold">import_accor.xlsx</div>
                                        <small class="text-muted">3.1 MB</small>
                                    </div>
                                </div>
                            </td>
                            <td>Hôtel Accor Nice</td>
                            <td>Succursale Sud</td>
                            <td>
                                <div class="text-center">
                                    <div class="fw-bold text-primary">
                                        <div class="spinner-border spinner-border-sm me-1"></div>
                                        En cours...
                                    </div>
                                    <small class="text-muted">45/200</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary">
                                    <i class="fas fa-spinner fa-spin me-1"></i>
                                    En cours
                                </span>
                            </td>
                            <td>
                                <div>08/06/2024</div>
                                <small class="text-muted">13:45</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?php echo e(route('admin.customer-import.show', 3)); ?>" class="btn btn-outline-primary btn-sm" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-warning btn-sm" title="Annuler">
                                        <i class="fas fa-stop"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Import 4 -->
                        <tr>
                            <td><span class="badge bg-primary">#004</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-csv text-info me-2"></i>
                                    <div>
                                        <div class="fw-bold">clients_invalides.csv</div>
                                        <small class="text-muted">0.5 MB</small>
                                    </div>
                                </div>
                            </td>
                            <td>Hôtel Sofitel Marseille</td>
                            <td>Succursale Est</td>
                            <td>
                                <div class="text-center">
                                    <div class="fw-bold text-danger">0</div>
                                    <small class="text-muted">échec total</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger">
                                    <i class="fas fa-times me-1"></i>
                                    Échoué
                                </span>
                            </td>
                            <td>
                                <div>07/06/2024</div>
                                <small class="text-muted">16:20</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?php echo e(route('admin.customer-import.show', 4)); ?>" class="btn btn-outline-primary btn-sm" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-info btn-sm" title="Réessayer">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Affichage de 1 à 4 sur 47 importations
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item disabled">
                            <span class="page-link">Précédent</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">2</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">3</a>
                        </li>
                        <li class="page-item">
                            <span class="page-link">...</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">12</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#">Suivant</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
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
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #5a5c69;
        background-color: #f8f9fc;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        border-radius: 0.25rem;
        margin-right: 2px;
    }
    
    .badge {
        font-size: 0.75rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Auto-refresh pour les imports en cours
    setInterval(function() {
        const processingRows = document.querySelectorAll('tbody tr');
        processingRows.forEach(row => {
            const statusBadge = row.querySelector('.badge.bg-primary');
            if (statusBadge && statusBadge.textContent.includes('En cours')) {
                // Ici vous pourriez faire un appel AJAX pour mettre à jour le statut
                console.log('Vérification du statut de l\'import en cours...');
            }
        });
    }, 30000); // Vérification toutes les 30 secondes

    // Confirmation de suppression
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-outline-danger') && e.target.closest('.btn-outline-danger').title === 'Supprimer') {
            e.preventDefault();
            if (confirm('Êtes-vous sûr de vouloir supprimer cette importation ?')) {
                // Ici vous pourriez faire l'appel de suppression
                console.log('Suppression confirmée');
            }
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/admin/customer-import/index.blade.php ENDPATH**/ ?>