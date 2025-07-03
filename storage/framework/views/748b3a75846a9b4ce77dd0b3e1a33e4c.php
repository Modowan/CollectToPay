

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header"><?php echo e(__('Tableau de bord Gestionnaire de Filiale')); ?></div>

                <div class="card-body">
                    <h2>Bienvenue dans votre espace de gestion</h2>
                    <p>
                        <?php if(Auth::check()): ?>
                            Vous êtes connecté en tant que <?php echo e(Auth::user()->name); ?>.
                        <?php elseif(Auth::guard('tenant')->check()): ?>
                            Vous êtes connecté en tant que <?php echo e(Auth::guard('tenant')->user()->name); ?>.
                        <?php elseif(session('user_name')): ?>
                            Vous êtes connecté en tant que <?php echo e(session('user_name')); ?>.
                        <?php else: ?>
                            Vous êtes connecté en tant que gestionnaire de filiale.
                        <?php endif; ?>
                    </p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Gestion des réservations</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action">Réservations actives</a>
                                        <a href="#" class="list-group-item list-group-item-action">Nouvelle réservation</a>
                                        <a href="#" class="list-group-item list-group-item-action">Historique des réservations</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Gestion des chambres</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action">État des chambres</a>
                                        <a href="#" class="list-group-item list-group-item-action">Maintenance</a>
                                        <a href="#" class="list-group-item list-group-item-action">Disponibilités</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Gestion des clients</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action">Liste des clients</a>
                                        <a href="#" class="list-group-item list-group-item-action">Ajouter un client</a>
                                        <a href="#" class="list-group-item list-group-item-action">Demandes spéciales</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning">
                                    <h5 class="mb-0">Tâches à effectuer</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <span class="text-muted">Aujourd'hui</span> - Confirmer les arrivées du jour (3 clients)
                                            <span class="badge bg-danger">Urgent</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="text-muted">Aujourd'hui</span> - Vérifier les départs de demain (5 départs)
                                            <span class="badge bg-warning">Important</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="text-muted">Cette semaine</span> - Mettre à jour les disponibilités
                                            <span class="badge bg-info">Normal</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="text-muted">Cette semaine</span> - Répondre aux demandes spéciales (2)
                                            <span class="badge bg-warning">Important</span>
                                        </li>
                                    </ul>
                                    <div class="text-end">
                                        <a href="#" class="btn btn-outline-primary btn-sm">Voir toutes les tâches</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">Statistiques de la filiale</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <h3>84%</h3>
                                            <p class="text-muted">Taux d'occupation</p>
                                        </div>
                                        <div class="col-6">
                                            <h3>42/50</h3>
                                            <p class="text-muted">Chambres disponibles</p>
                                        </div>
                                    </div>
                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action">Rapport journalier</a>
                                        <a href="#" class="list-group-item list-group-item-action">Statistiques mensuelles</a>
                                        <a href="#" class="list-group-item list-group-item-action">Prévisions</a>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/branch_manager/dashboard.blade.php ENDPATH**/ ?>