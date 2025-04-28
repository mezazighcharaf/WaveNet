<?php 
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once $_SERVER['DOCUMENT_ROOT'] . '/gestion_signalement/config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/gestion_signalement/gestion signalement/controller/signalementctrl.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/gestion_signalement/gestion signalement/model/signalement.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'] ?? null;
    $emplacement = $_POST['emplacement'] ?? null;
    $description = $_POST['description'] ?? null;

    if (empty($titre) || empty($emplacement) || empty($description)) {
        $_SESSION['error_message'] = 'Tous les champs sont obligatoires.';
        header('Location: index.php');
        exit;
    }

    try {
        $signalementC = new SignalementC();
        
        $signalement = new Signalement();
        $signalement->setIdSignalement(rand(1, 10000));
        $signalement->setTitre($titre);
        $signalement->setDescription($description);
        $signalement->setEmplacement($emplacement);
        $signalement->setStatut('non traité');
        
        $result = $signalementC->addSignalement($signalement); 

        if ($result) {
            $_SESSION['success_message'] = 'Signalement ajoute avec succes !';
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['error_message'] = 'Erreur lors de l ajout du signalement.';
            header('Location: index.php');
            exit;
        }

    } catch (PDOException $e) {
        error_log("Erreur PDO lors de l'ajout : " . $e->getMessage());
        $_SESSION['error_message'] = 'Erreur de base de données. Veuillez réessayer.';
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        error_log("Erreur générale lors de l'ajout : " . $e->getMessage());
        $_SESSION['error_message'] = 'Une erreur est survenue. Veuillez réessayer.';
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signaler - Urbaverse</title>
    <link rel="stylesheet" href="css/style11.css">
</head>
<body>
    <header class="main-header">
        <nav class="nav-container">
            <div class="logo">
                <h1>Urbaverse</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="dashboard.html">Dashboard</a></li>
                <li><a href="blog.html">Blog</a></li>
                <li><a href="index.php">Signaler</a></li>
                <li><a href="interventions.php">Interventions</a></li>
                <li><a href="about.html">À propos</a></li>
            </ul>
            <div class="user-actions">
                <span class="points">Points verts: 150</span>
                <a href="#login" class="btn btn-secondary">Connexion</a>
            </div>
        </nav>
    </header>

    <main class="report">
        <div class="report-header" style="text-align: center; margin-bottom: 4rem;">
            <h2>Signaler une Anomalie</h2>
            <p>Aidez-nous à améliorer votre environnement urbain</p>
        </div>

        <div class="report-form">
            <?php 
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success" style="padding: 1rem; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger" style="padding: 1rem; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                unset($_SESSION['error_message']);
            }
            ?>
            <form id="reportForm" action="index.php" method="POST">
                <div class="form-group">
                    <label for="titre">Titre (Type d'anomalie)</label>
                    <select id="titre" name="titre">
                        <option value="">Sélectionnez un type</option>
                        <option value="dechets">Déchets</option>
                        <option value="voirie">Voirie</option>
                        <option value="eclairage">Éclairage</option>
                        <option value="vegetation">Végétation</option>
                        <option value="autre">Autre</option>
                    </select>
                    <span class="error-message" id="titre-error"></span>
                </div>

                <div class="form-group">
                    <label for="emplacement">Localisation</label>
                    <input type="text" id="emplacement" name="emplacement" placeholder="Adresse ou description du lieu">
                    <span class="error-message" id="emplacement-error"></span>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" placeholder="Décrivez le problème en détail"></textarea>
                    <span class="error-message" id="description-error"></span>
                </div>

                <button type="submit" class="btn btn-primary">Envoyer le signalement</button>
            </form>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>Urbaverse</h4>
                <p>Ensemble pour un avenir urbain durable</p>
            </div>
            <div class="footer-section">
                <h4>Liens Rapides</h4>
                <ul>
                    <li><a href="about.html">À propos</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="#privacy">Confidentialité</a></li>
                    <li><a href="#terms">Conditions</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Suivez-nous</h4>
                <div class="social-links">
                    <a href="#twitter">Twitter</a>
                    <a href="#facebook">Facebook</a>
                    <a href="#instagram">Instagram</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Urbaverse. Tous droits réservés.</p>
        </div>
    </footer>
    <style>
        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        
        .form-control-invalid {
            border-color: #dc3545 !important;
            background-color: #fff8f8;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportForm = document.getElementById('reportForm');
            const titre = document.getElementById('titre');
            const emplacement = document.getElementById('emplacement');
            const description = document.getElementById('description');
            
            // Fonction de validation des caractères (uniquement lettres et chiffres)
            function contientUniquementLettresEtChiffres(texte) {
                // Expression régulière plus stricte - lettres, chiffres et espaces uniquement
                return /^[a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ]+$/.test(texte);
            }
            
            // Fonction pour nettoyer l'entrée (supprimer les caractères non autorisés)
            function nettoyerInput(input) {
                let valeurNettoyee = input.value.replace(/[^a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ]/g, '');
                if (valeurNettoyee !== input.value) {
                    input.value = valeurNettoyee;
                    return false;
                }
                return true;
            }
            
            // Fonction pour afficher une erreur
            function afficherErreur(element, message) {
                const errorElement = document.getElementById(element.id + '-error');
                if (errorElement) {
                    errorElement.textContent = message;
                    element.classList.add('form-control-invalid');
                }
                return false;
            }
            
            // Fonction pour effacer une erreur
            function effacerErreur(element) {
                const errorElement = document.getElementById(element.id + '-error');
                if (errorElement) {
                    errorElement.textContent = '';
                    element.classList.remove('form-control-invalid');
                }
            }
            
            // Écouter les événements de saisie pour effacer les erreurs et valider en temps réel
            titre.addEventListener('change', function() {
                effacerErreur(this);
            });
            
            emplacement.addEventListener('input', function() {
                if (!nettoyerInput(this)) {
                    afficherErreur(this, 'Seuls les lettres et les chiffres sont autorisés');
                } else {
                    effacerErreur(this);
                }
            });
            
            description.addEventListener('input', function() {
                if (!nettoyerInput(this)) {
                    afficherErreur(this, 'Seuls les lettres et les chiffres sont autorisés');
                } else {
                    effacerErreur(this);
                }
            });
            
            // Validation du formulaire lors de la soumission
            reportForm.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Réinitialiser toutes les erreurs
                effacerErreur(titre);
                effacerErreur(emplacement);
                effacerErreur(description);
                
                // Validation du titre
                if (!titre.value.trim()) {
                    isValid = afficherErreur(titre, 'Veuillez sélectionner un type d\'anomalie');
                }
                
                // Validation de l'emplacement
                if (!emplacement.value.trim()) {
                    isValid = afficherErreur(emplacement, 'Veuillez indiquer la localisation');
                } else if (!contientUniquementLettresEtChiffres(emplacement.value.trim())) {
                    isValid = afficherErreur(emplacement, 'La localisation ne doit contenir que des lettres et des chiffres');
                }
                
                // Validation de la description
                if (!description.value.trim()) {
                    isValid = afficherErreur(description, 'Veuillez fournir une description');
                } else if (description.value.trim().length < 10) {
                    isValid = afficherErreur(description, 'La description doit contenir au moins 10 caractères');
                } else if (!contientUniquementLettresEtChiffres(description.value.trim())) {
                    isValid = afficherErreur(description, 'La description ne doit contenir que des lettres et des chiffres');
                }
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>