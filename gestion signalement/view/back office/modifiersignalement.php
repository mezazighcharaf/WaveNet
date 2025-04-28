<?php
include_once '../../Controller/signalementctrl.php';
include_once __DIR__ . '/../../../config.php';

$titre = $description = $emplacement = $date_signalement = $statut = "";
$id_signalement = "";
$signalement_data = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $signalement = new Signalement();

    if (isset($_POST['id_signalement'])) {
        $signalement->setIdSignalement($_POST['id_signalement']);
    }
    if (isset($_POST['titre'])) {
        $signalement->setTitre($_POST['titre']);
    }
    if (isset($_POST['description'])) {
        $signalement->setDescription($_POST['description']);
    }
    if (isset($_POST['emplacement'])) {
        $signalement->setEmplacement($_POST['emplacement']);
    }
    if (isset($_POST['date_signalement'])) {
        $signalement->setDateSignalement($_POST['date_signalement']);
    }
    if (isset($_POST['statut'])) {
        $signalement->setStatut($_POST['statut']);
    }

    $controller = new SignalementC();
    $controller->updateSignalement($signalement);
}

if (isset($_REQUEST['search_id'])) {
    $id_signalement = $_REQUEST['search_id'];
    $controller = new SignalementC();
    $result = $controller->rechercher($id_signalement);
    if ($result && count($result) > 0) {
        $signalement_data = $result[0];
        $titre = $signalement_data['titre'];
        $description = $signalement_data['description'];
        $emplacement = $signalement_data['emplacement'];
        $date_signalement = $signalement_data['date_signalement'];
        $statut = $signalement_data['statut'];
    } else {
        $error_message = "Signalement non trouvé.";
    }
} elseif (isset($_GET['id'])) {
    $id_signalement = $_GET['id'];
    $controller = new SignalementC();
    $result = $controller->rechercher($id_signalement);
    if ($result && count($result) > 0) {
        $signalement_data = $result[0];
        $titre = $signalement_data['titre'];
        $description = $signalement_data['description'];
        $emplacement = $signalement_data['emplacement'];
        $date_signalement = $signalement_data['date_signalement'];
        $statut = $signalement_data['statut'];
    } else {
        $error_message = "Signalement non trouvé.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Modifier Signalement - Backoffice - Urbaverse</title>
    <link rel="stylesheet" href="css/backoffice.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body>
    <aside class="sidebar">
        <div class="logo">
          <h1>Urbaverse</h1>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="#dashboard">Dashboard</a></li>
                <li><a href="index.php" class="active">Signalements</a></li>
                <li><a href="#users">Utilisateurs</a></li>
                <li><a href="#settings">Paramètres</a></li>
                <li class="home-link"><a href="../front office/index.html">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="content-header">
            <h1>Modifier un Signalement</h1>
            <div class="user-info">
                <span>Admin</span>
            </div>
        </header>

        <div class="container-fluid">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rechercher un Signalement par ID</h6>
                </div>
                <div class="card-body">
                     <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>
                    <form class="user" action="modifiersignalement.php" method="GET">
                        <div class="form-group row">
                             <div class="col-sm-6 mb-3 mb-sm-0">
                                <input type="number" class="form-control form-control-user" id="search_id" name="search_id"
                                       placeholder="ID du Signalement" value="<?= htmlspecialchars($id_signalement) ?>" required>
                            </div>
                             <div class="col-sm-6">
                                 <button type="submit" class="btn btn-primary btn-user btn-block">
                                     <i class="fas fa-search fa-sm"></i> Rechercher
                                 </button>
                             </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($signalement_data): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                     <h6 class="m-0 font-weight-bold text-primary">Détails du Signalement #<?= htmlspecialchars($id_signalement) ?></h6>
                </div>
                <div class="card-body">
                    <form class="user" method="POST" action="modifiersignalement.php">
                        <input type="hidden" name="id_signalement" value="<?= htmlspecialchars($id_signalement ?? '') ?>">
                        <input type="hidden" name="search_id" value="<?= htmlspecialchars($id_signalement) ?>">

                        <div class="form-group">
                            <label for="titre">Titre:</label>
                            <input type="text" class="form-control form-control-user" id="titre" name="titre"
                                value="<?= htmlspecialchars($titre) ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea class="form-control form-control-user" id="description" name="description"
                                ><?= htmlspecialchars($description) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="emplacement">Emplacement:</label>
                            <input type="text" class="form-control form-control-user" id="emplacement" name="emplacement"
                                value="<?= htmlspecialchars($emplacement) ?>">
                        </div>

                        <div class="form-group">
                            <label for="date_signalement">Date du signalement:</label>
                            <input type="date" class="form-control form-control-user" id="date_signalement" name="date_signalement"
                                value="<?= htmlspecialchars($date_signalement) ?>">
                        </div>

                        <div class="form-group">
                            <label for="statut">Statut:</label>
                            <select class="form-control form-control-user" id="statut" name="statut">
                                <option value="En attente" <?= $statut == 'En attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="En cours" <?= $statut == 'En cours' ? 'selected' : '' ?>>En cours</option>
                                <option value="Résolu" <?= $statut == 'Résolu' ? 'selected' : '' ?>>Résolu</option>
                                <option value="Rejeté" <?= $statut == 'Rejeté' ? 'selected' : '' ?>>Rejeté</option>
                            </select>
                        </div>

                        <button type="submit" name="update" class="btn btn-primary btn-user btn-block">
                             <i class="fas fa-save fa-sm"></i> Mettre à jour le Signalement
                        </button>
                    </form>
                 </div>
            </div>
             <?php endif; ?>

            <div class="text-center mt-3">
                <a href='index.php' class="btn btn-secondary btn-icon-split">
                    <span class="icon text-white-50">
                         <i class="fas fa-arrow-left"></i>
                    </span>
                    <span class="text">Retour à la liste</span>
                 </a>
             </div>

        </div>
    </main>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sélection des éléments
        const form = document.querySelector('form.user[method="POST"]');
        if (!form) return; // Si aucun formulaire de mise à jour n'est présent, quitter
        
        const titreInput = document.getElementById('titre');
        const descriptionInput = document.getElementById('description');
        const emplacementInput = document.getElementById('emplacement');
        const dateSignalementInput = document.getElementById('date_signalement');
        const statutSelect = document.getElementById('statut');
        
        // Fonction pour vérifier si une chaîne contient uniquement des lettres et des chiffres
        function contientUniquementLettresEtChiffres(texte) {
            return /^[a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ,. '-]+$/.test(texte);
        }
        
        // Fonction pour nettoyer les caractères non autorisés
        function nettoyerInput(input) {
            input.value = input.value.replace(/[^a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ,. '-]/g, '');
        }
        
        // Ajout des écouteurs d'événements sur les champs de texte
        if (titreInput) {
            titreInput.addEventListener('input', function() {
                nettoyerInput(this);
            });
        }
        
        if (descriptionInput) {
            descriptionInput.addEventListener('input', function() {
                nettoyerInput(this);
            });
        }
        
        if (emplacementInput) {
            emplacementInput.addEventListener('input', function() {
                nettoyerInput(this);
            });
        }
        
        // Validation du formulaire lors de la soumission
        form.addEventListener('submit', function(event) {
            let isValid = true;
            let errorMessages = [];
            
            // Vérifier le titre
            if (titreInput && !titreInput.value.trim()) {
                errorMessages.push('Veuillez saisir un titre');
                isValid = false;
            } else if (titreInput && !contientUniquementLettresEtChiffres(titreInput.value.trim())) {
                errorMessages.push('Le titre ne doit contenir que des lettres, des chiffres et quelques caractères comme virgules et points');
                isValid = false;
            }
            
            // Vérifier la description
            if (descriptionInput && !descriptionInput.value.trim()) {
                errorMessages.push('Veuillez saisir une description');
                isValid = false;
            } else if (descriptionInput && !contientUniquementLettresEtChiffres(descriptionInput.value.trim())) {
                errorMessages.push('La description ne doit contenir que des lettres, des chiffres et quelques caractères comme virgules et points');
                isValid = false;
            }
            
            // Vérifier l'emplacement
            if (emplacementInput && !emplacementInput.value.trim()) {
                errorMessages.push('Veuillez saisir un emplacement');
                isValid = false;
            } else if (emplacementInput && !contientUniquementLettresEtChiffres(emplacementInput.value.trim())) {
                errorMessages.push('L\'emplacement ne doit contenir que des lettres, des chiffres et quelques caractères comme virgules et points');
                isValid = false;
            }
            
            // Vérifier la date
            if (dateSignalementInput && !dateSignalementInput.value.trim()) {
                errorMessages.push('Veuillez sélectionner une date');
                isValid = false;
            }
            
            // Vérifier le statut
            if (statutSelect && !statutSelect.value) {
                errorMessages.push('Veuillez sélectionner un statut');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                alert('Erreurs :\n- ' + errorMessages.join('\n- '));
            }
        });
    });
    </script>

</body>

</html>