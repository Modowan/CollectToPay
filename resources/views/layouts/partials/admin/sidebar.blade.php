<div class="sidebar" id="sidebar">
    <!-- En-tête Sidebar -->
    <div class="sidebar-header p-4 border-bottom border-light border-opacity-25">
        <div class="d-flex align-items-center">
            <div class="sidebar-logo me-3">
                <i class="fas fa-credit-card fa-2x text-white"></i>
            </div>
            <div class="sidebar-brand">
                <h4 class="mb-0 text-white fw-bold">CollectToPay</h4>
                <small class="text-light opacity-75">Panneau d'Administration</small>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav mt-4">
        <ul class="nav flex-column">
            <!-- Tableau de Bord -->
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" 
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt me-3"></i>
                    <span class="nav-text">Tableau de Bord</span>
                </a>
            </li>

            <!-- Séparateur -->
            <li class="nav-divider">
                <hr class="border-light border-opacity-25 my-3">
                <small class="text-light opacity-50 px-3">GESTION</small>
            </li>

            <!-- Gestion des Clients -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#clientsMenu" 
                   aria-expanded="{{ request()->routeIs('admin.customer*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-users me-3"></i>
                    <span class="nav-text">Gestion des Clients</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.customer*') ? 'show' : '' }}" id="clientsMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('admin.customers.index') }}" class="nav-link sub-link {{ request()->routeIs('admin.customers.index') ? 'active' : '' }}">
                                <i class="fas fa-list me-2"></i>
                                Liste des Clients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.customers.create') }}" class="nav-link sub-link {{ request()->routeIs('admin.customers.create') ? 'active' : '' }}">
                                <i class="fas fa-user-plus me-2"></i>
                                Ajouter un Client
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.customer-import.index') }}" 
                               class="nav-link sub-link {{ request()->routeIs('admin.customer-import*') ? 'active' : '' }}">
                                <i class="fas fa-upload me-2"></i>
                                Importer des Clients
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Gestion des Hôtels -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#hotelsMenu"
                   aria-expanded="{{ request()->routeIs('admin.hotels*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-hotel me-3"></i>
                    <span class="nav-text">Gestion des Hôtels</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.hotels*') ? 'show' : '' }}" id="hotelsMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('admin.hotels.index') }}" class="nav-link sub-link {{ request()->routeIs('admin.hotels.index') ? 'active' : '' }}">
                                <i class="fas fa-list me-2"></i>
                                Liste des Hôtels
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.hotels.create') }}" class="nav-link sub-link {{ request()->routeIs('admin.hotels.create') ? 'active' : '' }}">
                                <i class="fas fa-plus me-2"></i>
                                Ajouter un Hôtel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.hotels.settings') }}" class="nav-link sub-link {{ request()->routeIs('admin.hotels.settings') ? 'active' : '' }}">
                                <i class="fas fa-cog me-2"></i>
                                Paramètres Hôtels
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Gestion des Locataires -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#tenantsMenu"
                   aria-expanded="{{ request()->routeIs('admin.tenants*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-building me-3"></i>
                    <span class="nav-text">Locataires</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.tenants*') ? 'show' : '' }}" id="tenantsMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('admin.tenants.index') }}" class="nav-link sub-link {{ request()->routeIs('admin.tenants.index') ? 'active' : '' }}">
                                <i class="fas fa-list me-2"></i>
                                Liste des Locataires
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.tenants.create') }}" class="nav-link sub-link {{ request()->routeIs('admin.tenants.create') ? 'active' : '' }}">
                                <i class="fas fa-plus me-2"></i>
                                Ajouter un Locataire
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
                   aria-expanded="{{ request()->routeIs('admin.reports*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-chart-bar me-3"></i>
                    <span class="nav-text">Rapports</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.reports*') ? 'show' : '' }}" id="reportsMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.statistics') }}" class="nav-link sub-link {{ request()->routeIs('admin.reports.statistics') ? 'active' : '' }}">
                                <i class="fas fa-chart-line me-2"></i>
                                Statistiques
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.export') }}" class="nav-link sub-link {{ request()->routeIs('admin.reports.export') ? 'active' : '' }}">
                                <i class="fas fa-file-export me-2"></i>
                                Exporter Données
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Journaux -->
            <li class="nav-item">
                <a href="{{ route('admin.logs') }}" class="nav-link {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-history me-3"></i>
                    <span class="nav-text">Journaux d'Activité</span>
                </a>
            </li>

            <!-- Séparateur -->
            <li class="nav-divider">
                <hr class="border-light border-opacity-25 my-3">
                <small class="text-light opacity-50 px-3">SYSTÈME</small>
            </li>

            <!-- Paramètres -->
            <li class="nav-item">
                <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#settingsMenu"
                   aria-expanded="{{ request()->routeIs('admin.settings*') ? 'true' : 'false' }}">
                    <i class="nav-icon fas fa-cogs me-3"></i>
                    <span class="nav-text">Paramètres</span>
                    <i class="fas fa-chevron-down ms-auto nav-arrow"></i>
                </a>
                <div class="collapse {{ request()->routeIs('admin.settings*') ? 'show' : '' }}" id="settingsMenu">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.general') }}" class="nav-link sub-link {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">
                                <i class="fas fa-sliders-h me-2"></i>
                                Paramètres Généraux
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.email') }}" class="nav-link sub-link {{ request()->routeIs('admin.settings.email') ? 'active' : '' }}">
                                <i class="fas fa-envelope me-2"></i>
                                Configuration Email
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.security') }}" class="nav-link sub-link {{ request()->routeIs('admin.settings.security') ? 'active' : '' }}">
                                <i class="fas fa-shield-alt me-2"></i>
                                Sécurité
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Utilisateurs -->
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-cog me-3"></i>
                    <span class="nav-text">Utilisateurs</span>
                </a>
            </li>

            <!-- Sauvegardes -->
            <li class="nav-item">
                <a href="{{ route('admin.backups') }}" class="nav-link {{ request()->routeIs('admin.backups') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-database me-3"></i>
                    <span class="nav-text">Sauvegardes</span>
                </a>
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
                <div class="fw-semibold">{{ auth()->user()->name ?? 'Administrateur' }}</div>
                <small class="opacity-75">{{ auth()->user()->email ?? 'admin@collecttopay.com' }}</small>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="{{ route('admin.profile') }}" class="btn btn-outline-light btn-sm me-2" title="Profil">
                <i class="fas fa-user"></i>
            </a>
            <a href="{{ route('admin.settings.general') }}" class="btn btn-outline-light btn-sm me-2" title="Paramètres">
                <i class="fas fa-cog"></i>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm" title="Déconnexion">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</div>


