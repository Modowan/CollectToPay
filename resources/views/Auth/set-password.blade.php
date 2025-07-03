<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer votre mot de passe - CollectToPay</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
            --text-muted: #6b7280;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="200" cy="200" r="100" fill="url(%23a)"/><circle cx="800" cy="300" r="150" fill="url(%23a)"/><circle cx="400" cy="700" r="120" fill="url(%23a)"/><circle cx="900" cy="800" r="80" fill="url(%23a)"/></svg>');
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .password-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 480px;
            padding: 0;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
            position: relative;
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

        .password-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 40px 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .password-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }

        .password-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .password-header p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }

        .password-header .icon {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .password-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: var(--light-color);
            position: relative;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: white;
            outline: none;
        }

        .password-input-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
            background: rgba(37, 99, 235, 0.1);
        }

        .password-strength {
            margin-top: 12px;
        }

        .strength-meter {
            height: 6px;
            background: var(--border-color);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 3px;
        }

        .strength-weak { background: var(--danger-color); width: 25%; }
        .strength-fair { background: var(--warning-color); width: 50%; }
        .strength-good { background: var(--primary-color); width: 75%; }
        .strength-strong { background: var(--success-color); width: 100%; }

        .strength-text {
            font-size: 12px;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .requirements {
            margin-top: 16px;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            margin-bottom: 6px;
            transition: all 0.2s ease;
        }

        .requirement.met {
            color: var(--success-color);
        }

        .requirement.unmet {
            color: var(--text-muted);
        }

        .requirement i {
            width: 16px;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-loading {
            position: relative;
            color: transparent;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 14px;
        }

        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        /* Success Modal - HIDDEN BY DEFAULT */
        .success-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: none; /* IMPORTANT: Hidden by default */
            align-items: center;
            justify-content: center;
        }

        .success-modal.show {
            display: flex; /* Only show when .show class is added */
        }

        .success-modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            margin: 20px;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .password-container {
                border-radius: 16px;
            }
            
            .password-header {
                padding: 30px 24px 20px;
            }
            
            .password-header h1 {
                font-size: 24px;
            }
            
            .password-body {
                padding: 24px;
            }
        }

        /* Animation for form validation */
        .shake {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Success animation */
        .success-checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 2;
            stroke: var(--success-color);
            stroke-miterlimit: 10;
            margin: 20px auto;
            box-shadow: inset 0px 0px 0px var(--success-color);
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }

        .success-checkmark__circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 2;
            stroke-miterlimit: 10;
            stroke: var(--success-color);
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .success-checkmark__check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes scale {
            0%, 100% {
                transform: none;
            }
            50% {
                transform: scale3d(1.1, 1.1, 1);
            }
        }

        @keyframes fill {
            100% {
                box-shadow: inset 0px 0px 0px 30px var(--success-color);
            }
        }
    </style>
