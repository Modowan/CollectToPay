<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'CollectToPay')); ?></title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="<?php echo e(asset('js/app.js')); ?>" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Fonction pour récupérer et utiliser les données d'authentification depuis localStorage
        document.addEventListener('DOMContentLoaded', function() {
            try {
                var storedUserData = localStorage.getItem('user_auth_data');
                if (storedUserData) {
                    var userData = JSON.parse(storedUserData);
                    console.log('Données utilisateur récupérées depuis localStorage:', userData);
                    
                    // Mettre à jour l'interface utilisateur en fonction des données d'authentification
                    updateUIWithUserData(userData);
                }
            } catch (e) {
                console.error('Erreur lors de la récupération des données utilisateur:', e);
            }
        });
        
        // Fonction pour mettre à jour l'interface utilisateur avec les données d'authentification
        function updateUIWithUserData(userData) {
            if (!userData) return;
            
            var userRole = userData.user_role;
            var userName = userData.user_name;
            
            // Masquer les liens de connexion/inscription
            var authLinks = document.querySelectorAll('.auth-links');
            authLinks.forEach(function(link) {
                link.style.display = 'none';
            });
            
            // Afficher le menu utilisateur
            var userMenu = document.getElementById('user-menu');
            if (userMenu) {
                userMenu.style.display = 'block';
                var userNameElement = document.getElementById('user-name');
                if (userNameElement) {
                    userNameElement.textContent = userName;
                }
            }
            
            // Afficher les liens de navigation selon le rôle
            if (userRole === 'admin') {
                showRoleLinks('admin');
            } else if (userRole === 'hotel_manager') {
                showRoleLinks('hotel_manager');
            } else if (userRole === 'branch_manager') {
                showRoleLinks('branch_manager');
            } else if (userRole === 'customer') {
                showRoleLinks('customer');
            }
        }
        
        // Fonction pour afficher les liens de navigation selon le rôle
        function showRoleLinks(role) {
            var roleLinks = document.querySelectorAll('.role-links-' + role);
            roleLinks.forEach(function(link) {
                link.style.display = 'block';
            });
        }
    </script>
</head>
<body>
    <div id="app">
        <!-- Bloc de débogage pour l'authentification -->
        <?php if(config('app.debug')): ?>

        <?php endif; ?>

        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="<?php echo e(url('/')); ?>">
                    <?php echo e(config('app.name', 'CollectToPay')); ?>

                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="<?php echo e(__('Toggle navigation')); ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <!-- Liens de navigation pour admin -->
                        <li class="nav-item role-links-admin" style="display: none;">
                            <a class="nav-link" href="<?php echo e(url('/admin/dashboard')); ?>"><?php echo e(__('Tableau de bord')); ?></a>
                        </li>
                        <li class="nav-item role-links-admin" style="display: none;">
                            <a class="nav-link" href="<?php echo e(url('/admin/hotels')); ?>"><?php echo e(__('Hôtels')); ?></a>
                        </li>
                        
                        <!-- Liens de navigation pour hotel_manager -->
                        <li class="nav-item role-links-hotel_manager" style="display: none;">
                            <a class="nav-link" href="<?php echo e(url('/hotel/dashboard')); ?>"><?php echo e(__('Tableau de bord')); ?></a>
                        </li>
                        <li class="nav-item role-links-hotel_manager" style="display: none;">
                            <a class="nav-link" href="<?php echo e(url('/hotel/branches')); ?>"><?php echo e(__('Succursales')); ?></a>
                        </li>
                        
                        <!-- Liens de navigation pour branch_manager -->
                        <li class="nav-item role-links-branch_manager" style="display: none;">
                            <a class="nav-link" href="<?php echo e(url('/branch/dashboard')); ?>"><?php echo e(__('Tableau de bord')); ?></a>
                        </li>
                        <li class="nav-item role-links-branch_manager" style="display: none;">
                            <a class="nav-link" href="<?php echo e(url('/branch/customers')); ?>"><?php echo e(__('Clients')); ?></a>
                        </li>
                        
                        <!-- Liens de navigation pour customer -->
                        <li class="nav-item role-links-customer" style="display: none;">
                            <a class="nav-link" href="<?php echo e(url('/customer/dashboard')); ?>"><?php echo e(__('Tableau de bord')); ?></a>
                        </li>
                        <li class="nav-item role-links-customer" style="display: none;">
                            <a class="nav-link" href="<?php echo e(url('/customer/payments')); ?>"><?php echo e(__('Paiements')); ?></a>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Menu utilisateur (caché par défaut) -->
                        <li class="nav-item dropdown" id="user-menu" style="display: none;">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <span id="user-name">Utilisateur</span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">
                                    <?php echo e(__('Mon profil')); ?>

                                </a>
                               <a href="javascript:void(0);" onclick="logout()" class="dropdown-item"><?php echo e(__('Déconnexion')); ?></a>

                                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                                    <?php echo csrf_field(); ?>
                                </form>
                            </div>
                        </li>
                        
                        <!-- Liens d'authentification (visibles par défaut) -->
                        <?php if(Route::has('login')): ?>
                            <li class="nav-item auth-links">
                                <a class="nav-link" href="<?php echo e(route('login')); ?>"><?php echo e(__('Connexion')); ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if(Route::has('register')): ?>
                            <li class="nav-item auth-links">
                                <a class="nav-link" href="<?php echo e(route('register')); ?>"><?php echo e(__('Inscription')); ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
   <script>
function logout() {
    // Effacer les données d'authentification du localStorage
    localStorage.removeItem("user_auth_data");
    console.log("Données d'authentification supprimées du localStorage");
    
    // Faire une requête au endpoint de déconnexion Laravel
    fetch('/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }).then(() => {
        // Rediriger vers la page de login
        window.location.href = "/login";
    }).catch(() => {
        // En cas d'erreur, rediriger quand même vers la page de login
        window.location.href = "/login";
    });
}
</script>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/layouts/app.blade.php ENDPATH**/ ?>