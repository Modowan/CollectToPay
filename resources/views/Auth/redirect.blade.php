<!DOCTYPE html>
<html>
<head>
    <title>Redirection...</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Utiliser setTimeout pour s'assurer que la session est bien enregistrée avant la redirection
        window.onload = function() {
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
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-right: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .container {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .loading-text {
            margin-top: 20px;
            font-size: 18px;
            color: #3498db;
        }
        .loading-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="loading-container">
            <div class="loader"></div>
            <h2>Redirection en cours...</h2>
        </div>
        <p class="loading-text">Veuillez patienter pendant que nous vous redirigeons vers votre tableau de bord.</p>
    </div>
</body>
</html>
