<!DOCTYPE html>
<html>
<head>
    <title>Déconnexion...</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Effacer les données d'authentification du localStorage
        window.onload = function() {
            // Supprimer les données d'authentification
            localStorage.removeItem('user_auth_data');
            console.log('Données d\'authentification supprimées du localStorage');
            
            // Rediriger après un court délai
            setTimeout(function() {
                window.location.href = "{{ $redirectUrl }}";
            }, 100);
        };
    </script>
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f8fafc;
        }
        .container {
            text-align: center;
        }
        .message {
            margin-top: 20px;
            font-size: 18px;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Déconnexion en cours...</h2>
        <p class="message">Veuillez patienter pendant que nous vous déconnectons.</p>
    </div>
</body>
</html>
