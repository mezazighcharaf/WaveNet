<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { 
    session_start();
}

// Récupération du token
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['error_message'] = "Aucun token de vérification fourni.";
    header('Location: /WaveNet/views/frontoffice/userDashboard.php');
    exit;
}

// Connexion à la base de données
require_once '../../views/includes/config.php';
$db = connectDB();

if (!$db) {
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}

// Vérifier si le token existe et n'est pas expiré
$verified = false;
$error = '';

try {
    // Vérifier si la table email_verification existe
    $tableCheck = $db->query("SHOW TABLES LIKE 'email_verification'");
    if ($tableCheck->rowCount() === 0) {
        throw new Exception("Système de vérification d'email non configuré.");
    }
    
    // Vérifier le token
    $stmt = $db->prepare("SELECT id_utilisateur, expires_at FROM email_verification WHERE token = ?");
    $stmt->execute([$token]);
    $verification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$verification) {
        throw new Exception("Token de vérification invalide ou déjà utilisé.");
    }
    
    // Vérifier si le token a expiré
    $expiresAt = new DateTime($verification['expires_at']);
    $now = new DateTime();
    
    if ($now > $expiresAt) {
        // Supprimer le token expiré
        $db->prepare("DELETE FROM email_verification WHERE token = ?")->execute([$token]);
        throw new Exception("Le lien de vérification a expiré. Veuillez demander un nouveau lien.");
    }
    
    // Marquer l'email comme vérifié
    $userId = $verification['id_utilisateur'];
    $stmt = $db->prepare("UPDATE UTILISATEUR SET email_verified = 1 WHERE id_utilisateur = ?");
    $result = $stmt->execute([$userId]);
    
    if (!$result) {
        throw new Exception("Erreur lors de la vérification de votre email. Veuillez réessayer.");
    }
    
    // Supprimer le token utilisé
    $db->prepare("DELETE FROM email_verification WHERE token = ?")->execute([$token]);
    
    $verified = true;
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Préparer le titre de la page
$pageTitle = $verified ? 'Email vérifié' : 'Erreur de vérification';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | WaveNet</title>
    <link rel="stylesheet" href="../../views/assets/css/style11.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .verification-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: var(--white);
            box-shadow: var(--shadow-md);
            border-radius: var(--border-radius);
            text-align: center;
        }
        .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 20px;
            font-size: 40px;
        }
        .success-icon {
            background-color: rgba(72, 187, 120, 0.1);
            color: #48BB78;
        }
        .error-icon {
            background-color: rgba(245, 101, 101, 0.1);
            color: #F56565;
        }
        .verification-title {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--dark-green);
        }
        .verification-message {
            margin-bottom: 25px;
            color: var(--text-color);
        }
        .btn-primary {
            display: inline-block;
            background-color: var(--accent-green);
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #3a9d6e;
        }
    </style>
</head>
<body>
    <!-- Élément de fond -->
    <div class="page-background"></div>
    
    <div class="verification-container">
        <?php if ($verified): ?>
            <div class="icon-circle success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1 class="verification-title">Email vérifié avec succès !</h1>
            <p class="verification-message">
                Votre adresse email a été vérifiée avec succès. Vous pouvez maintenant profiter pleinement de toutes les fonctionnalités de votre compte WaveNet.
            </p>
            <a href="/WaveNet/views/frontoffice/userDashboard.php" class="btn-primary">
                <i class="fas fa-home"></i> Retour au tableau de bord
            </a>
        <?php else: ?>
            <div class="icon-circle error-icon">
                <i class="fas fa-times"></i>
            </div>
            <h1 class="verification-title">Erreur de vérification</h1>
            <p class="verification-message">
                <?php echo htmlspecialchars($error); ?>
            </p>
            <a href="/WaveNet/views/frontoffice/account_activity.php" class="btn-primary">
                <i class="fas fa-arrow-left"></i> Retour à l'activité du compte
            </a>
        <?php endif; ?>
    </div>
</body>
</html> 