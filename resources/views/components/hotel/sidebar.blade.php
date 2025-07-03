<div class="sidebar" id="sidebar">
    <!-- En-tête Sidebar -->
    <div class="sidebar-header p-4 border-bottom border-light border-opacity-25">
        <div class="d-flex align-items-center">
            <div class="sidebar-logo me-3">
                <i class="fas fa-hotel fa-2x text-white"></i>
            </div>
            <div class="sidebar-brand">
                <h4 class="mb-0 text-white fw-bold">CollectToPay</h4>
                <small class="text-light opacity-75">Gestion Hôtelière</small>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav mt-4">
        <ul class="nav flex-column">
            <!-- Tableau de Bord -->
            <li class="nav-item">
                <a href="{{ route('hotel.dashboard') }}" 
                   class="nav-link {{ request()->routeIs('hotel.dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt me-3"></i>
                    <span class="nav-text">Tableau de Bord</span>
                </a>
            </li>

            <!-- Séparateur -->
            <li class="nav-divider">
                <hr class="border-light border-opacity-25 my-3">
                <small class="text-light opacity-50 px-3">GESTION</small>
            </li>

            <!-- Gestion des Branches -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#branchesMenu" 
                   aria-expanded="{{ request()->routeIs('hotel.branches*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-building me-3"></i>
                    <span class="nav-text">Gestion des Branches</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('hotel.branches*') ? 'show' : '' }}" id="branchesMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('hotel.branches.index') }}" class="nav-link sub-link {{ request()->routeIs('hotel.branches.index') ? 'active' : '' }}">
                                <i class="fas fa-list me-2"></i>
                                Liste des Branches
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('hotel.branches.create') }}" class="nav-link sub-link {{ request()->routeIs('hotel.branches.create') ? 'active' : '' }}">
                                <i class="fas fa-plus me-2"></i>
                                Ajouter une Branche
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.branches.settings') ? 'active' : '' }}">
                                <i class="fas fa-cog me-2"></i>
                                Paramètres des Branches
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Gestion des Clients -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#clientsMenu" 
                   aria-expanded="{{ request()->routeIs('hotel.customer*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-users me-3"></i>
                    <span class="nav-text">Gestion des Clients</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('hotel.customer*') ? 'show' : '' }}" id="clientsMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.customers.index') ? 'active' : '' }}">
                                <i class="fas fa-list me-2"></i>
                                Liste des Clients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.customers.create') ? 'active' : '' }}">
                                <i class="fas fa-user-plus me-2"></i>
                                Ajouter un Client
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" 
                               class="nav-link sub-link {{ request()->routeIs('hotel.customer-import*') ? 'active' : '' }}">
                                <i class="fas fa-upload me-2"></i>
                                Importer des Clients
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Gestion du Personnel -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#staffMenu"
                   aria-expanded="{{ request()->routeIs('hotel.staff*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-user-tie me-3"></i>
                    <span class="nav-text">Gestion du Personnel</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('hotel.staff*') ? 'show' : '' }}" id="staffMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.staff.index') ? 'active' : '' }}">
                                <i class="fas fa-list me-2"></i>
                                Liste du Personnel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.staff.create') ? 'active' : '' }}">
                                <i class="fas fa-user-plus me-2"></i>
                                Ajouter un Employé
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.branch-managers*') ? 'active' : '' }}">
                                <i class="fas fa-user-shield me-2"></i>
                                Gestionnaires de Branches
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Réservations -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#bookingsMenu"
                   aria-expanded="{{ request()->routeIs('hotel.bookings*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-calendar-check me-3"></i>
                    <span class="nav-text">Réservations</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('hotel.bookings*') ? 'show' : '' }}" id="bookingsMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.bookings.index') ? 'active' : '' }}">
                                <i class="fas fa-list me-2"></i>
                                Toutes les Réservations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.bookings.pending') ? 'active' : '' }}">
                                <i class="fas fa-clock me-2"></i>
                                Réservations en Attente
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.bookings.create') ? 'active' : '' }}">
                                <i class="fas fa-plus me-2"></i>
                                Nouvelle Réservation
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Séparateur -->
            <li class="nav-divider">
                <hr class="border-light border-opacity-25 my-3">
                <small class="text-light opacity-50 px-3">RAPPORTS</small>
            </li>

            <!-- Rapports -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#reportsMenu"
                   aria-expanded="{{ request()->routeIs('hotel.reports*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-chart-bar me-3"></i>
                    <span class="nav-text">Rapports</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('hotel.reports*') ? 'show' : '' }}" id="reportsMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.reports.dashboard') ? 'active' : '' }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Tableau de Performance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.reports.branches') ? 'active' : '' }}">
                                <i class="fas fa-building me-2"></i>
                                Performance par Branche
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.reports.customers') ? 'active' : '' }}">
                                <i class="fas fa-users me-2"></i>
                                Analyse Clientèle
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.reports.export') ? 'active' : '' }}">
                                <i class="fas fa-file-export me-2"></i>
                                Exporter Données
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Journaux -->
            <li class="nav-item">
                <a href="" class="nav-link {{ request()->routeIs('hotel.logs') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-history me-3"></i>
                    <span class="nav-text">Journaux d'Activité</span>
                </a>
            </li>

            <!-- Séparateur -->
            <li class="nav-divider">
                <hr class="border-light border-opacity-25 my-3">
                <small class="text-light opacity-50 px-3">PARAMÈTRES</small>
            </li>

            <!-- Paramètres -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#settingsMenu"
                   aria-expanded="{{ request()->routeIs('hotel.settings*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-cogs me-3"></i>
                    <span class="nav-text">Paramètres</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('hotel.settings*') ? 'show' : '' }}" id="settingsMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.settings.general') ? 'active' : '' }}">
                                <i class="fas fa-sliders-h me-2"></i>
                                Paramètres Généraux
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.settings.notifications') ? 'active' : '' }}">
                                <i class="fas fa-bell me-2"></i>
                                Notifications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="" class="nav-link sub-link {{ request()->routeIs('hotel.settings.appearance') ? 'active' : '' }}">
                                <i class="fas fa-paint-brush me-2"></i>
                                Apparence
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Pied de page Sidebar -->
    <div class="sidebar-footer mt-auto p-4 border-top border-light border-opacity-25">
        <div class="d-flex align-items-center text-light">
            <div class="me-3">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-semibold">{{ auth()->user()->name ?? 'Hotel Manager' }}</div>
                <small class="opacity-75">{{ auth()->user()->email ?? 'manager@hotel.com' }}</small>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="" class="btn btn-outline-light btn-sm me-2" title="Profil">
                <i class="fas fa-user"></i>
            </a>
            <a href="" class="btn btn-outline-light btn-sm me-2" title="Paramètres">
                <i class="fas fa-cog"></i>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline" id="logout-form">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm" title="Déconnexion">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Styles spécifiques pour la sidebar */
