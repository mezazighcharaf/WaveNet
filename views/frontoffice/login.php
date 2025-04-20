<?php
session_start();
if (isset($_SESSION['login_error'])) {
    echo '<div class="alert alert-danger" style="color:red;">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
    unset($_SESSION['login_error']);
}
require_once __DIR__ . '/../../views/includes/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../views/assets/css/style11.css">
    <style>
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <section class="report">
        <form class="report-form" method="post" action="/WaveNet/controller/UserController.php?action=login">
            <h2>Connexion</h2>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password">
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
            <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
            <p><a href="/">Retour à l'accueil</a></p>
        </form>
    </section>
</body>
</html>
