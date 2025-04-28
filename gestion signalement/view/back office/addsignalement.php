<?php
include_once __DIR__ . '/../../controller/signalementctrl.php';
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../model/signalement.php';


$randomId = mt_rand(1000, 9999);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $signalement = new Signalement();
    
    
    $signalement->setIdSignalement($_POST['id_signalement']);
    $signalement->setTitre($_POST['titre']);
    $signalement->setDescription($_POST['description']);
    $signalement->setEmplacement($_POST['emplacement']);
    $signalement->setDateSignalement($_POST['date_signalement']);
    $signalement->setStatut($_POST['statut']);

    $signalementc = new SignalementC(); 
    if ($signalementc->addSignalement($signalement)) {
        header('Location: affichesignalement.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Signalement</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 240px;
            background-color: #2e4f3e;
            color: white;
            padding-top: 20px;
        }
        
        .sidebar .logo {
            padding: 0 20px 30px;
        }
        
        .sidebar .logo h1 {
            font-size: 24px;
            color: white;
        }
        
        .sidebar-nav ul {
            list-style: none;
        }
        
        .sidebar-nav a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px;
            transition: background-color 0.2s;
        }
        
        .sidebar-nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-nav a.active {
            background-color: #f1c40f;
            color: #2e4f3e;
            font-weight: 600;
        }
        
        .main-content {
            flex: 1;
            background-color: #f9f9f9;
            padding: 20px;
        }
        
        .header-admin {
            text-align: right;
            margin-bottom: 10px;
        }
        
        h1.content-title {
            color: #2e4f3e;
            font-size: 28px;
            margin-bottom: 30px;
        }
        
        .content-section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .content-section h2 {
            color: #2e4f3e;
            font-size: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-control:focus {
            border-color: #2e4f3e;
            outline: none;
        }
        
        .form-actions {
            margin-top: 30px;
            display: flex;
            align-items: center;
        }
        
        .btn-primary {
            background-color: #2e4f3e;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            margin-right: 15px;
        }
        
        .btn-primary:hover {
            background-color: #263f32;
        }
        
        .btn-link {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-link:hover {
            text-decoration: underline;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .form-control-invalid {
            background-color: #fff8f8;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <h1>Urbaverse</h1>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="affichesignalement.php" class="active">Signalements</a></li>
                <li><a href="afficherintervention.php">Interventions</a></li>
                <li><a href="utilisateurs.php">Utilisateurs</a></li>
                <li><a href="parametres.php">Paramètres</a></li>
                <li><a href="../front office/index.php">Retour au site</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-admin">
            Admin
        </div>
        
        <h1 class="content-title">Ajouter un Signalement</h1>
        
        <div class="content-section">
            <h2>Formulaire de Signalement</h2>
            
            <form action="addsignalement.php" method="POST">
                
                <input type="hidden" name="id_signalement" value="<?php echo $randomId; ?>">
                
                <div class="form-group">
                    <label for="titre">Titre</label>
                    <input type="text" id="titre" name="titre" class="form-control">
                    <span class="error-message" id="titre-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                    <span class="error-message" id="description-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="emplacement">Emplacement</label>
                    <input type="text" id="emplacement" name="emplacement" class="form-control">
                    <span class="error-message" id="emplacement-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="date_signalement">Date</label>
                    <input type="date" id="date_signalement" name="date_signalement" class="form-control">
                    <span class="error-message" id="date-error"></span>
                </div>
                
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut" class="form-control">
                        <option value="non traité">Non traité</option>
                        <option value="en cours">En cours</option>
                        <option value="traité">Traité</option>
                    </select>
                    <span class="error-message" id="statut-error"></span>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Ajouter Signalement</button>
                    <a href="affichesignalement.php" class="btn-link">Annuler</a>
                </div>
            </form>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const titreInput = document.getElementById('titre');
        const descriptionInput = document.getElementById('description');
        const emplacementInput = document.getElementById('emplacement');
        const dateSignalementInput = document.getElementById('date_signalement');
        const statutSelect = document.getElementById('statut');
        
        
        const titreError = document.getElementById('titre-error');
        const descriptionError = document.getElementById('description-error');
        const emplacementError = document.getElementById('emplacement-error');
        const dateError = document.getElementById('date-error');
        const statutError = document.getElementById('statut-error');
        
        function contientUniquementLettresEtChiffres(texte) {
            
            return /^[a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ]+$/.test(texte);
        }
        
        
        function nettoyerInput(input) {
            let valeurNettoyee = input.value.replace(/[^a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ]/g, '');
            if (valeurNettoyee !== input.value) {
                input.value = valeurNettoyee;
                return false;
            }
            return true;
        }
        
        function afficherErreur(input, messageElement, message) {
            input.classList.add('is-invalid');
            messageElement.textContent = message;
            messageElement.style.display = 'block';
        }
        
        function effacerErreur(input, messageElement) {
            input.classList.remove('is-invalid');
            messageElement.textContent = '';
            messageElement.style.display = 'none';
        }
        
        
        titreInput.addEventListener('input', function() {
            if (!nettoyerInput(this)) {
                afficherErreur(this, titreError, 'Seuls les lettres et les chiffres sont autorisés');
            } else {
                effacerErreur(this, titreError);
            }
        });
        
        descriptionInput.addEventListener('input', function() {
            if (!nettoyerInput(this)) {
                afficherErreur(this, descriptionError, 'Seuls les lettres et les chiffres sont autorisés');
            } else {
                effacerErreur(this, descriptionError);
            }
        });
        
        emplacementInput.addEventListener('input', function() {
            if (!nettoyerInput(this)) {
                afficherErreur(this, emplacementError, 'Seuls les lettres et les chiffres sont autorisés');
            } else {
                effacerErreur(this, emplacementError);
            }
        });
        
        dateSignalementInput.addEventListener('input', function() {
            effacerErreur(this, dateError);
        });
        
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            
            effacerErreur(titreInput, titreError);
            effacerErreur(descriptionInput, descriptionError);
            effacerErreur(emplacementInput, emplacementError);
            effacerErreur(dateSignalementInput, dateError);
            effacerErreur(statutSelect, statutError);
            
            
            if (!titreInput.value.trim()) {
                afficherErreur(titreInput, titreError, 'Veuillez saisir un titre');
                isValid = false;
            } else if (!contientUniquementLettresEtChiffres(titreInput.value.trim())) {
                afficherErreur(titreInput, titreError, 'Le titre ne doit contenir que des lettres et des chiffres');
                isValid = false;
            }
            
            
            if (!descriptionInput.value.trim()) {
                afficherErreur(descriptionInput, descriptionError, 'Veuillez saisir une description');
                isValid = false;
            } else if (!contientUniquementLettresEtChiffres(descriptionInput.value.trim())) {
                afficherErreur(descriptionInput, descriptionError, 'La description ne doit contenir que des lettres et des chiffres');
                isValid = false;
            }
            
            
            if (!emplacementInput.value.trim()) {
                afficherErreur(emplacementInput, emplacementError, 'Veuillez saisir un emplacement');
                isValid = false;
            } else if (!contientUniquementLettresEtChiffres(emplacementInput.value.trim())) {
                afficherErreur(emplacementInput, emplacementError, 'L\'emplacement ne doit contenir que des lettres et des chiffres');
                isValid = false;
            }
            
            
            if (!dateSignalementInput.value.trim()) {
                afficherErreur(dateSignalementInput, dateError, 'Veuillez sélectionner une date');
                isValid = false;
            }
            
            
            if (!statutSelect.value) {
                afficherErreur(statutSelect, statutError, 'Veuillez sélectionner un statut');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
    </script>
</body>
</html>