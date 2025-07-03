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
                <!-- Fil d'ariane pour Hotel Manager -->
                <li class="breadcrumb-item">
                    <a href="{{ route('hotel.dashboard') }}" class="text-decoration-none">
                        <i class="fas fa-home me-1"></i>
                        Tableau de Bord
                    </a>
                </li>
                @if(request()->routeIs('hotel.branches*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('hotel.branches.index') }}" class="text-decoration-none">
                            Gestion des Branches
                        </a>
                    </li>
                    @if(request()->routeIs('hotel.branches.create'))
                        <li class="breadcrumb-item active">Ajouter une Branche</li>
                    @elseif(request()->routeIs('hotel.branches.edit'))
                        <li class="breadcrumb-item active">Modifier une Branche</li>
                    @elseif(request()->routeIs('hotel.branches.show'))
                        <li class="breadcrumb-item active">Détails de la Branche</li>
                    @elseif(request()->routeIs('hotel.branches.settings'))
                        <li class="breadcrumb-item active">Paramètres des Branches</li>
                    @else
                        <li class="breadcrumb-item active">Liste des Branches</li>
                    @endif
                @elseif(request()->routeIs('hotel.customer*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('hotel.customers.index') }}" class="text-decoration-none">
                            Gestion des Clients
                        </a>
                    </li>
                    @if(request()->routeIs('hotel.customers.create'))
                        <li class="breadcrumb-item active">Ajouter un Client</li>
                    @elseif(request()->routeIs('hotel.customers.edit'))
                        <li class="breadcrumb-item active">Modifier un Client</li>
                    @elseif(request()->routeIs('hotel.customers.show'))
                        <li class="breadcrumb-item active">Détails du Client</li>
                    @elseif(request()->routeIs('hotel.customer-import.create'))
                        <li class="breadcrumb-item active">Importer des Clients</li>
                    @elseif(request()->routeIs('hotel.customer-import.preview'))
                        <li class="breadcrumb-item active">Aperçu de l'Importation</li>
                    @elseif(request()->routeIs('hotel.customer-import.show'))
                        <li class="breadcrumb-item active">Détails de l'Importation</li>
                    @elseif(request()->routeIs('hotel.customer-import.index'))
                        <li class="breadcrumb-item active">Liste des Importations</li>
                    @else
                        <li class="breadcrumb-item active">Liste des Clients</li>
                    @endif
                @elseif(request()->routeIs('hotel.staff*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('hotel.staff.index') }}" class="text-decoration-none">
                            Gestion du Personnel
                        </a>
                    </li>
                    @if(request()->routeIs('hotel.staff.create'))
                        <li class="breadcrumb-item active">Ajouter un Employé</li>
                    @elseif(request()->routeIs('hotel.staff.edit'))
                        <li class="breadcrumb-item active">Modifier un Employé</li>
                    @elseif(request()->routeIs('hotel.staff.show'))
                        <li class="breadcrumb-item active">Détails de l'Employé</li>
                    @elseif(request()->routeIs('hotel.branch-managers.index'))
                        <li class="breadcrumb-item active">Gestionnaires de Branches</li>
                    @else
                        <li class="breadcrumb-item active">Liste du Personnel</li>
                    @endif
                @elseif(request()->routeIs('hotel.bookings*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('hotel.bookings.index') }}" class="text-decoration-none">
                            Réservations
                        </a>
                    </li>
                    @if(request()->routeIs('hotel.bookings.create'))
                        <li class="breadcrumb-item active">Nouvelle Réservation</li>
                    @elseif(request()->routeIs('hotel.bookings.edit'))
                        <li class="breadcrumb-item active">Modifier une Réservation</li>
                    @elseif(request()->routeIs('hotel.bookings.show'))
                        <li class="breadcrumb-item active">Détails de la Réservation</li>
                    @elseif(request()->routeIs('hotel.bookings.pending'))
                        <li class="breadcrumb-item active">Réservations en Attente</li>
                    @else
                        <li class="breadcrumb-item active">Toutes les Réservations</li>
                    @endif
                @elseif(request()->routeIs('hotel.reports*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('hotel.reports.dashboard') }}" class="text-decoration-none">
                            Rapports
                        </a>
                    </li>
                    @if(request()->routeIs('hotel.reports.branches'))
                        <li class="breadcrumb-item active">Performance par Branche</li>
                    @elseif(request()->routeIs('hotel.reports.customers'))
                        <li class="breadcrumb-item active">Analyse Clientèle</li>
                    @elseif(request()->routeIs('hotel.reports.export'))
                        <li class="breadcrumb-item active">Exporter Données</li>
                    @else
                        <li class="breadcrumb-item active">Tableau de Performance</li>
                    @endif
                @elseif(request()->routeIs('hotel.settings*'))
                    <li class="breadcrumb-item">
                        <a href="{{ route('hotel.settings.general') }}" class="text-decoration-none">
                            Paramètres
                        </a>
                    </li>
                    @if(request()->routeIs('hotel.settings.notifications'))
                        <li class="breadcrumb-item active">Notifications</li>
                    @elseif(request()->routeIs('hotel.settings.appearance'))
                        <li class="breadcrumb-item active">Apparence</li>
                    @else
                        <li class="breadcrumb-item active">Paramètres Généraux</li>
                    @endif
                @else
                    <li class="breadcrumb-item active">@yield('title', 'Tableau de Bord')</li>
                @endif
            </ol>
        </nav>
    </div>

    <!-- Côté Droit -->
    <div class="navbar-right d-flex align-items-center">
        <!-- Sélecteur de Branche -->
        <div class="branch-selector me-3 d-none d-md-block">
            <div class="input-group">
                <select class="form-select border-0 bg-light" id="branchSelector" style="border-radius: 20px;">
                    <option value="all">Toutes les branches</option>
                    <!-- Options dynamiques des branches -->
                    @foreach(auth()->user()->hotel->branches ?? [] as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
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
                    5
                    <span class="visually-hidden">notifications non lues</span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg" style="width: 350px; max-height: 400px; overflow-y: auto;">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Notifications</h6>
                    <small class="text-muted">5 non lues</small>
                </div>
                <div class="dropdown-divider"></div>
                
                <!-- Notification 1 -->
                <a href="#" class="dropdown-item py-3 border-bottom">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-user-plus text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Nouveau client enregistré</h6>
                            <p class="mb-1 text-muted small">Jean Dupont a été ajouté à la branche Paris Centre</p>
                            <small class="text-muted">Il y a 10 minutes</small>
                        </div>
                    </div>
                </a>

                <!-- Notification 2 -->
                <a href="#" class="dropdown-item py-3 border-bottom">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-calendar-check text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Nouvelle réservation</h6>
                            <p class="mb-1 text-muted small">Réservation #12345 confirmée à la branche Aéroport</p>
                            <small class="text-muted">Il y a 30 minutes</small>
                        </div>
                    </div>
                </a>

                <!-- Notification 3 -->
                <a href="#" class="dropdown-item py-3 border-bottom">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Alerte de capacité</h6>
                            <p class="mb-1 text-muted small">La branche Centre-Ville atteint 90% de capacité</p>
                            <small class="text-muted">Il y a 1 heure</small>
                        </div>
                    </div>
                </a>

                <!-- Notification 4 -->
                <a href="#" class="dropdown-item py-3 border-bottom">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Rapport hebdomadaire</h6>
                            <p class="mb-1 text-muted small">Le rapport de performance est disponible</p>
                            <small class="text-muted">Il y a 3 heures</small>
                        </div>
                    </div>
                </a>

                <!-- Notification 5 -->
                <a href="#" class="dropdown-item py-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-cog text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Mise à jour système</h6>
                            <p class="mb-1 text-muted small">Nouvelle version disponible v2.1.0</p>
                            <small class="text-muted">Il y a 5 heures</small>
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
                    3
                    <span class="visually-hidden">messages non lus</span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg" style="width: 300px;">
                <div class="dropdown-header">
                    <h6 class="mb-0">Messages</h6>
                </div>
                <div class="dropdown-divider"></div>
                
                <a href="#" class="dropdown-item py-3 border-bottom">
                    <div class="d-flex">
                        <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Sophie Dubois</h6>
                            <p class="mb-1 text-muted small">Question sur la réservation #12345...</p>
                            <small class="text-muted">Il y a 15 minutes</small>
                        </div>
                    </div>
                </a>

                <a href="#" class="dropdown-item py-3 border-bottom">
                    <div class="d-flex">
                        <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Laurent Petit</h6>
                            <p class="mb-1 text-muted small">Rapport mensuel de la branche...</p>
                            <small class="text-muted">Il y a 2 heures</small>
                        </div>
                    </div>
                </a>

                <a href="#" class="dropdown-item py-3">
                    <div class="d-flex">
                        <img src="https://via.placeholder.com/40" class="rounded-circle me-3" alt="Avatar">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold">Marie Leroy</h6>
                            <p class="mb-1 text-muted small">Demande de formation pour...</p>
                            <small class="text-muted">Il y a 5 heures</small>
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
                    <div class="fw-semibold" style="font-size: 0.9rem;">{{ auth()->user()->name ?? 'Hotel Manager' }}</div>
                    <small class="text-muted">{{ auth()->user()->hotel->name ?? 'Nom de l\'Hôtel' }}</small>
                </div>
                <i class="fas fa-chevron-down ms-2 text-muted" style="font-size: 0.8rem;"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg">
                <h6 class="dropdown-header">Gestion du compte</h6>
                <a class="dropdown-item" href="">
                    <i class="fas fa-user me-2 text-primary"></i> Mon Profil
                </a>
                <a class="dropdown-item" href="">
                    <i class="fas fa-cog me-2 text-secondary"></i> Paramètres
                </a>
                <a class="dropdown-item" href="">
                    <i class="fas fa-bell me-2 text-warning"></i> Notifications
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="">
                    <i class="fas fa-question-circle me-2 text-info"></i> Aide & Support
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                    </button>   
                </form>
            </div>
        </div>
    </div>
</nav>

<style>
/* Styles pour la navbar */
.top-navbar {
    height: 70px;
    background-color: #ffffff;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.breadcrumb {
    background: transparent;
    margin-bottom: 0;
    padding: 0;
}

.breadcrumb-item + .breadcrumb-item::before {
    color: #6c757d;
}

.breadcrumb-item a {
    color: #1e40af;
}

.breadcrumb-item.active {
    color: #6c757d;
}

.dropdown-menu {
    border: none;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.dropdown-header {
    background-color: #f8f9fa;
    border-radius: 10px 10px 0 0;
    padding: 10px 15px;
}

.dropdown-footer {
    background-color: #f8f9fa;
    border-radius: 0 0 10px 10px;
    padding: 10px;
}

.dropdown-item {
    padding: 10px 15px;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: rgba(30, 64, 175, 0.05);
}

.dropdown-item:active {
    background-color: rgba(30, 64, 175, 0.1);
}

/* Style pour le sélecteur de branche */
#branchSelector {
    padding: 0.375rem 2rem 0.375rem 1rem;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 16px 12px;
    appearance: none;
}

/* Responsive */
@media (max-width: 768px) {
    .top-navbar {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutForm = document.getElementById('logout-form');
    
    if (logoutForm) {
        logoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Soumettre le formulaire via AJAX
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .finally(function() {
                // Forcer la redirection vers login quelle que soit la réponse
                window.location.href = '/login';
            });
        });
    }
});
</script>

