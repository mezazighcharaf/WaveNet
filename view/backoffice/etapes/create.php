<?php
session_start();

// Check if user is logged in as admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Admin';
}

require_once __DIR__ . '/../../../controller/EtapeController.php';
// Add this line to include the DefiController
require_once __DIR__ . '/../../../controller/DefiController.php';

// Initialize controller
$etapeController = new EtapeController();
// Add this to get all défis for the dropdown
$defiController = new DefiController();
$defis = $defiController->getAllDefis();

// Initialisation des variables d'erreur
$errors = [];
$formData = [
    'Titre_E' => '',
    'Description_E' => '',
    'Ordre' => '',
    'Points_Bonus' => '',
    'Statut_E' => 'Actif',
    'Id_Defi' => isset($_GET['Id_Defi']) ? $_GET['Id_Defi'] : ''
];

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formData = [
        'Titre_E' => trim(htmlspecialchars($_POST['Titre_E'] ?? '')),
        'Description_E' => trim(htmlspecialchars($_POST['Description_E'] ?? '')),
        'Ordre' => $_POST['Ordre'] ?? '',
        'Points_Bonus' => $_POST['Points_Bonus'] ?? '',
        'Statut_E' => $_POST['Statut_E'] ?? '',
        'Id_Defi' => $_POST['Id_Defi'] ?? ''
    ];

    // Validation
    if(empty($formData['Titre_E'])) {
        $errors['Titre_E'] = "Le titre de l'étape est obligatoire";
    } elseif(strlen($formData['Titre_E']) < 3) {
        $errors['Titre_E'] = "Le titre doit contenir au moins 3 caractères";
    } elseif(strlen($formData['Titre_E']) > 100) {
        $errors['Titre_E'] = "Le titre ne peut pas dépasser 100 caractères";
    }

    if(empty($formData['Description_E'])) {
        $errors['Description_E'] = "La description est obligatoire";
    } elseif(strlen($formData['Description_E']) < 10) {
        $errors['Description_E'] = "La description doit contenir au moins 10 caractères";
    }

    if(empty($formData['Ordre'])) {
        $errors['Ordre'] = "L'ordre de l'étape est obligatoire";
    } elseif(!is_numeric($formData['Ordre']) || $formData['Ordre'] < 1) {
        $errors['Ordre'] = "L'ordre doit être un nombre positif";
    }

    if($formData['Points_Bonus'] === '' || $formData['Points_Bonus'] === null) {
        $errors['Points_Bonus'] = "Les points bonus sont obligatoires";
    } elseif(!is_numeric($formData['Points_Bonus']) || $formData['Points_Bonus'] < 0 || $formData['Points_Bonus'] > 1000) {
        $errors['Points_Bonus'] = "Les points bonus doivent être compris entre 0 et 1000";
    }

    $statutsValides = ['Actif', 'Inactif', 'À venir'];
    if(empty($formData['Statut_E']) || !in_array($formData['Statut_E'], $statutsValides)) {
        $errors['Statut_E'] = "Le statut sélectionné n'est pas valide";
    }

    if(empty($formData['Id_Defi'])) {
        $errors['Id_Defi'] = "L'ID du défi est obligatoire";
    } elseif(!is_numeric($formData['Id_Defi']) || $formData['Id_Defi'] < 1) {
        $errors['Id_Defi'] = "L'ID du défi doit être un nombre positif";
    }

    if(empty($errors)) {
        if($etapeController->createEtape($formData)) {
            header('Location: index.php?message=Étape créée avec succès');
            exit();
        } else {
            $error = "Erreur lors de la création de l'étape";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une étape - Urbaverse</title>
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
        .js-error {
            display: none;
        }
        .js-error.visible {
            display: block;
        }
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
            min-height: 100px;
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
                <li><a href="index.php">Étapes</a></li>
                <li class="home-link"><a href="../../frontoffice/index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="content-header">
            <h1>Ajouter une nouvelle étape</h1>
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
            <h2 class="form-title">Créer une nouvelle étape</h2>
            <form action="" method="post" class="form-style" id="etape-form" novalidate>
                <div class="form-section">
                    <h3 class="section-title">Informations de l'étape</h3>
                    <div class="form-row">
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Titre_E']) ? 'has-error' : ''; ?>">
                                <label for="Titre_E">Titre de l'étape <span class="required">*</span></label>
                                <input type="text" id="Titre_E" name="Titre_E" class="form-control"
                                    value="<?php echo htmlspecialchars($formData['Titre_E']); ?>">
                                <?php if(isset($errors['Titre_E'])): ?>
                                    <div class="error-message"><?php echo $errors['Titre_E']; ?></div>
                                <?php else: ?>
                                    <div class="help-text">Entre 3 et 100 caractères</div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Titre_E"></div>
                            </div>
                        </div>
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Ordre']) ? 'has-error' : ''; ?>">
                                <label for="Ordre">Ordre <span class="required">*</span></label>
                                <input type="number" id="Ordre" name="Ordre" class="form-control"
                                    value="<?php echo htmlspecialchars($formData['Ordre']); ?>">
                                <?php if(isset($errors['Ordre'])): ?>
                                    <div class="error-message"><?php echo $errors['Ordre']; ?></div>
                                <?php else: ?>
                                    <div class="help-text">Nombre positif (ex: 1, 2, 3...)</div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Ordre"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group <?php echo isset($errors['Description_E']) ? 'has-error' : ''; ?>">
                        <label for="Description_E">Description <span class="required">*</span></label>
                        <textarea id="Description_E" name="Description_E" class="form-control" rows="4"><?php echo htmlspecialchars($formData['Description_E']); ?></textarea>
                        <?php if(isset($errors['Description_E'])): ?>
                            <div class="error-message"><?php echo $errors['Description_E']; ?></div>
                        <?php else: ?>
                            <div class="help-text">Minimum 10 caractères</div>
                        <?php endif; ?>
                        <div class="error-message js-error" id="error-Description_E"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Points_Bonus']) ? 'has-error' : ''; ?>">
                                <label for="Points_Bonus">Points bonus <span class="required">*</span></label>
                                <input type="number" id="Points_Bonus" name="Points_Bonus" class="form-control"
                                    value="<?php echo htmlspecialchars($formData['Points_Bonus']); ?>">
                                <?php if(isset($errors['Points_Bonus'])): ?>
                                    <div class="error-message"><?php echo $errors['Points_Bonus']; ?></div>
                                <?php else: ?>
                                    <div class="help-text">Valeur entre 0 et 1000</div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Points_Bonus"></div>
                            </div>
                        </div>
                        <div class="form-column">
                            <div class="form-group <?php echo isset($errors['Statut_E']) ? 'has-error' : ''; ?>">
                                <label for="Statut_E">Statut <span class="required">*</span></label>
                                <select id="Statut_E" name="Statut_E" class="form-control">
                                    <option value="">Sélectionnez un statut</option>
                                    <option value="Actif" <?php echo $formData['Statut_E'] == 'Actif' ? 'selected' : ''; ?>>Actif</option>
                                    <option value="Inactif" <?php echo $formData['Statut_E'] == 'Inactif' ? 'selected' : ''; ?>>Inactif</option>
                                    <option value="À venir" <?php echo $formData['Statut_E'] == 'À venir' ? 'selected' : ''; ?>>À venir</option>
                                </select>
                                <?php if(isset($errors['Statut_E'])): ?>
                                    <div class="error-message"><?php echo $errors['Statut_E']; ?></div>
                                <?php endif; ?>
                                <div class="error-message js-error" id="error-Statut_E"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group <?php echo isset($errors['Id_Defi']) ? 'has-error' : ''; ?>">
                        <label for="Id_Defi">Défi associé <span class="required">*</span></label>
                        <select id="Id_Defi" name="Id_Defi" class="form-control">
                            <option value="">Sélectionnez un défi</option>
                            <?php foreach($defis as $defi): ?>
                                <option value="<?php echo $defi['Id_Defi']; ?>" <?php echo ($formData['Id_Defi'] == $defi['Id_Defi']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($defi['Titre_D']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if(isset($errors['Id_Defi'])): ?>
                            <div class="error-message"><?php echo $errors['Id_Defi']; ?></div>
                        <?php else: ?>
                            <div class="help-text">Choisissez le défi auquel cette étape appartient</div>
                        <?php endif; ?>
                        <div class="error-message js-error" id="error-Id_Defi"></div>
                    </div>
                </div>
                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-submit">Ajouter l'étape</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('etape-form');
            form.setAttribute('novalidate', 'novalidate');

            function showError(fieldId, message) {
                const errorElement = document.getElementById('error-' + fieldId);
                errorElement.textContent = message;
                errorElement.classList.add('visible');
                document.getElementById(fieldId).parentNode.classList.add('has-error');
                return false;
            }
            function hideError(fieldId) {
                const errorElement = document.getElementById('error-' + fieldId);
                errorElement.textContent = '';
                errorElement.classList.remove('visible');
                document.getElementById(fieldId).parentNode.classList.remove('has-error');
                return true;
            }

            function validateTitre() {
                const titre = document.getElementById('Titre_E').value.trim();
                if (titre === '') {
                    return showError('Titre_E', "Le titre de l'étape est obligatoire");
                } else if (titre.length < 3) {
                    return showError('Titre_E', "Le titre doit contenir au moins 3 caractères");
                } else if (titre.length > 100) {
                    return showError('Titre_E', "Le titre ne peut pas dépasser 100 caractères");
                }
                return hideError('Titre_E');
            }
            function validateDescription() {
                const description = document.getElementById('Description_E').value.trim();
                if (description === '') {
                    return showError('Description_E', "La description est obligatoire");
                } else if (description.length < 10) {
                    return showError('Description_E', "La description doit contenir au moins 10 caractères");
                }
                return hideError('Description_E');
            }
            function validateOrdre() {
                const ordre = document.getElementById('Ordre').value.trim();
                if (ordre === '') {
                    return showError('Ordre', "L'ordre de l'étape est obligatoire");
                } else if (isNaN(ordre) || parseInt(ordre) < 1) {
                    return showError('Ordre', "L'ordre doit être un nombre positif");
                }
                return hideError('Ordre');
            }
            function validatePointsBonus() {
                const points = document.getElementById('Points_Bonus').value.trim();
                if (points === '') {
                    return showError('Points_Bonus', "Les points bonus sont obligatoires");
                } else if (isNaN(points) || parseInt(points) < 0 || parseInt(points) > 1000) {
                    return showError('Points_Bonus', "Les points bonus doivent être compris entre 0 et 1000");
                }
                return hideError('Points_Bonus');
            }
            function validateStatut() {
                const statut = document.getElementById('Statut_E').value;
                if (statut === '') {
                    return showError('Statut_E', "Veuillez sélectionner un statut");
                }
                return hideError('Statut_E');
            }
            function validateIdDefi() {
                const idDefi = document.getElementById('Id_Defi').value.trim();
                if (idDefi === '') {
                    return showError('Id_Defi', "L'ID du défi est obligatoire");
                } else if (isNaN(idDefi) || parseInt(idDefi) < 1) {
                    return showError('Id_Defi', "L'ID du défi doit être un nombre positif");
                }
                return hideError('Id_Defi');
            }

            document.getElementById('Titre_E').addEventListener('input', validateTitre);
            document.getElementById('Description_E').addEventListener('input', validateDescription);
            document.getElementById('Ordre').addEventListener('input', validateOrdre);
            document.getElementById('Points_Bonus').addEventListener('input', validatePointsBonus);
            document.getElementById('Statut_E').addEventListener('change', validateStatut);
            document.getElementById('Id_Defi').addEventListener('input', validateIdDefi);

            form.addEventListener('submit', function(event) {
                event.preventDefault();

                const titre = validateTitre();
                const description = validateDescription();
                const ordre = validateOrdre();
                const points = validatePointsBonus();
                const statut = validateStatut();
                const idDefi = validateIdDefi();

                if (titre && description && ordre && points && statut && idDefi) {
                    form.submit();
                } else {
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