<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créez votre mot de passe - <?php echo e($hotelName); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .hotel-name {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        .greeting {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .cta-button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .cta-button:hover {
            background-color: #2980b9;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .security-info {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            font-size: 14px;
            color: #7f8c8d;
            text-align: center;
        }
        .url-fallback {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            word-break: break-all;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">CollectToPay</div>
            <div class="hotel-name"><?php echo e($hotelName); ?></div>
        </div>

        <div class="greeting">
            Bonjour <?php echo e($firstName); ?> <?php echo e($lastName); ?>,
        </div>

        <div class="content">
            <p>Bienvenue dans notre système de gestion hôtelière ! Votre compte a été créé avec succès.</p>
            
            <p>Pour finaliser la configuration de votre compte, vous devez créer votre mot de passe personnel en cliquant sur le bouton ci-dessous :</p>
        </div>

        <div class="button-container">
            <a href="<?php echo e($setPasswordUrl); ?>" class="cta-button">
                Créer mon mot de passe
            </a>
        </div>

        <div class="security-info">
            <strong>🔒 Informations de sécurité :</strong>
            <ul>
                <li>Ce lien est valide pendant <strong>24 heures</strong></li>
                <li>Une fois votre mot de passe créé, ce lien ne sera plus utilisable</li>
                <li>Choisissez un mot de passe fort (minimum 8 caractères)</li>
            </ul>
        </div>

        <div class="content">
            <p><strong>Si le bouton ne fonctionne pas</strong>, copiez et collez cette adresse dans votre navigateur :</p>
            <div class="url-fallback">
                <?php echo e($setPasswordUrl); ?>

            </div>
        </div>

        <div class="content">
            <p>Une fois votre mot de passe créé, vous pourrez vous connecter à votre espace client et accéder à tous nos services.</p>
            
            <p>Si vous n'avez pas demandé la création de ce compte, vous pouvez ignorer cet email en toute sécurité.</p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement par le système CollectToPay.</p>
            <p><?php echo e($hotelName); ?> - Système de gestion hôtelière</p>
            <p><small>Pour toute question, contactez l'administration de votre hôtel.</small></p>
        </div>
    </div>
</body>
</html><?php /**PATH C:\xampp\htdocs\CollectToPay\resources\views/emails/set-password.blade.php ENDPATH**/ ?>