<?php
// Vérifier si l'ID utilisateur temporaire existe (après la première étape d'authentification)
if (!isset($_SESSION['temp_user_id'])) {
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

// Récupérer le titre du site depuis la configuration
require_once __DIR__ . '/../includes/config.php';
$pageTitle = "Vérification en deux étapes";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: var(--light-green);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .verification-container {
            max-width: 450px;
            width: 100%;
            padding: 0 1rem;
        }
        .verification-card {
            background-color: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            padding: 2rem;
            transition: transform var(--transition-speed);
        }
        .verification-card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .card-header h3 {
            color: var(--accent-green);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .otp-input {
            width: 100%;
            padding: 1rem;
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 4px;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            transition: all var(--transition-speed);
        }
        .otp-input:focus {
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
            outline: none;
        }
        .icon-shield {
            font-size: 3rem;
            color: var(--accent-green);
            margin-bottom: 1rem;
        }
        .logo-container {
            text-align: center;
            margin-top: 2rem;
        }
        .logo-container img {
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <div class="card-header">
                <i class="fas fa-shield-alt icon-shield"></i>
                <h3><?php echo $pageTitle; ?></h3>
                <p>Veuillez entrer le code à 6 chiffres généré par votre application d'authentification</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-danger">
                    <p><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                </div>
            <?php endif; ?>
            
            <form action="/WaveNet/controller/UserController.php?action=verifier2FA" method="post" autocomplete="off">
                <input type="text" name="code_verification" class="otp-input" 
                       placeholder="000000" required autofocus minlength="6" maxlength="6" pattern="[0-9]*">
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-check-circle"></i> Vérifier
                </button>
            </form>
            
            <div style="margin-top: 1.5rem; text-align: center; color: var(--gray-500);">
                <small>
                    <i class="fas fa-info-circle"></i>
                    Si vous avez perdu l'accès à votre application d'authentification, veuillez contacter l'administrateur.
                </small>
            </div>
            
            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="/WaveNet/controller/UserController.php?action=logout" class="backlink">
                    <i class="fas fa-arrow-left"></i> Retour à la page de connexion
                </a>
            </div>
        </div>
        
        <div class="logo-container">
            <img src="/WaveNet/assets/images/logo.png" alt="<?php echo SITE_NAME; ?>">
        </div>
    </div>
</body>
</html> 