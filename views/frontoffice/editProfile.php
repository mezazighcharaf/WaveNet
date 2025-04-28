<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: /WaveNet/views/frontoffice/login.php");
    exit;
}
$pageTitle = 'Modifier mon profil';
$activePage = 'profile';
require_once '../../views/includes/config.php';
$db = connectDB();
if (!$db) {
    error_log("Erreur: Impossible d'établir une connexion à la base de données.");
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}
require_once '../../models/Utilisateur.php';
require_once '../../models/Quartier.php';
$userId = $_SESSION['user_id'];
try {
    $userDbData = Utilisateur::findById($db, $userId);
    if (!$userDbData) {
        $_SESSION = array();
        session_destroy();
        header("Location: /WaveNet/views/frontoffice/login.php?error=user_not_found");
        exit;
    }
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des données utilisateur: " . $e->getMessage());
    die("Une erreur est survenue lors de la récupération de vos données.");
}
try {
    $quartiers = [];
    $stmt = $db->query("SELECT id_quartier, nom_quartier as nom FROM QUARTIER ORDER BY nom_quartier");
    $quartiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erreur lors de la récupération des quartiers: " . $e->getMessage());
}
$errorMessages = [];
if (isset($_SESSION['error_messages'])) {
    $errorMessages = $_SESSION['error_messages'];
    unset($_SESSION['error_messages']);
}
$successMessage = '';
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
require_once '../includes/userHeader.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | WaveNet</title>
    <link rel="stylesheet" href="../../views/assets/css/style11.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Élément de fond -->
    <div class="page-background"></div>
    <!-- HERO SECTION -->
    <section class="hero" style="min-height: 30vh;">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Modifier mon <span style="color: var(--accent-green);">profil</span></h1>
                <p class="hero-text">Mettez à jour vos informations personnelles et personnalisez votre expérience sur WaveNet.</p>
            </div>
            <div class="hero-image-container">
                <img src="../assets/img/profile-edit.jpg" alt="Modification du profil" class="hero-image">
            </div>
        </div>
    </section>
    <div class="container" style="margin-top: -3rem; position: relative; z-index: 10; margin-bottom: 3rem;">
        <div style="max-width: 800px; margin: 0 auto; background-color: var(--white); border-radius: var(--border-radius-lg); box-shadow: var(--shadow-md); padding: 2rem;">
            <!-- Messages d'erreur et de succès -->
            <div id="profile-message" style="margin-bottom: 2rem; display: none;"></div>
            <?php if (!empty($errorMessages)): ?>
                <div style="padding: 1rem; background-color: rgba(244, 67, 54, 0.1); border-left: 4px solid #d32f2f; margin-bottom: 2rem; border-radius: var(--border-radius);">
                    <h3 style="color: #d32f2f; font-size: 1.1rem; margin-bottom: 0.5rem;">Des erreurs sont survenues</h3>
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ((array)$errorMessages as $error): ?>
                            <li style="color: #d32f2f;"><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <div style="padding: 1rem; background-color: rgba(76, 175, 80, 0.1); border-left: 4px solid #2e7d32; margin-bottom: 2rem; border-radius: var(--border-radius);">
                    <h3 style="color: #2e7d32; font-size: 1.1rem; margin-bottom: 0.5rem;">Succès</h3>
                    <p style="color: #2e7d32; margin: 0;"><?= htmlspecialchars($successMessage) ?></p>
                </div>
            <?php endif; ?>
            <form id="edit-profile-form" method="post" action="/WaveNet/controller/UserController.php?action=updateProfile">
                <div style="margin-bottom: 2rem;">
                    <h2 style="font-size: 1.5rem; color: var(--dark-green); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--gray-200);">
                        <i class="fas fa-user-circle" style="margin-right: 0.5rem;"></i>Informations personnelles
                    </h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                        <div>
                            <label for="nom" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-color);">Nom</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($userDbData->getNom()) ?>" required 
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--gray-300); border-radius: var(--border-radius); font-size: 1rem; transition: all var(--transition-speed);">
                        </div>
                        <div>
                            <label for="prenom" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-color);">Prénom</label>
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($userDbData->getPrenom()) ?>" required 
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--gray-300); border-radius: var(--border-radius); font-size: 1rem; transition: all var(--transition-speed);">
                        </div>
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <label for="email" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-color);">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($userDbData->getEmail()) ?>" required 
                            style="width: 100%; padding: 0.8rem; border: 1px solid var(--gray-300); border-radius: var(--border-radius); font-size: 1rem; transition: all var(--transition-speed);">
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <label for="id_quartier" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-color);">Quartier</label>
                        <select id="id_quartier" name="id_quartier" 
                            style="width: 100%; padding: 0.8rem; border: 1px solid var(--gray-300); border-radius: var(--border-radius); font-size: 1rem; transition: all var(--transition-speed); appearance: none; background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%232e4f3e\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpolyline points=\'6 9 12 15 18 9\'%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1em; padding-right: 2.5rem;">
                            <option value="">Sélectionnez un quartier</option>
                            <?php foreach ($quartiers as $quartier): ?>
                                <option value="<?= $quartier['id_quartier'] ?>" <?= $userDbData->getIdQuartier() == $quartier['id_quartier'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($quartier['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <!-- Modification du mot de passe -->
                <div style="margin-bottom: 2rem;">
                    <h2 style="font-size: 1.5rem; color: var(--dark-green); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--gray-200);">
                        <i class="fas fa-lock" style="margin-right: 0.5rem;"></i>Modifier le mot de passe
                    </h2>
                    <p style="color: var(--gray-500); margin-bottom: 1.5rem;">
                        Laissez les champs vides si vous ne souhaitez pas changer votre mot de passe.
                    </p>
                    <div>
                        <label for="current_password" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-color);">Mot de passe actuel</label>
                        <input type="password" id="current_password" name="current_password" 
                            style="width: 100%; padding: 0.8rem; border: 1px solid var(--gray-300); border-radius: var(--border-radius); font-size: 1rem; transition: all var(--transition-speed);">
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                        <div>
                            <label for="new_password" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-color);">Nouveau mot de passe</label>
                            <input type="password" id="new_password" name="new_password" 
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--gray-300); border-radius: var(--border-radius); font-size: 1rem; transition: all var(--transition-speed);">
                        </div>
                        <div>
                            <label for="confirm_password" style="display: block; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-color);">Confirmer le mot de passe</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                style="width: 100%; padding: 0.8rem; border: 1px solid var(--gray-300); border-radius: var(--border-radius); font-size: 1rem; transition: all var(--transition-speed);">
                        </div>
                    </div>
                </div>
                <!-- Préférences de notification (optionnel, à développer plus tard) -->
                <div style="margin-bottom: 2rem;">
                    <h2 style="font-size: 1.5rem; color: var(--dark-green); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--gray-200);">
                        <i class="fas fa-bell" style="margin-right: 0.5rem;"></i>Préférences de notification
                    </h2>
                    <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                        <input type="checkbox" id="newsletter" name="newsletter" value="1" <?= ($userDbData->getNewsletter() ?? 0) ? 'checked' : '' ?> 
                            style="width: 18px; height: 18px; margin-right: 0.75rem; cursor: pointer;">
                        <label for="newsletter" style="margin: 0; cursor: pointer;">Recevoir la newsletter mensuelle</label>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <input type="checkbox" id="evenements" name="evenements" value="1" <?= ($userDbData->getEvenements() ?? 0) ? 'checked' : '' ?> 
                            style="width: 18px; height: 18px; margin-right: 0.75rem; cursor: pointer;">
                        <label for="evenements" style="margin: 0; cursor: pointer;">Être notifié des événements à venir dans mon quartier</label>
                    </div>
                </div>
                <!-- Boutons d'action -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
                    <a href="/WaveNet/views/frontoffice/userDashboard.php" style="color: var(--text-color); text-decoration: none; display: inline-flex; align-items: center;">
                        <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i>Retour au tableau de bord
                    </a>
                    <div>
                        <button type="reset" style="background-color: var(--white); color: var(--text-color); border: 1px solid var(--gray-300); padding: 0.75rem 1.25rem; border-radius: var(--border-radius); margin-right: 1rem; cursor: pointer; transition: all var(--transition-speed);">
                            Annuler
                        </button>
                        <button type="submit" style="background-color: var(--accent-green); color: var(--white); border: none; padding: 0.75rem 1.25rem; border-radius: var(--border-radius); font-weight: 600; cursor: pointer; transition: all var(--transition-speed);">
                            Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
    $additionalScripts = <<<EOT
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('edit-profile-form');
            const messageDiv = document.getElementById('profile-message');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    let errors = [];
                    const nom = document.getElementById('nom').value.trim();
                    const prenom = document.getElementById('prenom').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const currentPassword = document.getElementById('current_password').value;
                    const newPassword = document.getElementById('new_password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    if (nom === '') {
                        errors.push('Le nom est requis');
                        isValid = false;
                    }
                    if (prenom === '') {
                        errors.push('Le prénom est requis');
                        isValid = false;
                    }
                    if (email === '') {
                        errors.push('L\'email est requis');
                        isValid = false;
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        errors.push('L\'email n\'est pas valide');
                        isValid = false;
                    }
                    if (newPassword !== '' || confirmPassword !== '') {
                        if (currentPassword === '') {
                            errors.push('Le mot de passe actuel est requis pour changer votre mot de passe');
                            isValid = false;
                        }
                        if (newPassword.length < 8) {
                            errors.push('Le nouveau mot de passe doit contenir au moins 8 caractères');
                            isValid = false;
                        }
                        if (newPassword !== confirmPassword) {
                            errors.push('Les mots de passe ne correspondent pas');
                            isValid = false;
                        }
                    }
                    if (!isValid) {
                        e.preventDefault();
                        showError(errors.join('<br>'));
                    }
                });
            }
            function showError(message) {
                messageDiv.innerHTML = `<div style="padding: 1rem; background-color: rgba(244, 67, 54, 0.1); border-left: 4px solid #d32f2f; margin-bottom: 1rem; border-radius: var(--border-radius);">
                    <h3 style="color: #d32f2f; font-size: 1.1rem; margin-bottom: 0.5rem;">Erreur</h3>
                    <div style="color: #d32f2f;">\${message}</div>
                </div>`;
                messageDiv.style.display = 'block';
                window.scrollTo({
                    top: messageDiv.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    </script>
    EOT;
    require_once '../includes/footer.php';
    ?>
</body>
</html> 
