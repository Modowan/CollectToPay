<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lien expiré - CollectToPay</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }

        .particle:nth-child(1) { width: 60px; height: 60px; left: 15%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 80px; height: 80px; left: 25%; animation-delay: 1.5s; }
        .particle:nth-child(3) { width: 40px; height: 40px; left: 40%; animation-delay: 3s; }
        .particle:nth-child(4) { width: 100px; height: 100px; left: 55%; animation-delay: 4.5s; }
        .particle:nth-child(5) { width: 50px; height: 50px; left: 70%; animation-delay: 6s; }
        .particle:nth-child(6) { width: 70px; height: 70px; left: 85%; animation-delay: 7.5s; }

        @keyframes float {
            0%, 100% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10%, 90% { opacity: 1; }
            50% { transform: translateY(-120px) rotate(180deg); }
        }

        .container {
            position: relative;
            z-index: 2;
            max-width: 520px;
            width: 90%;
            margin: 0 auto;
        }

        .expired-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem 2rem;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .expired-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #fdcb6e, #e17055);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: clockTick 2s ease-in-out infinite;
            position: relative;
        }

        .expired-icon i {
            font-size: 2rem;
            color: white;
        }

        @keyframes clockTick {
            0%, 100% { transform: scale(1) rotate(0deg); }
            25% { transform: scale(1.05) rotate(-5deg); }
            75% { transform: scale(1.05) rotate(5deg); }
        }

        .expired-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .expired-message {
            font-size: 1.1rem;
            color: #4a5568;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .expiry-info {
            background: rgba(253, 203, 110, 0.15);
            border: 1px solid rgba(253, 203, 110, 0.3);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .expiry-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .expiry-item {
            text-align: left;
        }

        .expiry-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #e17055;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .expiry-value {
            font-size: 0.95rem;
            color: #2d3748;
            font-weight: 500;
        }

        .time-elapsed {
            background: rgba(231, 76, 60, 0.1);
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #e74c3c;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            padding: 0.875rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #fdcb6e, #e17055);
            color: white;
            box-shadow: 0 4px 15px rgba(253, 203, 110, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(253, 203, 110, 0.6);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.8);
            color: #4a5568;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-1px);
        }

        .help-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .help-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .help-steps {
            text-align: left;
            font-size: 0.9rem;
            color: #718096;
            line-height: 1.6;
        }

        .help-steps ol {
            padding-left: 1.5rem;
        }

        .help-steps li {
            margin-bottom: 0.5rem;
        }

        .contact-info {
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(116, 75, 162, 0.1);
            border-radius: 8px;
            font-size: 0.9rem;
            color: #4a5568;
        }

        .contact-info a {
            color: #764ba2;
            text-decoration: none;
            font-weight: 500;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .expired-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }

            .expired-title {
                font-size: 1.5rem;
            }

            .expired-message {
                font-size: 1rem;
            }

            .expiry-details {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .action-buttons {
                gap: 0.75rem;
            }

            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }
        }

        /* Loading animation for buttons */
        .btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn.loading::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="container">
        <div class="expired-card">
            <div class="expired-icon">
                <i class="fas fa-clock"></i>
            </div>

            <h1 class="expired-title">Lien expiré</h1>
            
            <p class="expired-message">
                Le lien de création de mot de passe que vous avez utilisé a expiré. Pour des raisons de sécurité, les liens de création de mot de passe ne sont valides que pendant 24 heures.
            </p>

            <div class="expiry-info">
                <div class="expiry-details">
                    @if(isset($created_at))
                    <div class="expiry-item">
                        <div class="expiry-label">Créé le</div>
                        <div class="expiry-value">{{ \Carbon\Carbon::parse($created_at)->format('d/m/Y à H:i') }}</div>
                    </div>
                    @endif

                    @if(isset($expires_at))
                    <div class="expiry-item">
                        <div class="expiry-label">Expiré le</div>
                        <div class="expiry-value">{{ \Carbon\Carbon::parse($expires_at)->format('d/m/Y à H:i') }}</div>
                    </div>
                    @endif
                </div>

                @if(isset($expires_at))
                <div class="time-elapsed">
                    <i class="fas fa-exclamation-circle"></i>
                    Expiré depuis {{ \Carbon\Carbon::parse($expires_at)->diffForHumans() }}
                </div>
                @endif
            </div>

            <div class="action-buttons">
                <a href="mailto:admin@collecttopay.com?subject=Demande de nouveau lien de création de mot de passe&body=Bonjour,%0D%0A%0D%0AMon lien de création de mot de passe a expiré. Pourriez-vous m'envoyer un nouveau lien ?%0D%0A%0D%0AEmail : {{ $email ?? 'votre-email@example.com' }}%0D%0A%0D%0AMerci." class="btn btn-primary">
                    <i class="fas fa-envelope"></i>
                    Demander un nouveau lien
                </a>
                
                <a href="{{ route('login') }}" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i>
                    Retour à la connexion
                </a>
            </div>

            <div class="help-section">
                <div class="help-title">Comment obtenir un nouveau lien ?</div>
                <div class="help-steps">
                    <ol>
                        <li>Contactez votre administrateur système ou l'équipe qui vous a envoyé le premier lien</li>
                        <li>Demandez un nouveau lien de création de mot de passe</li>
                        <li>Utilisez le nouveau lien dans les 24 heures suivant sa création</li>
                        <li>Créez votre mot de passe immédiatement après avoir reçu le lien</li>
                    </ol>
                </div>

                <div class="contact-info">
                    <strong>Besoin d'aide ?</strong><br>
                    Contactez notre support : <a href="mailto:support@collecttopay.com">support@collecttopay.com</a><br>
                    Ou votre administrateur système pour obtenir un nouveau lien rapidement.
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add click animation to buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!this.classList.contains('loading')) {
                    this.classList.add('loading');
                    
                    // Remove loading state after navigation
                    setTimeout(() => {
                        this.classList.remove('loading');
                    }, 2000);
                }
            });
        });

        // Auto-focus on primary action after page load
        window.addEventListener('load', function() {
            const primaryBtn = document.querySelector('.btn-primary');
            if (primaryBtn) {
                primaryBtn.focus();
            }
        });

        // Add current time display
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Add current time info if not already present
            const expiredCard = document.querySelector('.expired-card');
            if (expiredCard && !document.querySelector('.current-time')) {
                const currentTimeDiv = document.createElement('div');
                currentTimeDiv.className = 'current-time';
                currentTimeDiv.style.cssText = `
                    margin-top: 1rem;
                    padding: 0.5rem;
                    background: rgba(0, 0, 0, 0.05);
                    border-radius: 6px;
                    font-size: 0.8rem;
                    color: #718096;
                `;
                currentTimeDiv.innerHTML = `<i class="fas fa-clock"></i> Heure actuelle : ${timeString}`;
                
                const expiryInfo = document.querySelector('.expiry-info');
                if (expiryInfo) {
                    expiryInfo.appendChild(currentTimeDiv);
                }
            }
        }

        // Update time on page load
        window.addEventListener('load', updateCurrentTime);
    </script>
</body>
</html>