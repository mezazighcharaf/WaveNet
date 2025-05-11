<?php
session_start();

require_once __DIR__ . '/../../../controller/DefiController.php';

// Initialize controller
$defiController = new DefiController();

// Check if ID is provided
if(!isset($_GET['id'])) {
    header('Location: index.php?message=ID du défi non spécifié');
    exit();
}

$id = $_GET['id'];
$defi = $defiController->getDefi($id);

if(!$defi) {
    header('Location: index.php?message=Défi non trouvé');
    exit();
}

// Initialisation des variables d'erreur
$errors = [];
$formData = [
    'Titre_D' => $defi->Titre_D,
    'Description_D' => $defi->Description_D,
    'Objectif' => $defi->Objectif,
    'Points_verts' => $defi->Points_verts,
    'Date_Debut' => $defi->Date_Debut,
    'Date_Fin' => $defi->Date_Fin,
    'Difficulte' => $defi->Difficulte,
    'Id_Quartier' => $defi->Id_Quartier
];

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération et nettoyage des données
    $formData = [
        'Titre_D' => trim(htmlspecialchars($_POST['Titre_D'] ?? '')),
        'Description_D' => trim(htmlspecialchars($_POST['Description_D'] ?? '')),
        'Objectif' => trim(htmlspecialchars($_POST['Objectif'] ?? '')),
        'Points_verts' => $_POST['Points_verts'] ?? '',
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
    
    // Si aucune erreur, mise à jour du défi
    if(empty($errors)) {
        // Update defi
        if($defiController->updateDefi($id, $formData)) {
            header('Location: index.php?message=Défi mis à jour avec succès');
            exit();
        } else {
            $error = "Erreur lors de la mise à jour du défi";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un défi - Urbaverse</title>
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
            <h1>Modifier un défi</h1>
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

        <div class="defi-form">
            <h2 class="form-title">Modifier le défi #<?php echo $defi->Id_Defi; ?></h2>
            
            <form action="" method="post" class="form-style" id="defi-form" novalidate>
                <input type="hidden" name="Id_Defi" value="<?php echo $defi->Id_Defi; ?>">
                
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
                                      value="<?php echo htmlspecialchars($defi->Titre_D); ?>">
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
                                       value="<?php echo htmlspecialchars($formData['Points_verts']); ?>" min="1" max="1000" required>
                                <?php if(isset($errors['Points_verts'])): ?>
                                    <div class="error-message"><?php echo $errors['Points_verts']; ?></div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Points_verts"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['Description_D']) ? 'has-error' : ''; ?>">
                        <label for="Description_D">Description <span class="required">*</span></label>
                        <textarea id="Description_D" name="Description_D" class="form-control" rows="4"><?php echo htmlspecialchars($defi->Description_D); ?></textarea>
                        <?php if(isset($errors['Description_D'])): ?>
                            <div class="error-message"><?php echo $errors['Description_D']; ?></div>
                        <?php else: ?>
                            <div class="help-text">Minimum 20 caractères</div>
                        <?php endif; ?>
                        <div class="error-message js-error" id="error-Description_D"></div>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['Objectif']) ? 'has-error' : ''; ?>">
                        <label for="Objectif">Objectif <span class="required">*</span></label>
                        <textarea id="Objectif" name="Objectif" class="form-control" rows="3"><?php echo htmlspecialchars($defi->Objectif); ?></textarea>
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
                            <div class="form-group <?php echo isset($errors['Date_Debut']) ? 'has-error' : ''; ?>">
                                <label for="Date_Debut">Date de début <span class="required">*</span></label>
                                <input type="date" id="Date_Debut" name="Date_Debut" class="form-control" 
                                      value="<?php echo htmlspecialchars($defi->Date_Debut); ?>">
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
                                      value="<?php echo htmlspecialchars($defi->Date_Fin); ?>">
                                <?php if(isset($errors['Date_Fin'])): ?>
                                    <div class="error-message"><?php echo $errors['Date_Fin']; ?></div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Date_Fin"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
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
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Id_Quartier']) ? 'has-error' : ''; ?>">
                                <label for="Id_Quartier">Quartier <span class="required">*</span></label>
                                <input type="number" id="Id_Quartier" name="Id_Quartier" class="form-control" 
                                      value="<?php echo htmlspecialchars($defi->Id_Quartier); ?>">
                                <?php if(isset($errors['Id_Quartier'])): ?>
                                    <div class="error-message"><?php echo $errors['Id_Quartier']; ?></div>
                                <?php else: ?>
                                    <div class="help-text">Entrez l'identifiant du quartier (nombre positif)</div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Id_Quartier"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-submit">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('defi-form');
            const fields = {
                'Titre_D': {
                    min: 5,
                    max: 100,
                    required: true,
                    errorMessages: {
                        required: 'Le titre du défi est obligatoire',
                        min: 'Le titre doit contenir au moins 5 caractères',
                        max: 'Le titre ne peut pas dépasser 100 caractères'
                    }
                },
                'Description_D': {
                    min: 20,
                    required: true,
                    errorMessages: {
                        required: 'La description est obligatoire',
                        min: 'La description doit contenir au moins 20 caractères'
                    }
                },
                'Objectif': {
                    required: true,
                    errorMessages: {
                        required: 'L\'objectif est obligatoire'
                    }
                },
                'Points_verts': {
                    required: true,
                    min: 1,
                    max: 1000,
                    number: true,
                    errorMessages: {
                        required: 'Le nombre de points est obligatoire',
                        number: 'Veuillez entrer un nombre valide',
                        min: 'Le nombre de points doit être au moins 1',
                        max: 'Le nombre de points ne peut pas dépasser 1000'
                    }
                },
                'Date_Debut': {
                    required: true,
                    errorMessages: {
                        required: 'La date de début est obligatoire'
                    }
                },
                'Date_Fin': {
                    required: true,
                    errorMessages: {
                        required: 'La date de fin est obligatoire',
                        dateComparison: 'La date de fin doit être postérieure à la date de début'
                    }
                },
                'Difficulte': {
                    required: true,
                    errorMessages: {
                        required: 'La difficulté est obligatoire'
                    }
                },
                'Id_Quartier': {
                    required: true,
                    number: true,
                    min: 1,
                    errorMessages: {
                        required: 'L\'ID du quartier est obligatoire',
                        number: 'Veuillez entrer un nombre valide',
                        min: 'L\'ID du quartier doit être un nombre positif'
                    }
                }
            };

            // Ajouter les écouteurs d'événements pour la validation en temps réel
            Object.keys(fields).forEach(function(fieldName) {
                const field = document.getElementById(fieldName);
                if (field) {
                    field.addEventListener('blur', function() {
                        validateField(fieldName);
                    });
                    field.addEventListener('input', function() {
                        // Masquer l'erreur pendant que l'utilisateur tape
                        const errorElement = document.getElementById('error-' + fieldName);
                        if (errorElement) {
                            errorElement.classList.remove('visible');
                        }
                    });
                }
            });

            // Validation du formulaire avant soumission
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Valider tous les champs
                Object.keys(fields).forEach(function(fieldName) {
                    if (!validateField(fieldName)) {
                        isValid = false;
                    }
                });
                
                // Vérifier que la date de fin est postérieure à la date de début
                const dateDebut = document.getElementById('Date_Debut').value;
                const dateFin = document.getElementById('Date_Fin').value;
                
                if (dateDebut && dateFin) {
                    const dateDebutObj = new Date(dateDebut);
                    const dateFinObj = new Date(dateFin);
                    
                    if (dateFinObj < dateDebutObj) {
                        showError('Date_Fin', fields['Date_Fin'].errorMessages.dateComparison);
                        isValid = false;
                    }
                }
                
                // Empêcher la soumission si le formulaire n'est pas valide
                if (!isValid) {
                    event.preventDefault();
                }
            });

            // Fonction pour valider un champ
            function validateField(fieldName) {
                const field = document.getElementById(fieldName);
                const value = field.value.trim();
                const fieldRules = fields[fieldName];
                let isValid = true;
                let errorMessage = '';
                
                // Vérifier si le champ est requis
                if (fieldRules.required && value === '') {
                    isValid = false;
                    errorMessage = fieldRules.errorMessages.required;
                }
                // Vérifier la longueur minimale
                else if (fieldRules.min && value.length < fieldRules.min && value !== '') {
                    isValid = false;
                    errorMessage = fieldRules.errorMessages.min;
                }
                // Vérifier la longueur maximale
                else if (fieldRules.max && value.length > fieldRules.max) {
                    isValid = false;
                    errorMessage = fieldRules.errorMessages.max;
                }
                // Vérifier si c'est un nombre
                else if (fieldRules.number && isNaN(Number(value)) && value !== '') {
                    isValid = false;
                    errorMessage = fieldRules.errorMessages.number;
                }
                // Vérifier la valeur minimale pour les nombres
                else if (fieldRules.number && fieldRules.min && Number(value) < fieldRules.min && value !== '') {
                    isValid = false;
                    errorMessage = fieldRules.errorMessages.min;
                }
                // Vérifier la valeur maximale pour les nombres
                else if (fieldRules.number && fieldRules.max && Number(value) > fieldRules.max) {
                    isValid = false;
                    errorMessage = fieldRules.errorMessages.max;
                }
                
                // Afficher ou masquer le message d'erreur
                if (!isValid) {
                    showError(fieldName, errorMessage);
                } else {
                    hideError(fieldName);
                }
                
                return isValid;
            }

            // Fonction pour afficher une erreur
            function showError(fieldName, message) {
                const errorElement = document.getElementById('error-' + fieldName);
                const fieldElement = document.getElementById(fieldName);
                const formGroup = fieldElement.closest('.form-group');
                
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.classList.add('visible');
                }
                
                if (formGroup) {
                    formGroup.classList.add('has-error');
                }
            }

            // Fonction pour masquer une erreur
            function hideError(fieldName) {
                const errorElement = document.getElementById('error-' + fieldName);
                const fieldElement = document.getElementById(fieldName);
                const formGroup = fieldElement.closest('.form-group');
                
                if (errorElement) {
                    errorElement.textContent = '';
                    errorElement.classList.remove('visible');
                }
                
                if (formGroup) {
                    formGroup.classList.remove('has-error');
                }
            }
        });
    </script>
</body>
</html> 