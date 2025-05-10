<?php
include_once '../../controller/signalementctrl.php';
include_once '../../config.php';
include_once '../../models/signalement.php';


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
    <title>Ajouter un Signalement - WaveNet</title>
    <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
    <link rel="stylesheet" href="/WaveNet/views/assets/css/admin-dashboard.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <img src="/WaveNet/views/assets/images/logo.png" alt="Logo" class="logo-img">
            <h1>WaveNet</h1>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="/WaveNet/views/backoffice/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="/WaveNet/views/backoffice/listeUtilisateurs.php"><i class="fas fa-users"></i> Utilisateurs</a></li>
                <li><a href="/WaveNet/views/backoffice/defis.php"><i class="fas fa-trophy"></i> Défis</a></li>
                <li><a href="/WaveNet/views/backoffice/Gquartier.php"><i class="fas fa-map-marker-alt"></i> Quartiers</a></li>
                <li><a href="/WaveNet/views/backoffice/backinfra.php"><i class="fas fa-building"></i> Infrastructures</a></li>
                <li><a href="/WaveNet/views/backoffice/gsignalement.php" class="active"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
                <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="content-header">
            <h1>Ajouter un Signalement</h1>
            <div class="user-info">
                <span>Admin</span>
            </div>
        </header>
        
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
                    <a href="gsignalement.php" class="btn-link">Annuler</a>
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