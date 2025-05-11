<?php
session_start();

require_once __DIR__ . '/../../../controller/EtapeController.php';
require_once __DIR__ . '/../../../controller/DefiController.php';

// Initialize controllers
$etapeController = new EtapeController();
$defiController = new DefiController();

// Get all défis for the dropdown
$defis = $defiController->getAllDefis();

// Check if ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=ID de l\'étape non spécifié');
    exit();
}

$id = $_GET['id'];

// Get the etape data
$etape = $etapeController->getEtapeById($id);

// If etape not found, redirect with error
if(!$etape) {
    header('Location: index.php?error=Étape non trouvée');
    exit();
}

// Initialize form data with etape values
$formData = [
    'Id_etape' => $etape['Id_etape'],
    'Titre_E' => $etape['Titre_E'],
    'Description_E' => $etape['Description_E'],
    'Ordre' => $etape['Ordre'],
    'Points_Bonus' => $etape['Points_Bonus'],
    'Id_Defi' => $etape['Id_Defi']
];

// Initialize errors array
$errors = [];

// Process form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $formData = [
        'Id_etape' => $id,
        'Titre_E' => trim(htmlspecialchars($_POST['Titre_E'] ?? '')),
        'Description_E' => trim(htmlspecialchars($_POST['Description_E'] ?? '')),
        'Ordre' => $_POST['Ordre'] ?? '',
        'Points_Bonus' => $_POST['Points_Bonus'] ?? '',
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

    if(empty($formData['Id_Defi'])) {
        $errors['Id_Defi'] = "L'ID du défi est obligatoire";
    } elseif(!is_numeric($formData['Id_Defi']) || $formData['Id_Defi'] < 1) {
        $errors['Id_Defi'] = "L'ID du défi doit être un nombre positif";
    }

    if(empty($errors)) {
        if($etapeController->updateEtape($formData)) {
            header('Location: index.php?message=Étape mise à jour avec succès');
            exit();
        } else {
            $error = "Erreur lors de la mise à jour de l'étape";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une étape - Urbaverse</title>
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
                <li><a href="../dashboard/index.php">Dashboard</a></li>
                <li><a href="../defis/index.php">Défis</a></li>
                <li><a href="index.php">Étapes</a></li>
                <li class="home-link"><a href="../../frontoffice/index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="content-header">
            <h1>Modifier une étape</h1>
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
            <h2 class="form-title">Modifier l'étape</h2>
            <form action="" method="post" class="form-style" id="etape-form" novalidate>
                <div class="form-group <?php echo isset($errors['Titre_E']) ? 'has-error' : ''; ?>">
                    <label for="Titre_E">Titre de l'étape <span class="required">*</span></label>
                    <input type="text" id="Titre_E" name="Titre_E" class="form-control" value="<?php echo htmlspecialchars($formData['Titre_E']); ?>" required>
                    <?php if(isset($errors['Titre_E'])): ?>
                        <span class="error-message"><?php echo $errors['Titre_E']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?php echo isset($errors['Description_E']) ? 'has-error' : ''; ?>">
                    <label for="Description_E">Description <span class="required">*</span></label>
                    <textarea id="Description_E" name="Description_E" class="form-control" required><?php echo htmlspecialchars($formData['Description_E']); ?></textarea>
                    <?php if(isset($errors['Description_E'])): ?>
                        <span class="error-message"><?php echo $errors['Description_E']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?php echo isset($errors['Ordre']) ? 'has-error' : ''; ?>">
                    <label for="Ordre">Ordre <span class="required">*</span></label>
                    <input type="number" id="Ordre" name="Ordre" class="form-control" value="<?php echo htmlspecialchars($formData['Ordre']); ?>" min="1" required>
                    <?php if(isset($errors['Ordre'])): ?>
                        <span class="error-message"><?php echo $errors['Ordre']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?php echo isset($errors['Points_Bonus']) ? 'has-error' : ''; ?>">
                    <label for="Points_Bonus">Points bonus <span class="required">*</span></label>
                    <input type="number" id="Points_Bonus" name="Points_Bonus" class="form-control" value="<?php echo htmlspecialchars($formData['Points_Bonus']); ?>" min="0" max="1000" required>
                    <?php if(isset($errors['Points_Bonus'])): ?>
                        <span class="error-message"><?php echo $errors['Points_Bonus']; ?></span>
                    <?php endif; ?>
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
                    <?php endif; ?>
                    <div class="error-message js-error" id="error-Id_Defi"></div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-submit">Mettre à jour</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>