.sidebar {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    left: 0; /* Positionnement explicite à gauche */
}

.sidebar-header {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
}

.sidebar-brand h4 {
    font-size: 1.5rem;
    letter-spacing: -0.5px;
}

.nav-link {
    color: rgba(255,255,255,0.8) !important;
    padding: 0.75rem 1.5rem;
    margin: 0.25rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-link:hover {
    color: white !important;
    background: rgba(255,255,255,0.1);
    transform: translateX(5px);
}

.nav-link.active {
    color: white !important;
    background: rgba(255,255,255,0.2);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: white;
    border-radius: 0 4px 4px 0;
}

.nav-icon {
    width: 20px;
    text-align: center;
    opacity: 0.8;
}

.nav-arrow {
    transition: transform 0.3s ease;
    font-size: 0.8rem;
}

.nav-link[aria-expanded="true"] .nav-arrow {
    transform: rotate(180deg);
}

.sub-menu {
    background: rgba(0,0,0,0.1);
    margin: 0.5rem 1rem;
    border-radius: 8px;
    padding: 0.5rem 0;
}

.sub-link {
    color: rgba(255,255,255,0.7) !important;
    padding: 0.5rem 1.5rem;
    margin: 0.125rem 0.5rem;
    font-size: 0.9rem;
}

.sub-link:hover {
    color: white !important;
    background: rgba(255,255,255,0.1);
    transform: translateX(3px);
}

.sub-link.active {
    color: white !important;
    background: rgba(255,255,255,0.15);
}

.nav-divider {
    margin: 1rem 0;
}

.nav-divider small {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 1px;
}

.sidebar-footer {
    background: rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
}

/* Animation pour les icônes */
.nav-link i {
    transition: all 0.3s ease;
}

.nav-link:hover i {
    transform: scale(1.1);
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        z-index: 1050;
        left: 0; /* Maintenir à gauche même en mobile */
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
    
    .nav-link {
        margin: 0.125rem 0.5rem;
    }
    
    .sidebar-header {
        padding: 1rem !important;
    }
    
    .sidebar-footer {
        padding: 1rem !important;
    }
}

/* Scrollbar pour la sidebar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}
</style>
