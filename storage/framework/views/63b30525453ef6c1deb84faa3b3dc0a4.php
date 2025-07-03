<?php $__env->startSection('title', 'Tableau de Bord Branch Manager'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- En-tête avec informations de la branche -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="branch-logo me-4">
                            <i class="fas fa-building fa-3x text-success"></i>
                        </div>
                        <div>
                            <h2 class="mb-1"><?php echo e(auth()->user()->branch->name ?? 'Nom de la Branche'); ?></h2>
                            <p class="text-muted mb-0">
                                <i class="fas fa-map-marker-alt me-1"></i> 
                                <?php echo e(auth()->user()->branch->address ?? 'Adresse de la branche'); ?>, 
                                <?php echo e(auth()->user()->branch->city ?? 'Ville'); ?>

                            </p>
                        </div>
                        <div class="ms-auto text-end">
                            <span class="badge bg-success p-2">Actif</span>
                            <p class="text-muted mb-0 mt-1">Hôtel: <?php echo e(auth()->user()->branch->hotel->name ?? 'Nom de l\'Hôtel'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Clients</h6>
                            <h4 class="mb-0 fw-bold"><?php echo e(rand(40, 120)); ?></h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i> +<?php echo e(rand(3, 10)); ?> cette semaine
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-calendar-check fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Réservations</h6>
                            <h4 class="mb-0 fw-bold"><?php echo e(rand(15, 40)); ?></h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i> +<?php echo e(rand(1, 5)); ?> aujourd'hui
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-user-tie fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Personnel</h6>
                            <h4 class="mb-0 fw-bold"><?php echo e(rand(5, 15)); ?></h4>
                            <small class="text-muted">
                                <i class="fas fa-minus me-1"></i> Stable
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="fas fa-tasks fa-2x text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Tâches</h6>
                            <h4 class="mb-0 fw-bold"><?php echo e(rand(3, 12)); ?></h4>
                            <small class="text-danger">
                                <i class="fas fa-arrow-up me-1"></i> <?php echo e(rand(1, 3)); ?> en attente
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et tableaux -->
    <div class="row mb-4">
        <!-- Performance de la branche -->
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Performance de la Branche</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Ce mois
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Cette semaine</a></li>
                                <li><a class="dropdown-item" href="#">Ce mois</a></li>
                                <li><a class="dropdown-item" href="#">Ce trimestre</a></li>
                                <li><a class="dropdown-item" href="#">Cette année</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="branchPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières activités -->
        <div class="col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Activités Récentes</h5>
                        <a href="#" class="btn btn-sm btn-link text-success">Voir tout</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                                        <i class="fas fa-user-plus text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">Nouveau client <strong>Jean Dupont</strong> enregistré</p>
                                    <small class="text-muted">Il y a 10 minutes</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-info bg-opacity-10 p-2 rounded-circle">
                                        <i class="fas fa-calendar-check text-info"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">Réservation <strong>#12345</strong> confirmée</p>
                                    <small class="text-muted">Il y a 30 minutes</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-opacity-10 p-2 rounded-circle">
                                        <i class="fas fa-tasks text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">Nouvelle tâche assignée : <strong>Vérification des disponibilités</strong></p>
                                    <small class="text-muted">Il y a 2 heures</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                        <i class="fas fa-comment-alt text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">Nouveau message de <strong>Marie Martin</strong></p>
                                    <small class="text-muted">Il y a 3 heures</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Réservations et Personnel -->
    <div class="row">
        <!-- Réservations récentes -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Réservations Récentes</h5>
                        <a href="" class="btn btn-sm btn-success">
                            <i class="fas fa-eye me-1"></i> Voir tout
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Client</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Statut</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#12345</td>
                                    <td>Jean Dupont</td>
                                    <td><?php echo e(date('d/m/Y', strtotime('+2 days'))); ?></td>
                                    <td><span class="badge bg-success">Confirmée</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#12344</td>
                                    <td>Marie Martin</td>
                                    <td><?php echo e(date('d/m/Y', strtotime('+1 day'))); ?></td>
                                    <td><span class="badge bg-success">Confirmée</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#12343</td>
                                    <td>Pierre Dubois</td>
                                    <td><?php echo e(date('d/m/Y', strtotime('+3 days'))); ?></td>
                                    <td><span class="badge bg-warning">En attente</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>#12342</td>
                                    <td>Sophie Leroy</td>
                                    <td><?php echo e(date('d/m/Y', strtotime('+5 days'))); ?></td>
                                    <td><span class="badge bg-info">Nouvelle</span></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personnel de la branche -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Équipe</h5>
                        <a href="" class="btn btn-sm btn-success">
                            <i class="fas fa-users me-1"></i> Gérer
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Poste</th>
                                    <th scope="col">Contact</th>
                                    <th scope="col">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/35" class="rounded-circle me-2" alt="Avatar">
                                            <span>Thomas Bernard</span>
                                        </div>
                                    </td>
                                    <td>Réceptionniste</td>
                                    <td>thomas.bernard@example.com</td>
                                    <td><span class="badge bg-success">En service</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/35" class="rounded-circle me-2" alt="Avatar">
                                            <span>Julie Moreau</span>
                                        </div>
                                    </td>
                                    <td>Réceptionniste</td>
                                    <td>julie.moreau@example.com</td>
                                    <td><span class="badge bg-secondary">Repos</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/35" class="rounded-circle me-2" alt="Avatar">
                                            <span>Lucas Petit</span>
                                        </div>
                                    </td>
                                    <td>Agent de sécurité</td>
                                    <td>lucas.petit@example.com</td>
                                    <td><span class="badge bg-success">En service</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/35" class="rounded-circle me-2" alt="Avatar">
                                            <span>Emma Roux</span>
                                        </div>
                                    </td>
                                    <td>Service client</td>
                                    <td>emma.roux@example.com</td>
                                    <td><span class="badge bg-warning">Formation</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tâches et Services -->
    <div class="row">
        <!-- Tâches à faire -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Tâches à faire</h5>
                        <a href="#" class="btn btn-sm btn-success">
                            <i class="fas fa-plus me-1"></i> Ajouter
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="task1">
                                </div>
                                <label class="ms-2 form-check-label" for="task1">
                                    Vérifier les disponibilités pour le weekend
                                </label>
                            </div>
                            <span class="badge bg-danger rounded-pill">Urgent</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="task2">
                                </div>
                                <label class="ms-2 form-check-label" for="task2">
                                    Confirmer les réservations en attente
                                </label>
                            </div>
                            <span class="badge bg-warning rounded-pill">Important</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="task3">
                                </div>
                                <label class="ms-2 form-check-label" for="task3">
                                    Mettre à jour le planning du personnel
                                </label>
                            </div>
                            <span class="badge bg-info rounded-pill">Normal</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="task4">
                                </div>
                                <label class="ms-2 form-check-label" for="task4">
                                    Préparer le rapport hebdomadaire
                                </label>
                            </div>
                            <span class="badge bg-warning rounded-pill">Important</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Services populaires -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Services Populaires</h5>
                        <a href="" class="btn btn-sm btn-success">
                            <i class="fas fa-cog me-1"></i> Gérer
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="service-icon me-3">
                                            <i class="fas fa-wifi fa-2x text-success"></i>
                                        </div>
                                        <h6 class="mb-0">WiFi Premium</h6>
                                    </div>
                                    <p class="card-text small text-muted mb-2">Connexion haut débit pour tous les clients</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">Disponible</span>
                                        <small class="text-muted"><?php echo e(rand(20, 50)); ?> utilisations</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="service-icon me-3">
                                            <i class="fas fa-coffee fa-2x text-success"></i>
                                        </div>
                                        <h6 class="mb-0">Petit-déjeuner</h6>
                                    </div>
                                    <p class="card-text small text-muted mb-2">Service de petit-déjeuner continental</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">Disponible</span>
                                        <small class="text-muted"><?php echo e(rand(10, 30)); ?> réservations</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="service-icon me-3">
                                            <i class="fas fa-car fa-2x text-success"></i>
                                        </div>
                                        <h6 class="mb-0">Parking</h6>
                                    </div>
                                    <p class="card-text small text-muted mb-2">Places de parking sécurisées</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-warning">Limité</span>
                                        <small class="text-muted"><?php echo e(rand(5, 15)); ?> places restantes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="service-icon me-3">
                                            <i class="fas fa-concierge-bell fa-2x text-success"></i>
                                        </div>
                                        <h6 class="mb-0">Conciergerie</h6>
                                    </div>
                                    <p class="card-text small text-muted mb-2">Service personnalisé 24/7</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">Disponible</span>
                                        <small class="text-muted"><?php echo e(rand(5, 15)); ?> demandes</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique de performance de la branche
    const branchCtx = document.getElementById('branchPerformanceChart').getContext('2d');
    const branchChart = new Chart(branchCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil'],
            datasets: [
                {
                    label: 'Réservations',
                    data: [25, 30, 28, 35, 42, 38, 45],
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(16, 185, 129, 1)'
                },
                {
                    label: 'Clients',
                    data: [35, 40, 38, 45, 52, 48, 55],
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end'
                }
            }
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.branch_manager', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/branch/dashboard.blade.php ENDPATH**/ ?>