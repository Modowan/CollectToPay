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
                @if(session('customer_auth'))
                    <!-- Fil d'ariane pour les clients -->
                     @php $customer = session('customer_auth'); @endphp
                    <li class="breadcrumb-item">
                        <a href="{{ route('customer.dashboard') }}" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>
                            Tableau de Bord Client
                        </a>
                    </li>
                    @if(request()->routeIs('customer.profile'))
                        <li class="breadcrumb-item active">Mon Profil</li>
                    @elseif(request()->routeIs('customer.payments'))
                        <li class="breadcrumb-item active">Mes Paiements</li>
                    @elseif(request()->routeIs('customer.history'))
                        <li class="breadcrumb-item active">Historique</li>
                    @else
                        <li class="breadcrumb-item active">@yield('title', 'Dashboard')</li>
                    @endif
                @else
                <!-- DEBUG: {{ json_encode(session('customer_auth')) }} -->

                    <!-- Fil d'ariane pour les admins/managers -->
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>
                            Accueil
                        </a>
                    </li>
                    @if(request()->routeIs('admin.customer*'))
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.customers.index') }}" class="text-decoration-none">
                                Gestion des Clients
                            </a>
                        </li>
                        @if(request()->routeIs('admin.customers.create'))
                            <li class="breadcrumb-item active">Ajouter un Client</li>
                        @elseif(request()->routeIs('admin.customers.edit'))
                            <li class="breadcrumb-item active">Modifier un Client</li>
                        @elseif(request()->routeIs('admin.customers.show'))
                            <li class="breadcrumb-item active">Détails du Client</li>
                        @elseif(request()->routeIs('admin.customer-import.create'))
                            <li class="breadcrumb-item active">Importer des Clients</li>
                        @elseif(request()->routeIs('admin.customer-import.preview'))
                            <li class="breadcrumb-item active">Aperçu de l'Importation</li>
                        @elseif(request()->routeIs('admin.customer-import.show'))
                            <li class="breadcrumb-item active">Détails de l'Importation</li>
                        @elseif(request()->routeIs('admin.customer-import.index'))
                            <li class="breadcrumb-item active">Liste des Importations</li>
                        @else
                            <li class="breadcrumb-item active">Liste des Clients</li>
                        @endif
                    @elseif(request()->routeIs('admin.hotels*'))
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.hotels.index') }}" class="text-decoration-none">
                                Gestion des Hôtels
                            </a>
                        </li>
                        @if(request()->routeIs('admin.hotels.create'))
                            <li class="breadcrumb-item active">Ajouter un Hôtel</li>
                        @elseif(request()->routeIs('admin.hotels.edit'))
                            <li class="breadcrumb-item active">Modifier un Hôtel</li>
                        @elseif(request()->routeIs('admin.hotels.show'))
                            <li class="breadcrumb-item active">Détails de l'Hôtel</li>
                        @elseif(request()->routeIs('admin.hotels.settings'))
                            <li class="breadcrumb-item active">Paramètres des Hôtels</li>
                        @else
                            <li class="breadcrumb-item active">Liste des Hôtels</li>
                        @endif
                    @elseif(request()->routeIs('admin.tenants*'))
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.tenants.index') }}" class="text-decoration-none">
                                Locataires
                            </a>
                        </li>
                        @if(request()->routeIs('admin.tenants.create'))
                            <li class="breadcrumb-item active">Ajouter un Locataire</li>
                        @elseif(request()->routeIs('admin.tenants.edit'))
                            <li class="breadcrumb-item active">Modifier un Locataire</li>
                        @elseif(request()->routeIs('admin.tenants.show'))
                            <li class="breadcrumb-item active">Détails du Locataire</li>
                        @else
                            <li class="breadcrumb-item active">Liste des Locataires</li>
                        @endif
                    @elseif(request()->routeIs('admin.settings*'))
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.settings.general') }}" class="text-decoration-none">
                                Paramètres
                            </a>
                        </li>
                        @if(request()->routeIs('admin.settings.email'))
                            <li class="breadcrumb-item active">Configuration Email</li>
                        @elseif(request()->routeIs('admin.settings.security'))
                            <li class="breadcrumb-item active">Sécurité</li>
                        @else
                            <li class="breadcrumb-item active">Paramètres Généraux</li>
                        @endif
                    @elseif(request()->routeIs('admin.users*'))
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                                Utilisateurs
                            </a>
                        </li>
                        @if(request()->routeIs('admin.users.create'))
                            <li class="breadcrumb-item active">Ajouter un Utilisateur</li>
                        @elseif(request()->routeIs('admin.users.edit'))
                            <li class="breadcrumb-item active">Modifier un Utilisateur</li>
                        @elseif(request()->routeIs('admin.users.show'))
                            <li class="breadcrumb-item active">Détails de l'Utilisateur</li>
                        @else
                            <li class="breadcrumb-item active">Liste des Utilisateurs</li>
                        @endif
                    @else
                        <li class="breadcrumb-item active">@yield('title', 'Tableau de Bord')</li>
                    @endif
                @endif
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
                    @if(session('customer_auth'))
                        <!-- Affichage pour les clients -->
                        @php $customer = session('customer_auth'); @endphp
                        <div class="fw-semibold" style="font-size: 0.9rem;">{{ $customer['name'] ?? 'Client' }}</div>
                        <small class="text-muted">Client - {{ $customer['tenant_name'] ?? 'Hôtel' }}</small>
                    @else
                        <!-- Affichage pour les admins/managers -->
                        <div class="fw-semibold" style="font-size: 0.9rem;">{{ auth()->user()->name ?? 'Administrateur' }}</div>
                        <small class="text-muted">{{ auth()->user()->role ?? 'Admin' }}</small>
                    @endif
                </div>
                <i class="fas fa-chevron-down ms-2 text-muted" style="font-size: 0.8rem;"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg">
                <div class="dropdown-header">
                    <div class="d-flex align-items-center">
                        <img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="Avatar">
                        <div>
                            @if(session('customer_auth'))
                                <!-- Header pour les clients -->
                                @php $customer = session('customer_auth'); @endphp
                                <h6 class="mb-0">{{ $customer['name'] ?? 'Client' }}</h6>
                                <small class="text-muted">{{ $customer['email'] ?? 'client@hotel.com' }}</small>
                                <div class="mt-1">
                                    <span class="badge bg-info">{{ $customer['tenant_name'] ?? 'Hôtel' }}</span>
                                </div>
                            @else
                                <!-- Header pour les admins/managers -->
                                <h6 class="mb-0">{{ auth()->user()->name ?? 'Administrateur' }}</h6>
                                <small class="text-muted">{{ auth()->user()->email ?? 'admin@collecttopay.com' }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                
                @if(session('customer_auth'))
                    <!-- Menu pour les clients -->
                    <a class="dropdown-item" href="{{ route('customer.profile') }}">
                        <i class="fas fa-user me-2"></i>
                        Mon Profil
                    </a>
                    <a class="dropdown-item" href="{{ route('customer.payments') }}">
                        <i class="fas fa-credit-card me-2"></i>
                        Mes Paiements
                    </a>
                    <a class="dropdown-item" href="{{ route('customer.history') }}">
                        <i class="fas fa-history me-2"></i>
                        Historique
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-question-circle me-2"></i>
                        Aide
                    </a>
                @else
                    <!-- Menu pour les admins/managers -->
                    <a class="dropdown-item" href="{{ route('admin.profile') }}">
                        <i class="fas fa-user me-2"></i>
                        Mon Profil
                    </a>
                    <a class="dropdown-item" href="{{ route('admin.settings.general') }}">
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
                @endif
                
                <div class="dropdown-divider"></div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
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

