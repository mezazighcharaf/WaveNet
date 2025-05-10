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
require_once '../../models/signalement.php';
require_once '../../controller/signalementctrl.php';

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
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
        /* Nouveaux styles pour la carte */
        #mapAll {
            height: 300px; 
            width: 100%; 
            margin-bottom: 2rem; 
            border-radius: 8px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .location-input-group {
            display: flex;
            align-items: center;
        }
        .location-input-group input {
            flex: 1;
            margin-right: 5px;
        }
        .location-input-group button {
            padding: 8px 12px;
            background-color: var(--accent-green);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .location-input-group button:hover {
            background-color: var(--dark-green);
        }
        #mapModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            position: relative;
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 80%;
            max-width: 800px;
            border-radius: 8px;
        }
        .close-modal {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 24px;
            cursor: pointer;
        }
        #map {
            height: 500px;
            width: 100%;
            border-radius: 4px;
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
            <!-- Carte pour afficher les signalements existants -->
            <div id="mapAll"></div>
            
            <div class="form-container">
                <form action="addSignalement.php" method="POST" id="signalementForm">
                    <input type="hidden" name="id_signalement" value="<?php echo $randomId; ?>">
                    
                    <div class="form-group">
                        <label for="titre">Titre du signalement*</label>
                        <select id="titre" name="titre" class="form-control" required>
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
                        <label for="emplacement">Localisation*</label>
                        <div class="location-input-group">
                            <input type="text" id="emplacement" name="emplacement" class="form-control" placeholder="Adresse ou description du lieu" readonly required>
                            <button type="button" id="btnMap"><i class="fas fa-map-marker-alt"></i> Choisir sur la carte</button>
                        </div>
                        <span class="error-message" id="emplacement-error"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description détaillée*</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Décrivez le problème en détail" required></textarea>
                        <span class="error-message" id="description-error"></span>
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

    <!-- Modal de la carte -->
    <div id="mapModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Choisissez un emplacement sur la carte</h3>
            <div id="map"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('signalementForm');
        const titre = document.getElementById('titre');
        const emplacement = document.getElementById('emplacement');
        const description = document.getElementById('description');
        const mapModal = document.getElementById('mapModal');
        const btnMap = document.getElementById('btnMap');
        const closeModal = document.querySelector('.close-modal');
        let map;
        let marker;
        let mapAll;

        // Fonction pour initialiser la carte de sélection
        function initMap() {
            map = L.map('map').setView([36.8065, 10.1815], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            map.on('click', function(e) {
                const { lat, lng } = e.latlng;
                if (marker) {
                    marker.setLatLng(e.latlng);
                } else {
                    marker = L.marker(e.latlng).addTo(map);
                }

                // Obtenir l'adresse à partir des coordonnées (géocodage inverse)
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                        emplacement.value = address;
                        mapModal.style.display = 'none';
                    })
                    .catch(() => {
                        emplacement.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                        mapModal.style.display = 'none';
                    });
            });
        }

        // Initialiser la carte générale
        mapAll = L.map('mapAll').setView([36.8065, 10.1815], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapAll);

        // Fonction pour géocoder une adresse
        function geocodeAdresse(adresse) {
            return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(adresse)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
                    } else {
                        return null;
                    }
                });
        }

        // Charger les signalements existants sur la carte
        (async function() {
            try {
                const response = await fetch('/WaveNet/controller/signalementctrl.php?action=get_all_json');
                const signalements = await response.json();
                
                if (signalements && signalements.length) {
                    for (const sig of signalements) {
                        const coords = await geocodeAdresse(sig.emplacement);
                        if (coords) {
                            const marker = L.marker(coords).addTo(mapAll);
                            marker.bindPopup(
                                `<b>${sig.titre}</b><br>` +
                                `<b>Description :</b> ${sig.description}<br>` +
                                `<b>Emplacement :</b> ${sig.emplacement}<br>` +
                                `<b>Date :</b> ${sig.date_signalement}<br>` +
                                `<b>Statut :</b> ${sig.statut}`
                            );
                        }
                    }
                }
            } catch (error) {
                console.error('Erreur lors du chargement des signalements :', error);
            }
        })();

        // Gestionnaires d'événements pour la modal de carte
        btnMap.addEventListener('click', function() {
            mapModal.style.display = 'block';
            if (!map) {
                initMap();
            }
            setTimeout(() => {
                map.invalidateSize();
            }, 100);
        });

        closeModal.addEventListener('click', function() {
            mapModal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === mapModal) {
                mapModal.style.display = 'none';
            }
        });

        // Fonctions de validation
        function contientUniquementLettresEtChiffres(texte) {
            return /^[a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ.,\-']+$/.test(texte);
        }
        
        function estAdresseValide(texte) {
            return /^[a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ.,'\-()/]+$/.test(texte);
        }
        
        function afficherErreur(element, message) {
            const errorElement = document.getElementById(element.id + '-error');
            if (errorElement) {
                errorElement.textContent = message;
                element.classList.add('form-control-invalid');
            }
            return false;
        }
        
        function effacerErreur(element) {
            const errorElement = document.getElementById(element.id + '-error');
            if (errorElement) {
                errorElement.textContent = '';
                element.classList.remove('form-control-invalid');
            }
        }
        
        // Gestionnaires d'événements pour les champs du formulaire
        titre.addEventListener('change', function() {
            effacerErreur(this);
        });
        
        emplacement.addEventListener('input', function() {
            effacerErreur(this);
        });
        
        description.addEventListener('input', function() {
            effacerErreur(this);
        });
        
        // Validation du formulaire avant envoi
        if (form) {
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Nettoyer les erreurs précédentes
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
                } else if (!estAdresseValide(emplacement.value.trim())) {
                    isValid = afficherErreur(emplacement, 'La localisation ne doit contenir que des caractères d\'adresse valides');
                }
                
                // Validation de la description
                if (!description.value.trim()) {
                    isValid = afficherErreur(description, 'Veuillez fournir une description');
                } else if (description.value.trim().length < 10) {
                    isValid = afficherErreur(description, 'La description doit contenir au moins 10 caractères');
                } else if (!contientUniquementLettresEtChiffres(description.value.trim())) {
                    isValid = afficherErreur(description, 'La description ne doit contenir que des caractères autorisés');
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