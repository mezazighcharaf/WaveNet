<?php
session_start();
$error = null;
if (isset($_SESSION['register_error'])) {
    echo '<div class="alert alert-danger" style="color:red;">' . htmlspecialchars($_SESSION['register_error']) . '</div>';
    unset($_SESSION['register_error']);
}
try {
    require_once __DIR__ . '/../../views/includes/config.php';
    $db = connectDB();
    require_once __DIR__ . '/../../models/Quartier.php';
    $quartiers = Quartier::getAll($db);
} catch (Exception $e) {
    $error = "Erreur de connexion à la base de données: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription | <?php echo SITE_NAME; ?></title>
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
        <form class="report-form" method="post" action="/WaveNet/controller/UserController.php?action=register">
            <h2>Créer un compte</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom">
            </div>
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="quartier_id">Quartier</label>
                <select id="quartier_id" name="quartier_id">
                    <option value="">Sélectionnez votre quartier</option>
                    <?php if (!empty($quartiers)): ?>
                        <?php foreach ($quartiers as $quartier): ?>
                            <option value="<?php echo $quartier->getId(); ?>"><?php echo htmlspecialchars($quartier->getNom()); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            <button type="submit" class="btn btn-primary">S'inscrire</button>
            <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
            <p><a href="/">Retour à l'accueil</a></p>
        </form>
    </section>
</body>
</html>
