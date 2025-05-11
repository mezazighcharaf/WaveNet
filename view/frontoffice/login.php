<?php
session_start();

// Vérifier si le formulaire a été soumis
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inclure le contrôleur d'authentification
    require_once __DIR__ . '/../../controller/AuthController.php';
    $authController = new AuthController();
    
    // Récupérer les données du formulaire
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Tenter la connexion
    $result = $authController->login($email, $password);
    
    if ($result['success']) {
        // Connexion réussie, rediriger vers la page d'accueil
        header('Location: index.php');
        exit;
    } else {
        // Afficher le message d'erreur
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Urbaverse</title>
    <link rel="stylesheet" href="/Projet_Web/assets/css/frontoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="logo-container">
        <img src="/Projet_Web/assets/img/logo.jpg" alt="Urbaverse Logo">
        <h1 class="logo-title--white">Urbaverse</h1>
    </div>
    
    <div class="login-links">
        <a href="../../index.php">Accueil</a>
        <a href="defis.php">Défis</a>
        <a href="../backoffice/dashboard/index.php">Backoffice</a>
    </div>
    
    <div class="login-form">
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="submit-btn">Se connecter</button>
        </form>
    </div>
</body>
</html> 