<style>
/* ================================================== */
/* STYLES PRINCIPAUX DE LA SIDEBAR - Version défilante */
/* ================================================== */

.sidebar {
    /* Style de base inchangé */
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: 280px;
    
    /* Gestion du défilement */
    overflow-y: auto; /* Active le défilement pour TOUTE la sidebar */
    height: 100vh;
    
    /* Organisation simplifiée */
    display: flex;
    flex-direction: column;
    z-index: 1000;
}

/* ================================================== */
/* EN-TÊTE DE LA SIDEBAR - Maintenant défilant */
/* ================================================== */

.sidebar-header {
    /* Styles visuels inchangés */
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    
    /* SUPPRIMER flex-shrink: 0 pour permettre le défilement */
}

/* ================================================== */
/* CONTENU PRINCIPAL */
/* ================================================== */

.sidebar-nav {
    /* La navigation prend tout l'espace nécessaire */
    padding-bottom: 1rem;
}

/* ================================================== */
/* PIED DE PAGE - Maintenant défilant */
/* ================================================== */

.sidebar-footer {
    /* Styles visuels inchangés */
    background: rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    
    /* SUPPRIMER flex-shrink: 0 pour permettre le défilement */
}

/* ================================================== */
/* STYLES RESTANTS - Inchangés */
/* ================================================== */

.sidebar-brand h4 {
    font-size: 1.5rem;
    letter-spacing: -0.5px;
    margin-bottom: 0.25rem;
}

.nav-link {
    color: rgba(255,255,255,0.8) !important;
    padding: 0.75rem 1.5rem;
    margin: 0.25rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    display: flex;
    align-items: center;
}

/* ... (tous les autres styles restent exactement les mêmes) ... */

/* ================================================== */
/* RESPONSIVE DESIGN - Inchangé */
/* ================================================== */

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
}

/* ================================================== */
/* BARRE DE DÉFILEMENT PERSONNALISÉE - Inchangé */
/* ================================================== */

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