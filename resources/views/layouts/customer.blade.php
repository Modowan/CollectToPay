<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Client') - CollectToPay</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 250px;
            --navbar-height: 60px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 15px;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white !important;
        }

        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Navbar */
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 30px;
            margin-bottom: 0;
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
        }

        /* Content Area */
        .content-wrapper {
            padding: 30px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content-wrapper {
                padding: 15px;
            }
        }

        /* Profile Progress */
        .progress-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: conic-gradient(var(--primary-color) 0deg, var(--primary-color) 0deg, #e9ecef 0deg, #e9ecef 360deg);
            position: relative;
        }

        .progress-circle-inner {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <h4>
                <i class="fas fa-hotel me-2"></i>
                Client Portal
            </h4>
            @if(session('customer_auth'))
                @php $customer = session('customer_auth'); @endphp
                <small class="d-block mt-2 opacity-75">
                    {{ $customer['tenant_name'] ?? 'Hôtel' }}
                </small>
            @endif
        </div>

        <!-- Sidebar Navigation -->
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('customer.dashboard') }}" 
                       class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Tableau de Bord</span>
                    </a>
                </li>

                <!-- Mon Profil - NOUVEAU LIEN -->
                <li class="nav-item">
                    <a href="{{ route('customer.profile') }}" 
                       class="nav-link {{ request()->routeIs('customer.profile*') ? 'active' : '' }}">
                        <i class="fas fa-user"></i>
                        <span>Mon Profil</span>
                        @if(session('customer_auth'))
                            @php 
                                $customer = session('customer_auth');
                                // Simuler un pourcentage de completion (à remplacer par la vraie logique)
                                $completion = 45; // Exemple
                            @endphp
                            @if($completion < 100)
                                <span class="badge bg-warning ms-auto">{{ $completion }}%</span>
                            @else
                                <span class="badge bg-success ms-auto">
                                    <i class="fas fa-check"></i>
                                </span>
                            @endif
                        @endif
                    </a>
                </li>

                <!-- Mes Paiements -->
                <li class="nav-item">
                    <a href="{{ route('customer.payments') }}" 
                       class="nav-link {{ request()->routeIs('customer.payments') ? 'active' : '' }}">
                        <i class="fas fa-credit-card"></i>
                        <span>Mes Paiements</span>
                    </a>
                </li>

                <!-- Historique -->
                <li class="nav-item">
                    <a href="{{ route('customer.history') }}" 
                       class="nav-link {{ request()->routeIs('customer.history') ? 'active' : '' }}">
                        <i class="fas fa-history"></i>
                        <span>Historique</span>
                    </a>
                </li>

                <!-- Divider -->
                <li class="nav-item">
                    <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">
                </li>

                <!-- Paramètres -->
                <li class="nav-item">
                    <a href="{{ route('customer.settings') }}" 
                       class="nav-link {{ request()->routeIs('customer.settings') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span>Paramètres</span>
                    </a>
                </li>

                <!-- Support -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-question-circle"></i>
                        <span>Support</span>
                    </a>
                </li>

                <!-- Déconnexion -->
                <li class="nav-item mt-4">
                    <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Déconnexion</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg top-navbar">
            <div class="container-fluid">
                <!-- Mobile Menu Toggle -->
                <button class="btn btn-outline-primary d-lg-none me-3" type="button" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Page Title -->
                <div class="navbar-brand">
                    @yield('page-title', 'Dashboard Client')
                </div>

                <!-- Right Side -->
                <div class="ms-auto d-flex align-items-center">
                    <!-- Notifications -->
                    <div class="dropdown me-3">
                        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                3
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><a class="dropdown-item" href="#">Nouveau message</a></li>
                            <li><a class="dropdown-item" href="#">Paiement confirmé</a></li>
                            <li><a class="dropdown-item" href="#">Profil à compléter</a></li>
                        </ul>
                    </div>

                    <!-- User Info -->
                    @if(session('customer_auth'))
                        @php $customer = session('customer_auth'); @endphp
                        <div class="d-flex align-items-center">
                            <div class="me-3 text-end d-none d-md-block">
                                <div class="fw-semibold">{{ $customer['name'] ?? 'Client' }}</div>
                                <small class="text-muted">{{ $customer['email'] ?? '' }}</small>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <span class="d-none d-md-inline">{{ explode(' ', $customer['name'] ?? 'Client')[0] }}</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">{{ $customer['name'] ?? 'Client' }}</h6></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('customer.profile') }}">
                                        <i class="fas fa-user me-2"></i>Mon Profil
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('customer.settings') }}">
                                        <i class="fas fa-cog me-2"></i>Paramètres
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-nav').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                        <form id="logout-form-nav" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Breadcrumb -->
            @if(!request()->routeIs('customer.dashboard'))
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('customer.dashboard') }}" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    @if(request()->routeIs('customer.profile*'))
                        <li class="breadcrumb-item active">Mon Profil</li>
                    @elseif(request()->routeIs('customer.payments'))
                        <li class="breadcrumb-item active">Mes Paiements</li>
                    @elseif(request()->routeIs('customer.history'))
                        <li class="breadcrumb-item active">Historique</li>
                    @elseif(request()->routeIs('customer.settings'))
                        <li class="breadcrumb-item active">Paramètres</li>
                    @else
                        <li class="breadcrumb-item active">@yield('title', 'Page')</li>
                    @endif
                </ol>
            </nav>
            @endif

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Content -->
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = event.target.closest('[onclick="toggleSidebar()"]');
            
            if (window.innerWidth <= 768 && !sidebar.contains(event.target) && !toggleBtn) {
                sidebar.classList.remove('show');
            }
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Update profile completion progress (if available)
        function updateProfileProgress(percentage) {
            const progressCircles = document.querySelectorAll('.progress-circle');
            progressCircles.forEach(function(circle) {
                const degrees = (percentage / 100) * 360;
                let color = '#dc3545'; // Rouge
                
                if (percentage >= 91) color = '#28a745'; // Vert
                else if (percentage >= 71) color = '#007bff'; // Bleu
                else if (percentage >= 41) color = '#fd7e14'; // Orange
                
                circle.style.background = `conic-gradient(${color} ${degrees}deg, #e9ecef ${degrees}deg)`;
                
                const inner = circle.querySelector('.progress-circle-inner');
                if (inner) {
                    inner.textContent = `${percentage}%`;
                }
            });
        }

        // Load profile completion on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch profile completion percentage via AJAX
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
                    updateProfileProgress(data.completion_percentage);
                }
            })
            .catch(error => {
                console.log('Profile status not available');
            });
        });
    </script>

    @stack('scripts')
</body>
</html>

