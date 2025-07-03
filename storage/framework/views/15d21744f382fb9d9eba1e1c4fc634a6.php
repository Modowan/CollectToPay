

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header"><?php echo e(__('Tableau de bord Gestionnaire d\'Hôtel')); ?></div>

                <div class="card-body">
                    <h2>Bienvenue dans votre espace de gestion</h2>
                    <p>Vous êtes connecté en tant que gestionnaire d'hôtel.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Gestion des filiales</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action">Liste des filiales</a>
                                        <a href="#" class="list-group-item list-group-item-action">Ajouter une filiale</a>
                                        <a href="#" class="list-group-item list-group-item-action">Statistiques des filiales</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Gestion des clients</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action">Liste des clients</a>
                                        <a href="#" class="list-group-item list-group-item-action">Ajouter un client</a>
                                        <a href="#" class="list-group-item list-group-item-action">Importer des clients</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Rapports</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action">Rapport mensuel</a>
                                        <a href="#" class="list-group-item list-group-item-action">Statistiques globales</a>
                                        <a href="#" class="list-group-item list-group-item-action">Exporter les données</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning">
                                    <h5 class="mb-0">Activité récente</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <span class="text-muted">Aujourd'hui 14:30</span> - Nouveau client ajouté
                                        </li>
                                        <li class="mb-2">
                                            <span class="text-muted">Aujourd'hui 11:15</span> - Mise à jour des informations de la filiale Paris Centre
                                        </li>
                                        <li class="mb-2">
                                            <span class="text-muted">Hier 16:45</span> - Rapport mensuel généré
                                        </li>
                                        <li class="mb-2">
                                            <span class="text-muted">Hier 09:30</span> - 3 nouveaux clients importés
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">Paramètres de l'hôtel</h5>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        <a href="#" class="list-group-item list-group-item-action">Informations générales</a>
                                        <a href="#" class="list-group-item list-group-item-action">Paramètres de facturation</a>
                                        <a href="#" class="list-group-item list-group-item-action">Gestion des utilisateurs</a>
                                        <a href="#" class="list-group-item list-group-item-action">Personnalisation</a>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/hotel_manager/dashboard.blade.php ENDPATH**/ ?>