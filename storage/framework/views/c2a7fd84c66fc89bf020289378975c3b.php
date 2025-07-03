<nav class="top-navbar d-flex align-items-center justify-content-between px-4">
    <!-- Côté Gauche -->
    <div class="navbar-left d-flex align-items-center">
        <!-- Bouton Toggle Sidebar -->
        <button class="btn btn-link text-dark me-3 d-lg-none" onclick="toggleSidebar()" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        
        <!-- Bouton Toggle Desktop -->
        <button class="btn btn-link text-dark me-3 d-none d-lg-block" onclick="toggleSidebar()" title="Réduire/Étendre le menu">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="d-none d-md-block">
            <ol class="breadcrumb mb-0">
                <?php if(session('customer_auth')): ?>
                    <!-- Fil d'ariane pour les clients -->
                     <?php $customer = session('customer_auth'); ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('customer.dashboard')); ?>" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>
                            Tableau de Bord Client
                        </a>
                    </li>
                    <?php if(request()->routeIs('customer.profile')): ?>
                        <li class="breadcrumb-item active">Mon Profil</li>
                    <?php elseif(request()->routeIs('customer.payments')): ?>
                        <li class="breadcrumb-item active">Mes Paiements</li>
                    <?php elseif(request()->routeIs('customer.history')): ?>
                        <li class="breadcrumb-item active">Historique</li>
                    <?php else: ?>
                        <li class="breadcrumb-item active"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></li>
                    <?php endif; ?>
                <?php else: ?>
                <!-- DEBUG: <?php echo e(json_encode(session('customer_auth'))); ?> -->

                    <!-- Fil d'ariane pour les admins/managers -->
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>
                            Accueil
                        </a>
                    </li>
                    <?php if(request()->routeIs('admin.customer*')): ?>
                        <li class="breadcrumb-item">
                            <a href="<?php echo e(route('admin.customers.index')); ?>" class="text-decoration-none">
                                Gestion des Clients
                            </a>
                        </li>
                        <?php if(request()->routeIs('admin.customers.create')): ?>
                            <li class="breadcrumb-item active">Ajouter un Client</li>
                        <?php elseif(request()->routeIs('admin.customers.edit')): ?>
                            <li class="breadcrumb-item active">Modifier un Client</li>
                        <?php elseif(request()->routeIs('admin.customers.show')): ?>
                            <li class="breadcrumb-item active">Détails du Client</li>
                        <?php elseif(request()->routeIs('admin.customer-import.create')): ?>
                            <li class="breadcrumb-item active">Importer des Clients</li>
                        <?php elseif(request()->routeIs('admin.customer-import.preview')): ?>
                            <li class="breadcrumb-item active">Aperçu de l'Importation</li>
                        <?php elseif(request()->routeIs('admin.customer-import.show')): ?>
                            <li class="breadcrumb-item active">Détails de l'Importation</li>
                        <?php elseif(request()->routeIs('admin.customer-import.index')): ?>
                            <li class="breadcrumb-item active">Liste des Importations</li>
                        <?php else: ?>
                            <li class="breadcrumb-item active">Liste des Clients</li>
                        <?php endif; ?>
                    <?php elseif(request()->routeIs('admin.hotels*')): ?>
                        <li class="breadcrumb-item">
                            <a href="<?php echo e(route('admin.hotels.index')); ?>" class="text-decoration-none">
                                Gestion des Hôtels
                            </a>
                        </li>
                        <?php if(request()->routeIs('admin.hotels.create')): ?>
                            <li class="breadcrumb-item active">Ajouter un Hôtel</li>
                        <?php elseif(request()->routeIs('admin.hotels.edit')): ?>
                            <li class="breadcrumb-item active">Modifier un Hôtel</li>
                        <?php elseif(request()->routeIs('admin.hotels.show')): ?>
                            <li class="breadcrumb-item active">Détails de l'Hôtel</li>
                        <?php elseif(request()->routeIs('admin.hotels.settings')): ?>
                            <li class="breadcrumb-item active">Paramètres des Hôtels</li>
                        <?php else: ?>
                            <li class="breadcrumb-item active">Liste des Hôtels</li>
                        <?php endif; ?>
                    <?php elseif(request()->routeIs('admin.tenants*')): ?>
                        <li class="breadcrumb-item">
                            <a href="<?php echo e(route('admin.tenants.index')); ?>" class="text-decoration-none">
                                Locataires
                            </a>
                        </li>
                        <?php if(request()->routeIs('admin.tenants.create')): ?>
                            <li class="breadcrumb-item active">Ajouter un Locataire</li>
                        <?php elseif(request()->routeIs('admin.tenants.edit')): ?>
                            <li class="breadcrumb-item active">Modifier un Locataire</li>
                        <?php elseif(request()->routeIs('admin.tenants.show')): ?>
                            <li class="breadcrumb-item active">Détails du Locataire</li>
                        <?php else: ?>
                            <li class="breadcrumb-item active">Liste des Locataires</li>
                        <?php endif; ?>
                    <?php elseif(request()->routeIs('admin.settings*')): ?>
                        <li class="breadcrumb-item">
                            <a href="<?php echo e(route('admin.settings.general')); ?>" class="text-decoration-none">
                                Paramètres
                            </a>
                        </li>
                        <?php if(request()->routeIs('admin.settings.email')): ?>
                            <li class="breadcrumb-item active">Configuration Email</li>
                        <?php elseif(request()->routeIs('admin.settings.security')): ?>
                            <li class="breadcrumb-item active">Sécurité</li>
                        <?php else: ?>
                            <li class="breadcrumb-item active">Paramètres Généraux</li>
                        <?php endif; ?>
                    <?php elseif(request()->routeIs('admin.users*')): ?>
                        <li class="breadcrumb-item">
                            <a href="<?php echo e(route('admin.users.index')); ?>" class="text-decoration-none">
                                Utilisateurs
                            </a>
                        </li>
                        <?php if(request()->routeIs('admin.users.create')): ?>
                            <li class="breadcrumb-item active">Ajouter un Utilisateur</li>
                        <?php elseif(request()->routeIs('admin.users.edit')): ?>
                            <li class="breadcrumb-item active">Modifier un Utilisateur</li>
                        <?php elseif(request()->routeIs('admin.users.show')): ?>
                            <li class="breadcrumb-item active">Détails de l'Utilisateur</li>
                        <?php else: ?>
                            <li class="breadcrumb-item active">Liste des Utilisateurs</li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="breadcrumb-item active"><?php echo $__env->yieldContent('title', 'Tableau de Bord'); ?></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ol>
        </nav>
    </div>

    <!-- Côté Droit -->
    <div class="navbar-right d-flex align-items-center">
        <!-- Recherche -->
        <div class="search-box me-3 d-none d-md-block">
            <div class="input-group">
                <input type="text" class="form-control border-0 bg-light" placeholder="Rechercher..." style="border-radius: 20px 0 0 20px;">
                <button class="btn btn-light border-0" type="button" style="border-radius: 0 20px 20px 0;">
                    <i class="fas fa-search text-muted"></i>
                </button>
            </div>
        </div>

        <!-- Notifications -->
        <div class="dropdown me-3">
            <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown" title="Notifications">
                <i class="fas fa-bell fa-lg"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                    3
                    <span class="visually-hidden">notifications non lues</span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg" style="width: 350px; max-height: 400px; overflow-y: auto;">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Notifications</h6>
                    <small class="text-muted">3 non lues</small>
                </div>
                <div class="dropdown-divider"></div>
                
                <!-- Notification 1 -->
                <a href="#" class="dropdown-item py-3 border-bottom">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-upload text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Nouvelle importation</h6>
                            <p class="mb-1 text-muted small">Importation de 150 clients terminée avec succès</p>
                            <small class="text-muted">Il y a 5 minutes</small>
                        </div>
                    </div>
                </a>

                <!-- Notification 2 -->
                <a href="#" class="dropdown-item py-3 border-bottom">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Erreur d'importation</h6>
                            <p class="mb-1 text-muted small">5 lignes n'ont pas pu être importées</p>
                            <small class="text-muted">Il y a 1 heure</small>
                        </div>
                    </div>
                </a>

                <!-- Notification 3 -->
                <a href="#" class="dropdown-item py-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-info-circle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Mise à jour système</h6>
                            <p class="mb-1 text-muted small">Nouvelle version disponible v2.1.0</p>
                            <small class="text-muted">Il y a 2 heures</small>
                        </div>
                    </div>
                </a>

                <div class="dropdown-divider"></div>
                <div class="dropdown-footer text-center">
                    <a href="#" class="btn btn-sm btn-outline-primary">Voir toutes les notifications</a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="dropdown me-3">
            <button class="btn btn-link text-dark position-relative" type="button" data-bs-toggle="dropdown" title="Messages">
                <i class="fas fa-envelope fa-lg"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success" style="font-size: 0.6rem;">
                    2
                    <span class="visually-hidden">messages non lus</span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg" style="width: 300px;">
                <div class="dropdown-header">
                    <h6 class="mb-0">Messages</h6>
                </div>
                <div class="dropdown-divider"></div>
                
                <a href="#" class="dropdown-item py-3">
                    <div class="d-flex">
                        <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Jean Dupont</h6>
                            <p class="mb-1 text-muted small">Problème avec l'importation...</p>
                            <small class="text-muted">Il y a 10 minutes</small>
                        </div>
                    </div>
                </a>

                <a href="#" class="dropdown-item py-3">
                    <div class="d-flex">
                        <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Marie Martin</h6>
                            <p class="mb-1 text-muted small">Demande d'assistance technique</p>
                            <small class="text-muted">Il y a 1 heure</small>
                        </div>
                    </div>
                </a>

                <div class="dropdown-divider"></div>
                <div class="dropdown-footer text-center">
                    <a href="#" class="btn btn-sm btn-outline-primary">Voir tous les messages</a>
                </div>
            </div>
        </div>

        <!-- Profil Utilisateur -->
        <div class="dropdown">
            <button class="btn btn-link text-dark d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                <img src="https://via.placeholder.com/35" class="rounded-circle me-2" alt="Avatar">
                <div class="d-none d-md-block text-start">
                    <?php if(session('customer_auth')): ?>
                        <!-- Affichage pour les clients -->
                        <?php $customer = session('customer_auth'); ?>
                        <div class="fw-semibold" style="font-size: 0.9rem;"><?php echo e($customer['name'] ?? 'Client'); ?></div>
                        <small class="text-muted">Client - <?php echo e($customer['tenant_name'] ?? 'Hôtel'); ?></small>
                    <?php else: ?>
                        <!-- Affichage pour les admins/managers -->
                        <div class="fw-semibold" style="font-size: 0.9rem;"><?php echo e(auth()->user()->name ?? 'Administrateur'); ?></div>
                        <small class="text-muted"><?php echo e(auth()->user()->role ?? 'Admin'); ?></small>
                    <?php endif; ?>
                </div>
                <i class="fas fa-chevron-down ms-2 text-muted" style="font-size: 0.8rem;"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg">
                <div class="dropdown-header">
                    <div class="d-flex align-items-center">
                        <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="Avatar">
                        <div>
                            <?php if(session('customer_auth')): ?>
                                <!-- Header pour les clients -->
                                <?php $customer = session('customer_auth'); ?>
                                <h6 class="mb-0"><?php echo e($customer['name'] ?? 'Client'); ?></h6>
                                <small class="text-muted"><?php echo e($customer['email'] ?? 'client@hotel.com'); ?></small>
                                <div class="mt-1">
                                    <span class="badge bg-info"><?php echo e($customer['tenant_name'] ?? 'Hôtel'); ?></span>
                                </div>
                            <?php else: ?>
                                <!-- Header pour les admins/managers -->
                                <h6 class="mb-0"><?php echo e(auth()->user()->name ?? 'Administrateur'); ?></h6>
                                <small class="text-muted"><?php echo e(auth()->user()->email ?? 'admin@collecttopay.com'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                
                <?php if(session('customer_auth')): ?>
                    <!-- Menu pour les clients -->
                    <a class="dropdown-item" href="<?php echo e(route('customer.profile')); ?>">
                        <i class="fas fa-user me-2"></i>
                        Mon Profil
                    </a>
                    <a class="dropdown-item" href="<?php echo e(route('customer.payments')); ?>">
                        <i class="fas fa-credit-card me-2"></i>
                        Mes Paiements
                    </a>
                    <a class="dropdown-item" href="<?php echo e(route('customer.history')); ?>">
                        <i class="fas fa-history me-2"></i>
                        Historique
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-question-circle me-2"></i>
                        Aide
                    </a>
                <?php else: ?>
                    <!-- Menu pour les admins/managers -->
                    <a class="dropdown-item" href="<?php echo e(route('admin.profile')); ?>">
                        <i class="fas fa-user me-2"></i>
                        Mon Profil
                    </a>
                    <a class="dropdown-item" href="<?php echo e(route('admin.settings.general')); ?>">
                        <i class="fas fa-cog me-2"></i>
                        Paramètres
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-bell me-2"></i>
                        Préférences
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-question-circle me-2"></i>
                        Aide
                    </a>
                <?php endif; ?>
                
                <div class="dropdown-divider"></div>
                
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Overlay pour mobile -->
<div class="sidebar-overlay d-lg-none" onclick="toggleSidebar()"></div>

<style>
/* Styles pour la navbar */
.top-navbar {
    background: white !important;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.breadcrumb {
    background: none;
    padding: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6b7280;
    font-weight: bold;
}

.breadcrumb-item a {
    color: #6366f1;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #4f46e5;
    text-decoration: underline;
}

.breadcrumb-item.active {
    color: #374151;
    font-weight: 500;
}

/* Styles pour les dropdowns */
.dropdown-menu {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    margin-top: 8px;
}

.dropdown-item {
    padding: 12px 20px;
    border-radius: 8px;
    margin: 2px 8px;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8fafc;
    color: #1e293b;
}

.dropdown-header {
    padding: 15px 20px 10px;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
}

.dropdown-footer {
    padding: 10px 20px 15px;
    border-top: 1px solid #e2e8f0;
}

/* Styles pour la recherche */
.search-box .form-control {
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.search-box .form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Styles pour les notifications */
.badge {
    font-size: 0.6rem;
    padding: 0.25em 0.5em;
}

/* Responsive */
@media (max-width: 768px) {
    .navbar-right .d-none.d-md-block {
        display: none !important;
    }
    
    .dropdown-menu {
        width: 280px !important;
        max-width: 90vw;
    }
}

/* Animation pour le toggle sidebar */
.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Styles pour les avatars */
.rounded-circle {
    border: 2px solid #e2e8f0;
    transition: all 0.2s ease;
}

.rounded-circle:hover {
    border-color: #6366f1;
}

/* Badge pour le tenant */
.badge.bg-info {
    background-color: #06b6d4 !important;
    font-size: 0.7rem;
}
</style>

<?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/layouts/partials/admin/navbar.blade.php ENDPATH**/ ?>