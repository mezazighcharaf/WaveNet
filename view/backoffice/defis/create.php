<?php
session_start();

// Check if user is logged in as admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    // Just mock admin role for demonstration since there's no login
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Admin';
}

require_once __DIR__ . '/../../../controller/DefiController.php';

// Initialize controller
$defiController = new DefiController();

// Initialisation des variables d'erreur
$errors = [];
$formData = [
    'Titre_D' => '',
    'Description_D' => '',
    'Objectif' => '',
    'Points_verts' => '',
    'Statut_D' => 'Actif',
    'Date_Debut' => '',
    'Date_Fin' => '',
    'Difficulte' => 'Facile',
    'Id_Quartier' => ''
];

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération et nettoyage des données
    $formData = [
        'Titre_D' => trim(htmlspecialchars($_POST['Titre_D'] ?? '')),
        'Description_D' => trim(htmlspecialchars($_POST['Description_D'] ?? '')),
        'Objectif' => trim(htmlspecialchars($_POST['Objectif'] ?? '')),
        'Points_verts' => $_POST['Points_verts'] ?? '',
        'Statut_D' => $_POST['Statut_D'] ?? '',
        'Date_Debut' => $_POST['Date_Debut'] ?? '',
        'Date_Fin' => $_POST['Date_Fin'] ?? '',
        'Difficulte' => $_POST['Difficulte'] ?? '',
        'Id_Quartier' => $_POST['Id_Quartier'] ?? ''
    ];
    
    // Validation des données
    // Titre
    if(empty($formData['Titre_D'])) {
        $errors['Titre_D'] = "Le titre du défi est obligatoire";
    } elseif(strlen($formData['Titre_D']) < 5) {
        $errors['Titre_D'] = "Le titre doit contenir au moins 5 caractères";
    } elseif(strlen($formData['Titre_D']) > 100) {
        $errors['Titre_D'] = "Le titre ne peut pas dépasser 100 caractères";
    }
    
    // Description
    if(empty($formData['Description_D'])) {
        $errors['Description_D'] = "La description est obligatoire";
    } elseif(strlen($formData['Description_D']) < 20) {
        $errors['Description_D'] = "La description doit contenir au moins 20 caractères";
    }
    
    // Objectif
    if(empty($formData['Objectif'])) {
        $errors['Objectif'] = "L'objectif est obligatoire";
    }
    
    // Points verts
    if(empty($formData['Points_verts'])) {
        $errors['Points_verts'] = "Le nombre de points est obligatoire";
    } elseif(!is_numeric($formData['Points_verts']) || $formData['Points_verts'] < 1 || $formData['Points_verts'] > 1000) {
        $errors['Points_verts'] = "Le nombre de points doit être compris entre 1 et 1000";
    }
    
    // Statut
    $statutsValides = ['Actif', 'Inactif', 'À venir'];
    if(empty($formData['Statut_D']) || !in_array($formData['Statut_D'], $statutsValides)) {
        $errors['Statut_D'] = "Le statut sélectionné n'est pas valide";
    }
    
    // Dates
    if(empty($formData['Date_Debut'])) {
        $errors['Date_Debut'] = "La date de début est obligatoire";
    }
    
    if(empty($formData['Date_Fin'])) {
        $errors['Date_Fin'] = "La date de fin est obligatoire";
    }
    
    // Vérification que la date de fin est postérieure à la date de début
    if(!empty($formData['Date_Debut']) && !empty($formData['Date_Fin'])) {
        $dateDebut = new DateTime($formData['Date_Debut']);
        $dateFin = new DateTime($formData['Date_Fin']);
        
        if($dateFin < $dateDebut) {
            $errors['Date_Fin'] = "La date de fin doit être postérieure à la date de début";
        }
    }
    
    // Difficulté
    $difficultesValides = ['Facile', 'Intermédiaire', 'Difficile'];
    if(empty($formData['Difficulte']) || !in_array($formData['Difficulte'], $difficultesValides)) {
        $errors['Difficulte'] = "La difficulté sélectionnée n'est pas valide";
    }
    
    // Quartier
    if(empty($formData['Id_Quartier'])) {
        $errors['Id_Quartier'] = "L'ID du quartier est obligatoire";
    } elseif(!is_numeric($formData['Id_Quartier']) || $formData['Id_Quartier'] < 1) {
        $errors['Id_Quartier'] = "L'ID du quartier doit être un nombre positif";
    }
    
    // Si aucune erreur, création du défi
    if(empty($errors)) {
        if($defiController->createDefi($formData)) {
            header('Location: index.php?message=Défi créé avec succès');
            exit();
        } else {
            $error = "Erreur lors de la création du défi";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un défi - Urbaverse</title>
    <link rel="stylesheet" href="../../../assets/css/backoffice.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        .form-group.has-error input,
        .form-group.has-error textarea,
        .form-group.has-error select {
            border-color: #e74c3c;
        }
        .help-text {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        /* Styles pour la validation personnalisée */
        .js-error {
            display: none;
        }
        .js-error.visible {
            display: block;
        }
        
        /* Nouveau design du formulaire */
        .defi-form {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            padding: 32px;
            margin: 24px 0;
        }
        
        .form-title {
            color: #2c3e50;
            margin-bottom: 28px;
            font-size: 1.6rem;
            font-weight: 600;
            padding-bottom: 16px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .form-row {
            display: flex;
            gap: 24px;
            margin-bottom: 8px;
        }
        
        .form-column {
            flex: 1;
        }
        
        .form-section {
            margin-bottom: 32px;
            background: #f9fafb;
            border-radius: 8px;
            padding: 24px;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 18px;
            color: #34495e;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #dde1e3;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 120px;
        }
        
        .required {
            color: #e74c3c;
            margin-left: 4px;
        }
        
        .form-actions {
            margin-top: 36px;
            display: flex;
            gap: 16px;
            justify-content: flex-end;
        }
        
        .btn-submit {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-submit:hover {
            background-color: #2ecc71;
        }
        
        .btn-cancel {
            background-color: #ecf0f1;
            color: #34495e;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-cancel:hover {
            background-color: #dde1e3;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .defi-form {
                padding: 20px;
            }
            
            .form-section {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            <a href="../../frontoffice/index.php" style="text-decoration: none; display: flex; align-items: center; justify-content: center; padding: 15px 0;">
                <img src="../../../assets/img/logo.jpg" alt="Logo Urbaverse" width="40" height="40" style="border-radius: 50%; margin-right: 10px; border: 2px solid var(--accent-green);">
                <h1 style="color: white; margin: 0; font-size: 22px;">Urbaverse</h1>
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php">Défis</a></li>
                <li class="home-link"><a href="../../frontoffice/index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="content-header">
            <h1>Ajouter un nouveau défi</h1>
            <div class="user-info">
                <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
                <a href="../../frontoffice/index.php" class="home-button">Accueil</a>
            </div>
        </header>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($errors)): ?>
            <div class="alert alert-danger">
                <p>Veuillez corriger les erreurs suivantes :</p>
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="defi-form">
            <h2 class="form-title">Créer un nouveau défi écologique</h2>
            
            <form action="" method="post" class="form-style" id="defi-form" novalidate>
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <p>Veuillez corriger les erreurs suivantes :</p>
                        <ul>
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="form-section">
                    <h3 class="section-title">Informations générales</h3>
                    <div class="form-row">
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Titre_D']) ? 'has-error' : ''; ?>">
                                <label for="Titre_D">Titre du défi <span class="required">*</span></label>
                                <input type="text" id="Titre_D" name="Titre_D" class="form-control" 
                                      value="<?php echo htmlspecialchars($formData['Titre_D']); ?>">
                                <?php if(isset($errors['Titre_D'])): ?>
                                    <div class="error-message"><?php echo $errors['Titre_D']; ?></div>
                                <?php else: ?>
                                    <div class="help-text">Entre 5 et 100 caractères</div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Titre_D"></div>
                            </div>
                        </div>
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Points_verts']) ? 'has-error' : ''; ?>">
                                <label for="Points_verts">Points verts <span class="required">*</span></label>
                                <input type="number" id="Points_verts" name="Points_verts" class="form-control" 
                                      value="<?php echo htmlspecialchars($formData['Points_verts']); ?>">
                                <?php if(isset($errors['Points_verts'])): ?>
                                    <div class="error-message"><?php echo $errors['Points_verts']; ?></div>
                                <?php else: ?>
                                    <div class="help-text">Valeur entre 1 et 1000</div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Points_verts"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['Description_D']) ? 'has-error' : ''; ?>">
                        <label for="Description_D">Description <span class="required">*</span></label>
                        <textarea id="Description_D" name="Description_D" class="form-control" rows="4"><?php echo htmlspecialchars($formData['Description_D']); ?></textarea>
                        <?php if(isset($errors['Description_D'])): ?>
                            <div class="error-message"><?php echo $errors['Description_D']; ?></div>
                        <?php else: ?>
                            <div class="help-text">Minimum 20 caractères</div>
                        <?php endif; ?>
                        <div class="error-message js-error" id="error-Description_D"></div>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['Objectif']) ? 'has-error' : ''; ?>">
                        <label for="Objectif">Objectif <span class="required">*</span></label>
                        <textarea id="Objectif" name="Objectif" class="form-control" rows="3"><?php echo htmlspecialchars($formData['Objectif']); ?></textarea>
                        <?php if(isset($errors['Objectif'])): ?>
                            <div class="error-message"><?php echo $errors['Objectif']; ?></div>
                        <?php endif; ?>
                        <div class="error-message js-error" id="error-Objectif"></div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">Paramètres du défi</h3>
                    
                    <div class="form-row">
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Statut_D']) ? 'has-error' : ''; ?>">
                                <label for="Statut_D">Statut <span class="required">*</span></label>
                                <select id="Statut_D" name="Statut_D" class="form-control">
                                    <option value="">Sélectionnez un statut</option>
                                    <option value="Actif" <?php echo $formData['Statut_D'] == 'Actif' ? 'selected' : ''; ?>>Actif</option>
                                    <option value="Inactif" <?php echo $formData['Statut_D'] == 'Inactif' ? 'selected' : ''; ?>>Inactif</option>
                                    <option value="À venir" <?php echo $formData['Statut_D'] == 'À venir' ? 'selected' : ''; ?>>À venir</option>
                                </select>
                                <?php if(isset($errors['Statut_D'])): ?>
                                    <div class="error-message"><?php echo $errors['Statut_D']; ?></div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Statut_D"></div>
                            </div>
                        </div>
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Difficulte']) ? 'has-error' : ''; ?>">
                                <label for="Difficulte">Difficulté <span class="required">*</span></label>
                                <select id="Difficulte" name="Difficulte" class="form-control">
                                    <option value="">Sélectionnez une difficulté</option>
                                    <option value="Facile" <?php echo $formData['Difficulte'] == 'Facile' ? 'selected' : ''; ?>>Facile</option>
                                    <option value="Intermédiaire" <?php echo $formData['Difficulte'] == 'Intermédiaire' ? 'selected' : ''; ?>>Intermédiaire</option>
                                    <option value="Difficile" <?php echo $formData['Difficulte'] == 'Difficile' ? 'selected' : ''; ?>>Difficile</option>
                                </select>
                                <?php if(isset($errors['Difficulte'])): ?>
                                    <div class="error-message"><?php echo $errors['Difficulte']; ?></div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Difficulte"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Date_Debut']) ? 'has-error' : ''; ?>">
                                <label for="Date_Debut">Date de début <span class="required">*</span></label>
                                <input type="date" id="Date_Debut" name="Date_Debut" class="form-control" 
                                      value="<?php echo htmlspecialchars($formData['Date_Debut']); ?>">
                                <?php if(isset($errors['Date_Debut'])): ?>
                                    <div class="error-message"><?php echo $errors['Date_Debut']; ?></div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Date_Debut"></div>
                            </div>
                        </div>
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Date_Fin']) ? 'has-error' : ''; ?>">
                                <label for="Date_Fin">Date de fin <span class="required">*</span></label>
                                <input type="date" id="Date_Fin" name="Date_Fin" class="form-control" 
                                      value="<?php echo htmlspecialchars($formData['Date_Fin']); ?>">
                                <?php if(isset($errors['Date_Fin'])): ?>
                                    <div class="error-message"><?php echo $errors['Date_Fin']; ?></div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Date_Fin"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['Id_Quartier']) ? 'has-error' : ''; ?>">
                        <label for="Id_Quartier">Quartier <span class="required">*</span></label>
                        <input type="number" id="Id_Quartier" name="Id_Quartier" class="form-control" 
                              value="<?php echo htmlspecialchars($formData['Id_Quartier']); ?>">
                        <?php if(isset($errors['Id_Quartier'])): ?>
                            <div class="error-message"><?php echo $errors['Id_Quartier']; ?></div>
                        <?php else: ?>
                            <div class="help-text">Entrez l'identifiant du quartier (nombre positif)</div>
                        <?php endif; ?>
                        <div class="error-message js-error" id="error-Id_Quartier"></div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-submit">Ajouter le défi</button>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Référence au formulaire
            const form = document.getElementById('defi-form');
            
            // Désactiver la validation native HTML5
            form.setAttribute('novalidate', 'novalidate');
            
            // Fonction pour afficher une erreur
            function showError(fieldId, message) {
                const errorElement = document.getElementById('error-' + fieldId);
                errorElement.textContent = message;
                errorElement.classList.add('visible');
                document.getElementById(fieldId).parentNode.classList.add('has-error');
                return false;
            }
            
            // Fonction pour masquer une erreur
            function hideError(fieldId) {
                const errorElement = document.getElementById('error-' + fieldId);
                errorElement.textContent = '';
                errorElement.classList.remove('visible');
                document.getElementById(fieldId).parentNode.classList.remove('has-error');
                return true;
            }
            
            // Validation du titre
            function validateTitre() {
                const titre = document.getElementById('Titre_D').value.trim();
                if (titre === '') {
                    return showError('Titre_D', 'Le titre du défi est obligatoire');
                } else if (titre.length < 5) {
                    return showError('Titre_D', 'Le titre doit contenir au moins 5 caractères');
                } else if (titre.length > 100) {
                    return showError('Titre_D', 'Le titre ne peut pas dépasser 100 caractères');
                }
                return hideError('Titre_D');
            }
            
            // Validation de la description
            function validateDescription() {
                const description = document.getElementById('Description_D').value.trim();
                if (description === '') {
                    return showError('Description_D', 'La description est obligatoire');
                } else if (description.length < 20) {
                    return showError('Description_D', 'La description doit contenir au moins 20 caractères');
                }
                return hideError('Description_D');
            }
            
            // Validation de l'objectif
            function validateObjectif() {
                const objectif = document.getElementById('Objectif').value.trim();
                if (objectif === '') {
                    return showError('Objectif', 'L\'objectif est obligatoire');
                }
                return hideError('Objectif');
            }
            
            // Validation des points verts
            function validatePoints() {
                const points = document.getElementById('Points_verts').value.trim();
                if (points === '') {
                    return showError('Points_verts', 'Le nombre de points est obligatoire');
                } else if (isNaN(points) || parseInt(points) < 1 || parseInt(points) > 1000) {
                    return showError('Points_verts', 'Le nombre de points doit être compris entre 1 et 1000');
                }
                return hideError('Points_verts');
            }
            
            // Validation du statut
            function validateStatut() {
                const statut = document.getElementById('Statut_D').value;
                if (statut === '') {
                    return showError('Statut_D', 'Veuillez sélectionner un statut');
                }
                return hideError('Statut_D');
            }
            
            // Validation de la date de début
            function validateDateDebut() {
                const dateDebut = document.getElementById('Date_Debut').value;
                if (dateDebut === '') {
                    return showError('Date_Debut', 'La date de début est obligatoire');
                }
                return hideError('Date_Debut');
            }
            
            // Validation de la date de fin
            function validateDateFin() {
                const dateFin = document.getElementById('Date_Fin').value;
                const dateDebut = document.getElementById('Date_Debut').value;
                
                if (dateFin === '') {
                    return showError('Date_Fin', 'La date de fin est obligatoire');
                }
                
                if (dateDebut !== '' && dateFin !== '') {
                    const debut = new Date(dateDebut);
                    const fin = new Date(dateFin);
                    if (fin < debut) {
                        return showError('Date_Fin', 'La date de fin doit être postérieure à la date de début');
                    }
                }
                
                return hideError('Date_Fin');
            }
            
            // Validation de la difficulté
            function validateDifficulte() {
                const difficulte = document.getElementById('Difficulte').value;
                if (difficulte === '') {
                    return showError('Difficulte', 'Veuillez sélectionner une difficulté');
                }
                return hideError('Difficulte');
            }
            
            // Validation du quartier
            function validateQuartier() {
                const quartier = document.getElementById('Id_Quartier').value.trim();
                if (quartier === '') {
                    return showError('Id_Quartier', 'L\'ID du quartier est obligatoire');
                } else if (isNaN(quartier) || parseInt(quartier) < 1) {
                    return showError('Id_Quartier', 'L\'ID du quartier doit être un nombre positif');
                }
                return hideError('Id_Quartier');
            }
            
            // Validation au changement des champs
            document.getElementById('Titre_D').addEventListener('input', validateTitre);
            document.getElementById('Description_D').addEventListener('input', validateDescription);
            document.getElementById('Objectif').addEventListener('input', validateObjectif);
            document.getElementById('Points_verts').addEventListener('input', validatePoints);
            document.getElementById('Statut_D').addEventListener('change', validateStatut);
            document.getElementById('Date_Debut').addEventListener('change', function() {
                validateDateDebut();
                validateDateFin(); // Re-valider la date de fin si la date de début change
            });
            document.getElementById('Date_Fin').addEventListener('change', validateDateFin);
            document.getElementById('Difficulte').addEventListener('change', validateDifficulte);
            document.getElementById('Id_Quartier').addEventListener('input', validateQuartier);
            
            // Validation à la soumission du formulaire
            form.addEventListener('submit', function(event) {
                // Empêcher la soumission par défaut
                event.preventDefault();
                
                // Valider tous les champs
                const titre = validateTitre();
                const description = validateDescription();
                const objectif = validateObjectif();
                const points = validatePoints();
                const statut = validateStatut();
                const dateDebut = validateDateDebut();
                const dateFin = validateDateFin();
                const difficulte = validateDifficulte();
                const quartier = validateQuartier();
                
                // Si tous les champs sont valides, soumettre le formulaire
                if (titre && description && objectif && points && statut && 
                    dateDebut && dateFin && difficulte && quartier) {
                    // Soumission du formulaire
                    form.submit();
                } else {
                    // Scroll vers la première erreur
                    const firstErrorElement = document.querySelector('.js-error.visible');
                    if (firstErrorElement) {
                        firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
    </script>
</body>
</html> 