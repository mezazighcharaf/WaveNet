<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { 
    session_start();
}

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

// Inclusion des fichiers nécessaires
require_once '../../views/includes/config.php';
require_once '../../gestion signalement/model/signalement.php';
require_once '../../gestion signalement/controller/signalementctrl.php';

$pageTitle = 'Ajouter un signalement';
$activePage = 'signalement';

// Traitement du formulaire
$message = '';
$success = false;
$randomId = mt_rand(1000, 9999);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $signalement = new Signalement();
    
    $signalement->setIdSignalement($_POST['id_signalement']);
    $signalement->setTitre($_POST['titre']);
    $signalement->setDescription($_POST['description']);
    $signalement->setEmplacement($_POST['emplacement']);
    $signalement->setDateSignalement($_POST['date_signalement']);
    $signalement->setStatut('non traité'); // Par défaut, tout nouveau signalement est "non traité"

    $signalementc = new SignalementC(); 
    if ($signalementc->addSignalement($signalement)) {
        $success = true;
        $message = "Votre signalement a été ajouté avec succès. Nous l'examinerons dans les plus brefs délais.";
    } else {
        $message = "Une erreur s'est produite lors de l'ajout du signalement. Veuillez réessayer.";
    }
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
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-green);
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: var(--accent-green);
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        textarea.form-control {
            min-height: 150px;
        }
        .btn-primary {
            background-color: var(--accent-green);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: var(--dark-green);
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-3"><?php echo $pageTitle; ?></h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
            <?php if ($success): ?>
                <p>
                    <a href="userDashboard.php" class="btn btn-primary">Retour au tableau de bord</a>
                    <a href="viewSignalements.php" class="btn btn-secondary">Voir mes signalements</a>
                </p>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <div class="form-container">
                <form action="addSignalement.php" method="POST" id="signalementForm">
                    <input type="hidden" name="id_signalement" value="<?php echo $randomId; ?>">
                    
                    <div class="form-group">
                        <label for="titre">Titre du signalement*</label>
                        <input type="text" id="titre" name="titre" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description détaillée*</label>
                        <textarea id="description" name="description" class="form-control" required></textarea>
                        <small class="form-text text-muted">Décrivez le problème avec le plus de détails possible.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="emplacement">Emplacement*</label>
                        <input type="text" id="emplacement" name="emplacement" class="form-control" required>
                        <small class="form-text text-muted">Indiquez l'adresse ou le lieu précis du problème.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_signalement">Date du constat*</label>
                        <input type="date" id="date_signalement" name="date_signalement" class="form-control" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Envoyer le signalement</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('signalementForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Validation du titre
                const titre = document.getElementById('titre');
                if (!titre.value.trim()) {
                    alert('Veuillez saisir un titre');
                    isValid = false;
                }
                
                // Validation de la description
                const description = document.getElementById('description');
                if (!description.value.trim()) {
                    alert('Veuillez saisir une description');
                    isValid = false;
                }
                
                // Validation de l'emplacement
                const emplacement = document.getElementById('emplacement');
                if (!emplacement.value.trim()) {
                    alert('Veuillez saisir un emplacement');
                    isValid = false;
                }
                
                // Validation de la date
                const date = document.getElementById('date_signalement');
                if (!date.value) {
                    alert('Veuillez sélectionner une date');
                    isValid = false;
                }
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
        }
    });
    </script>

<?php require_once '../includes/footer.php'; ?>
</body>
</html> 