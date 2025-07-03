

<?php $__env->startSection('title', 'Dashboard Client'); ?>
<?php $__env->startSection('page-title', 'Tableau de Bord'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <?php if(session('customer_auth')): ?>
                                <?php $customer = session('customer_auth'); ?>
                                <h1 class="h3 mb-2">
                                    <i class="fas fa-sun me-2"></i>
                                    Bonjour <?php echo e(explode(' ', $customer['name'] ?? 'Client')[0]); ?> !
                                </h1>
                                <p class="mb-0 opacity-90">
                                    Bienvenue dans votre espace client <?php echo e($customer['tenant_name'] ?? 'Hôtel'); ?>

                                </p>
                            <?php else: ?>
                                <h1 class="h3 mb-2">
                                    <i class="fas fa-sun me-2"></i>
                                    Bienvenue !
                                </h1>
                                <p class="mb-0 opacity-90">
                                    Bienvenue dans votre espace client
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex align-items-center justify-content-end">
                                <div class="me-3">
                                    <small class="d-block opacity-75">Aujourd'hui</small>
                                    <strong><?php echo e(date('d/m/Y')); ?></strong>
                                </div>
                                <div class="bg-white bg-opacity-20 rounded-circle p-3">
                                    <i class="fas fa-calendar-day fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Completion Alert -->
    <?php if(session('customer_auth')): ?>
        <?php 
            $customer = session('customer_auth');
            // Simuler un profil incomplet (à remplacer par la vraie logique)
            $profileCompleted = false;
            $completionPercentage = 45; // Exemple
        ?>
        <?php if(!$profileCompleted): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm" role="alert">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="alert-heading mb-1">Profil incomplet</h6>
                                    <p class="mb-0">
                                        Complétez votre profil pour profiter pleinement de nos services. 
                                        <strong><?php echo e($completionPercentage); ?>% complété</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="<?php echo e(route('customer.profile')); ?>" class="btn btn-warning">
                                <i class="fas fa-user-edit me-1"></i>
                                Compléter mon profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Quick Actions Cards -->
    <div class="row mb-4">
        <!-- Mon Profil Card - NOUVEAU -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-2x text-primary"></i>
                        </div>
                    </div>
                    <h5 class="card-title mb-2">Mon Profil</h5>
                    <p class="card-text text-muted mb-3">
                        Gérez vos informations personnelles et préférences
                    </p>
                    <?php if(session('customer_auth')): ?>
                        <?php $completionPercentage = 45; ?>
                        <div class="mb-3">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: <?php echo e($completionPercentage); ?>%" 
                                     aria-valuenow="<?php echo e($completionPercentage); ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <small class="text-muted"><?php echo e($completionPercentage); ?>% complété</small>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo e(route('customer.profile')); ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>
                        Accéder au profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Mes Paiements Card -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-credit-card fa-2x text-success"></i>
                        </div>
                    </div>
                    <h5 class="card-title mb-2">Mes Paiements</h5>
                    <p class="card-text text-muted mb-3">
                        Consultez et gérez vos moyens de paiement
                    </p>
                    <div class="mb-3">
                        <span class="badge bg-success">2 cartes actives</span>
                    </div>
                    <a href="<?php echo e(route('customer.payments')); ?>" class="btn btn-success">
                        <i class="fas fa-wallet me-1"></i>
                        Voir les paiements
                    </a>
                </div>
            </div>
        </div>

        <!-- Historique Card -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-history fa-2x text-info"></i>
                        </div>
                    </div>
                    <h5 class="card-title mb-2">Historique</h5>
                    <p class="card-text text-muted mb-3">
                        Consultez l'historique de vos transactions
                    </p>
                    <div class="mb-3">
                        <span class="badge bg-info">12 transactions</span>
                    </div>
                    <a href="<?php echo e(route('customer.history')); ?>" class="btn btn-info">
                        <i class="fas fa-list me-1"></i>
                        Voir l'historique
                    </a>
                </div>
            </div>
        </div>

        <!-- Support Card -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-headset fa-2x text-warning"></i>
                        </div>
                    </div>
                    <h5 class="card-title mb-2">Support</h5>
                    <p class="card-text text-muted mb-3">
                        Besoin d'aide ? Contactez notre équipe
                    </p>
                    <div class="mb-3">
                        <span class="badge bg-success">En ligne</span>
                    </div>
                    <a href="#" class="btn btn-warning">
                        <i class="fas fa-comments me-1"></i>
                        Contacter le support
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <!-- Account Summary -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Résumé du compte
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h2 text-primary mb-1">€1,245.50</div>
                                <small class="text-muted">Solde total</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h2 text-success mb-1">12</div>
                                <small class="text-muted">Transactions ce mois</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="h2 text-info mb-1">€89.30</div>
                                <small class="text-muted">Dernière transaction</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Completion -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user-check me-2 text-primary"></i>
                        Profil
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php if(session('customer_auth')): ?>
                        <?php $completionPercentage = 45; ?>
                        <div class="progress-circle mx-auto mb-3" style="width: 100px; height: 100px;">
                            <div class="progress-circle-inner">
                                <?php echo e($completionPercentage); ?>%
                            </div>
                        </div>
                        <h6 class="mb-2">Profil <?php echo e($completionPercentage < 100 ? 'incomplet' : 'complet'); ?></h6>
                        <p class="text-muted small mb-3">
                            <?php echo e($completionPercentage < 100 ? 'Complétez votre profil pour une meilleure expérience' : 'Votre profil est complet !'); ?>

                        </p>
                        <?php if($completionPercentage < 100): ?>
                            <a href="<?php echo e(route('customer.profile')); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>
                                Compléter
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('customer.profile')); ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>
                                Modifier
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="fas fa-clock me-2 text-primary"></i>
                                Activité récente
                            </h5>
                        </div>
                        <div class="col-auto">
                            <a href="<?php echo e(route('customer.history')); ?>" class="btn btn-outline-primary btn-sm">
                                Voir tout
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Timeline Item 1 -->
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Paiement confirmé</h6>
                                        <p class="text-muted mb-0">Transaction de €89.30 confirmée</p>
                                    </div>
                                    <small class="text-muted">Il y a 2h</small>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Item 2 -->
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Profil mis à jour</h6>
                                        <p class="text-muted mb-0">Informations de contact modifiées</p>
                                    </div>
                                    <small class="text-muted">Hier</small>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline Item 3 -->
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-sign-in-alt text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Première connexion</h6>
                                        <p class="text-muted mb-0">Bienvenue dans votre espace client !</p>
                                    </div>
                                    <small class="text-muted">Il y a 3 jours</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hover-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.progress-circle {
    background: conic-gradient(var(--primary-color) 0deg, var(--primary-color) 162deg, #e9ecef 162deg, #e9ecef 360deg);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.progress-circle-inner {
    width: 75px;
    height: 75px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: var(--primary-color);
}

.timeline {
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.card-header {
    border-bottom: 1px solid #e9ecef !important;
}

.badge {
    font-size: 0.75em;
}

@media (max-width: 768px) {
    .hover-card:hover {
        transform: none;
    }
    
    .timeline::before {
        display: none;
    }
}
</style>

<!-- Custom JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(function(bar) {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(function() {
            bar.style.transition = 'width 1s ease-in-out';
            bar.style.width = width;
        }, 500);
    });

    // Animate progress circle
    const progressCircle = document.querySelector('.progress-circle');
    if (progressCircle) {
        const percentage = 45; // À remplacer par la vraie valeur
        const degrees = (percentage / 100) * 360;
        
        setTimeout(function() {
            progressCircle.style.transition = 'background 1s ease-in-out';
            progressCircle.style.background = `conic-gradient(var(--primary-color) ${degrees}deg, #e9ecef ${degrees}deg)`;
        }, 800);
    }

    // Auto-refresh profile completion
    setInterval(function() {
        fetch('/customer/profile/status', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.completion_percentage) {
                // Update progress displays
                const percentage = data.completion_percentage;
                
                // Update progress bars
                const progressBars = document.querySelectorAll('.progress-bar');
                progressBars.forEach(function(bar) {
                    bar.style.width = percentage + '%';
                    bar.setAttribute('aria-valuenow', percentage);
                });
                
                // Update progress circle
                const progressCircle = document.querySelector('.progress-circle');
                if (progressCircle) {
                    const degrees = (percentage / 100) * 360;
                    progressCircle.style.background = `conic-gradient(var(--primary-color) ${degrees}deg, #e9ecef ${degrees}deg)`;
                    
                    const inner = progressCircle.querySelector('.progress-circle-inner');
                    if (inner) {
                        inner.textContent = percentage + '%';
                    }
                }
                
                // Update percentage text
                const percentageTexts = document.querySelectorAll('small:contains("% complété")');
                percentageTexts.forEach(function(text) {
                    text.textContent = percentage + '% complété';
                });
            }
        })
        .catch(error => {
            console.log('Profile status update failed');
        });
    }, 30000); // Check every 30 seconds
});
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.customer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/customer/dashboard.blade.php ENDPATH**/ ?>