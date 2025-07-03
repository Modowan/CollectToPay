

<?php $__env->startSection('title', 'Tableau de Bord Administrateur'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- En-tête du Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-tachometer-alt text-primary me-2"></i>
                Tableau de Bord Administrateur
            </h1>
            <p class="text-muted mt-1">Bienvenue dans votre interface d'administration CollectToPay</p>
        </div>
        <div class="text-end">
            <small class="text-muted">Dernière connexion : <?php echo e(auth()->user()->last_login_at ?? 'Première connexion'); ?></small>
        </div>
    </div>

    <!-- Cartes de Statistiques -->
    <div class="row mb-4">
        <!-- Total Clients -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Clients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">1,247</div>
                            <div class="text-xs text-success mt-1">
                                <i class="fas fa-arrow-up me-1"></i>
                                +12% ce mois
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Importations ce Mois -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Importations ce Mois
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">23</div>
                            <div class="text-xs text-success mt-1">
                                <i class="fas fa-arrow-up me-1"></i>
                                +8 cette semaine
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-upload fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hôtels Actifs -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Hôtels Actifs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">5</div>
                            <div class="text-xs text-info mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Tous opérationnels
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hotel fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emails Envoyés -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Emails Envoyés
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">892</div>
                            <div class="text-xs text-warning mt-1">
                                <i class="fas fa-envelope me-1"></i>
                                Cette semaine
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-bolt me-2"></i>
                        Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Importer des Clients -->
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="<?php echo e(route('admin.customer-import.create')); ?>" class="btn btn-primary btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none">
                                <i class="fas fa-upload fa-2x mb-2"></i>
                                <span class="fw-bold">Importer des Clients</span>
                                <small class="opacity-75">Fichier Excel/CSV</small>
                            </a>
                        </div>

                        <!-- Voir les Importations -->
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="<?php echo e(route('admin.customer-import.index')); ?>" class="btn btn-outline-primary btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none">
                                <i class="fas fa-list fa-2x mb-2"></i>
                                <span class="fw-bold">Historique Imports</span>
                                <small class="opacity-75">Voir tous les imports</small>
                            </a>
                        </div>

                        <!-- Gérer les Hôtels -->
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="<?php echo e(route('admin.hotels.index')); ?>" class="btn btn-outline-success btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none">
                                <i class="fas fa-hotel fa-2x mb-2"></i>
                                <span class="fw-bold">Gérer les Hôtels</span>
                                <small class="opacity-75">Configuration hôtels</small>
                            </a>
                        </div>

                        <!-- Paramètres -->
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="<?php echo e(route('admin.settings.general')); ?>" class="btn btn-outline-secondary btn-lg w-100 h-100 d-flex flex-column align-items-center justify-content-center text-decoration-none">
                                <i class="fas fa-cogs fa-2x mb-2"></i>
                                <span class="fw-bold">Paramètres</span>
                                <small class="opacity-75">Configuration système</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et Activité Récente -->
    <div class="row">
        <!-- Graphique des Importations -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-chart-area me-2"></i>
                        Évolution des Importations
                    </h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <div class="dropdown-header">Actions :</div>
                            <a class="dropdown-item" href="#">Exporter les données</a>
                            <a class="dropdown-item" href="#">Voir le rapport détaillé</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="importChart" width="100%" height="40"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activité Récente -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-clock me-2"></i>
                        Activité Récente
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Activité 1 -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="fas fa-upload text-white fa-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">Importation réussie</div>
                                    <div class="text-muted small">150 clients importés pour Hôtel Hilton Paris</div>
                                    <div class="text-muted small">Il y a 5 minutes</div>
                                </div>
                            </div>
                        </div>

                        <!-- Activité 2 -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="fas fa-user text-white fa-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">Nouveau client ajouté</div>
                                    <div class="text-muted small">Jean Dupont - Hôtel Marriott Lyon</div>
                                    <div class="text-muted small">Il y a 1 heure</div>
                                </div>
                            </div>
                        </div>

                        <!-- Activité 3 -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="fas fa-exclamation-triangle text-white fa-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">Erreur d'importation</div>
                                    <div class="text-muted small">5 lignes rejetées - emails invalides</div>
                                    <div class="text-muted small">Il y a 2 heures</div>
                                </div>
                            </div>
                        </div>

                        <!-- Activité 4 -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="fas fa-cog text-white fa-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold">Configuration mise à jour</div>
                                    <div class="text-muted small">Paramètres email modifiés</div>
                                    <div class="text-muted small">Il y a 3 heures</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <a href="<?php echo e(route('admin.logs')); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-history me-1"></i>
                            Voir tous les logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statut du Système -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-white">
                        <i class="fas fa-server me-2"></i>
                        Statut du Système
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Base de Données -->
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-database text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Base de Données</div>
                                    <div class="text-success small">Opérationnelle</div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Email -->
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-envelope text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Service Email</div>
                                    <div class="text-success small">Opérationnel</div>
                                </div>
                            </div>
                        </div>

                        <!-- Stockage -->
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-hdd text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Stockage</div>
                                    <div class="text-warning small">75% utilisé</div>
                                </div>
                            </div>
                        </div>

                        <!-- Sauvegardes -->
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-info rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-shield-alt text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">Sauvegardes</div>
                                    <div class="text-info small">Dernière : Hier</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    
    .timeline-item {
        position: relative;
    }
    
    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 17px;
        top: 45px;
        width: 2px;
        height: calc(100% - 10px);
        background: #e3e6f0;
    }
    
    .chart-area {
        position: relative;
        height: 300px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des importations
    const ctx = document.getElementById('importChart').getContext('2d');
    const importChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Importations',
                data: [12, 19, 15, 25, 22, 23],
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e3e6f0'
                    }
                },
                x: {
                    grid: {
                        color: '#e3e6f0'
                    }
                }
            }
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>