</head>
<body>
    <div class="password-container">
        <!-- Header -->
        <div class="password-header">
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <h1>Créer votre mot de passe</h1>
            <p>Sécurisez votre compte avec un mot de passe fort</p>
        </div>

        <!-- Body -->
        <div class="password-body">
            <!-- Success Message (hidden by default) -->
            <div id="successMessage" class="alert alert-success" style="display: none;">
                <i class="fas fa-check-circle"></i>
                <span>Votre mot de passe a été créé avec succès !</span>
            </div>

            <!-- Error Message (hidden by default) -->
            <div id="errorMessage" class="alert alert-danger" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorText">Une erreur s'est produite</span>
            </div>

            <!-- Form -->
            <form id="passwordForm" method="POST" action="{{ route('set-password.submit', $token ?? 'TOKEN_PLACEHOLDER') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token ?? 'TOKEN_PLACEHOLDER' }}">
                <input type="hidden" name="email" value="{{ $email ?? 'EMAIL_PLACEHOLDER' }}">

                <!-- User Info -->
                <div class="form-group">
                    <div class="alert" style="background: rgba(37, 99, 235, 0.1); color: var(--primary-color); border-left: 4px solid var(--primary-color);">
                        <i class="fas fa-user"></i>
                        <div>
                            <strong>Compte :</strong> {{ $email ?? 'Votre email' }}<br>
                            <small>Créez un mot de passe sécurisé pour votre compte</small>
                        </div>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Nouveau mot de passe
                    </label>
                    <div class="password-input-group">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Entrez votre mot de passe"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                    
                    <!-- Password Strength Meter -->
                    <div class="password-strength">
                        <div class="strength-meter">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text">
                            <span id="strengthText">Entrez un mot de passe</span>
                            <span id="strengthScore"></span>
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="requirements">
                        <div class="requirement unmet" id="req-length">
                            <i class="fas fa-circle"></i>
                            <span>Au moins 8 caractères</span>
                        </div>
                        <div class="requirement unmet" id="req-uppercase">
                            <i class="fas fa-circle"></i>
                            <span>Une lettre majuscule</span>
                        </div>
                        <div class="requirement unmet" id="req-lowercase">
                            <i class="fas fa-circle"></i>
                            <span>Une lettre minuscule</span>
                        </div>
                        <div class="requirement unmet" id="req-number">
                            <i class="fas fa-circle"></i>
                            <span>Un chiffre</span>
                        </div>
                        <div class="requirement unmet" id="req-special">
                            <i class="fas fa-circle"></i>
                            <span>Un caractère spécial (!@#$%^&*)</span>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        <i class="fas fa-lock"></i>
                        Confirmer le mot de passe
                    </label>
                    <div class="password-input-group">
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="form-control" 
                               placeholder="Confirmez votre mot de passe"
                               required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                            <i class="fas fa-eye" id="confirmToggleIcon"></i>
                        </button>
                    </div>
                    <div id="confirmMatch" class="mt-2" style="display: none;">
                        <small class="text-success">
                            <i class="fas fa-check"></i> Les mots de passe correspondent
                        </small>
                    </div>
                    <div id="confirmNoMatch" class="mt-2" style="display: none;">
                        <small class="text-danger">
                            <i class="fas fa-times"></i> Les mots de passe ne correspondent pas
                        </small>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn" class="btn btn-primary" disabled>
                    <i class="fas fa-shield-alt me-2"></i>
                    Créer mon mot de passe
                </button>
            </form>

            <!-- Footer -->
            <div class="footer-text">
                <p>
                    <i class="fas fa-shield-alt me-1"></i>
                    Vos données sont sécurisées et chiffrées
                </p>
                <p class="mt-2">
                    Besoin d'aide ? <a href="mailto:support@collecttopay.com">Contactez le support</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Success Modal - PROPERLY HIDDEN -->
    <div id="successModal" class="success-modal">
        <div class="success-modal-content">
            <svg class="success-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="success-checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="success-checkmark__check" fill="none" d="m14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
            <h3 style="color: var(--success-color); margin: 20px 0 10px;">Mot de passe créé !</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Votre compte est maintenant sécurisé. Vous allez être redirigé vers la page de connexion.</p>
            <div style="width: 100%; height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden;">
                <div id="redirectProgress" style="height: 100%; background: var(--success-color); width: 0%; transition: width 0.1s ease;"></div>
            </div>
            <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Redirection dans <span id="countdown">5</span> secondes...</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        function checkPasswordStrength(password) {
            let score = 0;

            // Length check
            if (password.length >= 8) {
                score += 1;
                document.getElementById('req-length').classList.remove('unmet');
                document.getElementById('req-length').classList.add('met');
                document.querySelector('#req-length i').className = 'fas fa-check';
            } else {
                document.getElementById('req-length').classList.remove('met');
                document.getElementById('req-length').classList.add('unmet');
                document.querySelector('#req-length i').className = 'fas fa-circle';
            }

            // Uppercase check
            if (/[A-Z]/.test(password)) {
                score += 1;
                document.getElementById('req-uppercase').classList.remove('unmet');
                document.getElementById('req-uppercase').classList.add('met');
                document.querySelector('#req-uppercase i').className = 'fas fa-check';
            } else {
                document.getElementById('req-uppercase').classList.remove('met');
                document.getElementById('req-uppercase').classList.add('unmet');
                document.querySelector('#req-uppercase i').className = 'fas fa-circle';
            }

            // Lowercase check
            if (/[a-z]/.test(password)) {
                score += 1;
                document.getElementById('req-lowercase').classList.remove('unmet');
                document.getElementById('req-lowercase').classList.add('met');
                document.querySelector('#req-lowercase i').className = 'fas fa-check';
            } else {
                document.getElementById('req-lowercase').classList.remove('met');
                document.getElementById('req-lowercase').classList.add('unmet');
                document.querySelector('#req-lowercase i').className = 'fas fa-circle';
            }

            // Number check
            if (/[0-9]/.test(password)) {
                score += 1;
                document.getElementById('req-number').classList.remove('unmet');
                document.getElementById('req-number').classList.add('met');
                document.querySelector('#req-number i').className = 'fas fa-check';
            } else {
                document.getElementById('req-number').classList.remove('met');
                document.getElementById('req-number').classList.add('unmet');
                document.querySelector('#req-number i').className = 'fas fa-circle';
            }

            // Special character check
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                score += 1;
                document.getElementById('req-special').classList.remove('unmet');
                document.getElementById('req-special').classList.add('met');
                document.querySelector('#req-special i').className = 'fas fa-check';
            } else {
                document.getElementById('req-special').classList.remove('met');
                document.getElementById('req-special').classList.add('unmet');
                document.querySelector('#req-special i').className = 'fas fa-circle';
            }

            // Update strength meter
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            const strengthScore = document.getElementById('strengthScore');

            strengthFill.className = 'strength-fill';
            
            if (score === 0) {
                strengthText.textContent = 'Entrez un mot de passe';
                strengthScore.textContent = '';
            } else if (score <= 2) {
                strengthFill.classList.add('strength-weak');
                strengthText.textContent = 'Faible';
                strengthScore.textContent = score + '/5';
            } else if (score <= 3) {
                strengthFill.classList.add('strength-fair');
                strengthText.textContent = 'Moyen';
                strengthScore.textContent = score + '/5';
            } else if (score <= 4) {
                strengthFill.classList.add('strength-good');
                strengthText.textContent = 'Bon';
                strengthScore.textContent = score + '/5';
            } else {
                strengthFill.classList.add('strength-strong');
                strengthText.textContent = 'Excellent';
                strengthScore.textContent = score + '/5';
            }

            return score;
        }

        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId === 'password' ? 'passwordToggleIcon' : 'confirmToggleIcon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Check if passwords match
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirmation').value;
            const matchDiv = document.getElementById('confirmMatch');
            const noMatchDiv = document.getElementById('confirmNoMatch');

            if (confirm.length === 0) {
                matchDiv.style.display = 'none';
                noMatchDiv.style.display = 'none';
                return false;
            }

            if (password === confirm) {
                matchDiv.style.display = 'block';
                noMatchDiv.style.display = 'none';
                return true;
            } else {
                matchDiv.style.display = 'none';
                noMatchDiv.style.display = 'block';
                return false;
            }
        }

        // Validate form
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirmation').value;
            const submitBtn = document.getElementById('submitBtn');

            const strengthScore = checkPasswordStrength(password);
            const passwordsMatch = checkPasswordMatch();

            if (strengthScore >= 4 && passwordsMatch && password.length >= 8) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        // Show success modal with countdown
        function showSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.classList.add('show'); // Use class instead of style
            
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            const progressElement = document.getElementById('redirectProgress');
            
            const timer = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                progressElement.style.width = ((5 - countdown) / 5 * 100) + '%';
                
                if (countdown <= 0) {
                    clearInterval(timer);
                    // Redirect to login page
                    window.location.href = '/login';
                }
            }, 1000);
        }

        // Event listeners
        document.getElementById('password').addEventListener('input', validateForm);
        document.getElementById('password_confirmation').addEventListener('input', validateForm);

        // Form submission
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
            
            // Hide any existing messages
            document.getElementById('successMessage').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'none';
            
            // Simulate form submission (replace with actual AJAX call)
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success modal
                    showSuccessModal();
                } else {
                    throw new Error(data.message || 'Une erreur s\'est produite');
                }
            })
            .catch(error => {
                // Show error message
                document.getElementById('errorText').textContent = error.message;
                document.getElementById('errorMessage').style.display = 'block';
                
                // Shake the form
                document.querySelector('.password-container').classList.add('shake');
                setTimeout(() => {
                    document.querySelector('.password-container').classList.remove('shake');
                }, 500);
            })
            .finally(() => {
                // Reset button
                submitBtn.classList.remove('btn-loading');
                submitBtn.innerHTML = originalText;
                validateForm(); // Re-enable if form is still valid
            });
        });

        // Initialize form validation on page load
        document.addEventListener('DOMContentLoaded', function() {
            validateForm();
            
            // Debug: Log modal state on load
            console.log('Page loaded, modal display:', document.getElementById('successModal').style.display);
            console.log('Modal classes:', document.getElementById('successModal').className);
        });
    </script>
</body>
</html>

