<?php $__env->startSection('title', 'Tableau de Bord Hotel Manager'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- En-tête avec informations de l'hôtel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="hotel-logo me-4">
                            <i class="fas fa-hotel fa-3x text-primary"></i>
                        </div>
                        <div>
                            <h2 class="mb-1"><?php echo e(auth()->user()->hotel->name ?? 'Nom de l\'Hôtel'); ?></h2>
                            <p class="text-muted mb-0">
                                <i class="fas fa-map-marker-alt me-1"></i> 
                                <?php echo e(auth()->user()->hotel->address ?? 'Adresse de l\'hôtel'); ?>, 
                                <?php echo e(auth()->user()->hotel->city ?? 'Ville'); ?>

                            </p>
                        </div>
                        <div class="ms-auto text-end">
                            <span class="badge bg-success p-2">Actif</span>
                            <p class="text-muted mb-0 mt-1">ID: <?php echo e(auth()->user()->hotel->id ?? '1'); ?></p>
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
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-building fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Branches</h6>
                            <h4 class="mb-0 fw-bold"><?php echo e(rand(3, 8)); ?></h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i> +1 ce mois
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
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Clients</h6>
                            <h4 class="mb-0 fw-bold"><?php echo e(rand(120, 350)); ?></h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i> +<?php echo e(rand(5, 20)); ?> cette semaine
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
                            <h4 class="mb-0 fw-bold"><?php echo e(rand(30, 80)); ?></h4>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i> +<?php echo e(rand(3, 12)); ?> aujourd'hui
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
                            <h4 class="mb-0 fw-bold"><?php echo e(rand(15, 40)); ?></h4>
                            <small class="text-muted">
                                <i class="fas fa-minus me-1"></i> Stable
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et tableaux -->
    <div class="row mb-4">
        <!-- Performance par branche -->
        <div class="col-xl-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Performance par Branche</h5>
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
                        <a href="#" class="btn btn-sm btn-link text-primary">Voir tout</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                        <i class="fas fa-user-plus text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">Nouveau client enregistré à <strong>Paris Centre</strong></p>
                                    <small class="text-muted">Il y a 10 minutes</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                                        <i class="fas fa-calendar-check text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">Nouvelle réservation confirmée à <strong>Aéroport</strong></p>
                                    <small class="text-muted">Il y a 30 minutes</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-opacity-10 p-2 rounded-circle">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">Alerte de capacité à <strong>Centre-Ville</strong></p>
                                    <small class="text-muted">Il y a 1 heure</small>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-info bg-opacity-10 p-2 rounded-circle">
                                        <i class="fas fa-chart-line text-info"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0">Rapport hebdomadaire disponible</p>
                                    <small class="text-muted">Il y a 3 heures</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Branches et Réservations -->
    <div class="row">
        <!-- Liste des branches -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Branches</h5>
                        <a href="<?php echo e(route('hotel.branches.index')); ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Gérer
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Adresse</th>
                                    <th scope="col">Manager</th>
                                    <th scope="col">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-primary me-2"></i>
                                            <span>Centre-Ville</span>
                                        </div>
                                    </td>
                                    <td>123 Rue Principale</td>
                                    <td>Catherine Durand</td>
                                    <td><span class="badge bg-success">Actif</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-primary me-2"></i>
                                            <span>Aéroport</span>
                                        </div>
                                    </td>
                                    <td>Terminal 2, Zone B</td>
                                    <td>Philippe Lemoine</td>
                                    <td><span class="badge bg-success">Actif</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-primary me-2"></i>
                                            <span>Quartier d'Affaires</span>
                                        </div>
                                    </td>
                                    <td>45 Avenue des Finances</td>
                                    <td>Valérie Girard</td>
                                    <td><span class="badge bg-success">Actif</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-primary me-2"></i>
                                            <span>Gare Centrale</span>
                                        </div>
                                    </td>
                                    <td>Place de la Gare</td>
                                    <td>Laurent Petit</td>
                                    <td><span class="badge bg-warning">Maintenance</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Réservations récentes -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Réservations Récentes</h5>
                        <a href="" class="btn btn-sm btn-primary">
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
                                    <th scope="col">Branche</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#12345</td>
                                    <td>Jean Dupont</td>
                                    <td>Centre-Ville</td>
                                    <td><?php echo e(date('d/m/Y', strtotime('+2 days'))); ?></td>
                                    <td><span class="badge bg-success">Confirmée</span></td>
                                </tr>
                                <tr>
                                    <td>#12344</td>
                                    <td>Marie Martin</td>
                                    <td>Aéroport</td>
                                    <td><?php echo e(date('d/m/Y', strtotime('+1 day'))); ?></td>
                                    <td><span class="badge bg-success">Confirmée</span></td>
                                </tr>
                                <tr>
                                    <td>#12343</td>
                                    <td>Pierre Dubois</td>
                                    <td>Quartier d'Affaires</td>
                                    <td><?php echo e(date('d/m/Y', strtotime('+3 days'))); ?></td>
                                    <td><span class="badge bg-warning">En attente</span></td>
                                </tr>
                                <tr>
                                    <td>#12342</td>
                                    <td>Sophie Leroy</td>
                                    <td>Centre-Ville</td>
                                    <td><?php echo e(date('d/m/Y', strtotime('+5 days'))); ?></td>
                                    <td><span class="badge bg-info">Nouvelle</span></td>
                                </tr>
                            </tbody>
                        </table>
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
    // Graphique de performance par branche
    const branchCtx = document.getElementById('branchPerformanceChart').getContext('2d');
    const branchChart = new Chart(branchCtx, {
        type: 'bar',
        data: {
            labels: ['Centre-Ville', 'Aéroport', 'Quartier d\'Affaires', 'Gare Centrale'],
            datasets: [
                {
                    label: 'Réservations',
                    data: [65, 42, 38, 25],
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Clients',
                    data: [89, 56, 47, 35],
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1
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
<script>
function forceLogout() {
    // 1. Envoyer la requête de déconnexion
    fetch('/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(() => {
        // 2. Supprimer les cookies d'authentification
        document.cookie.split(";").forEach(function(c) {
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
        });
        
        // 3. Vider le stockage local
        localStorage.clear();
        sessionStorage.clear();
        
        // 4. Forcer la redirection vers login
        window.location.href = '/login';
    })
    .catch(() => {
        // En cas d'erreur, forcer quand même la redirection
        window.location.href = '/login';
    });
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.hotel_manager', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/hotel/dashboard.blade.php ENDPATH**/ ?>