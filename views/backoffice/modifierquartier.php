<?php
include_once "../../Controller/quartierC.php";
// Remove the include for the non-existent Model file
// include_once "../../Model/quartier.php";

$quartierC = new quartierC();
$e = null;
$message = null;

if (isset($_GET['id'])) {
    $e = $quartierC->recupererQuartierParId($_GET['id']);
}

if (
    isset($_POST['idq']) &&
    isset($_POST['nomq']) &&
    isset($_POST['ville']) &&
    isset($_POST['scoreeco']) &&
    isset($_POST['classement']) &&
    isset($_POST['localisation']) 
) {
    $idq = $_POST['idq'];
    $nomq = $_POST['nomq'];
    $ville = $_POST['ville'];
    $scoreeco = $_POST['scoreeco'];
    $classement = $_POST['classement'];
    $localisation = $_POST['localisation'];
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : "";
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : "";

    if (!preg_match('/^\d{8}$/', $idq)) {
        $message = "L'identifiant doit contenir exactement 8 chiffres ❌";
    }
    elseif (!preg_match('/^[\p{L} ]+$/u', $nomq)) {
        $message = "Le nom doit contenir uniquement des lettres ❌";
    }
    elseif (!preg_match('/^[\p{L} ]+$/u', $ville)) {
        $message = "La ville doit contenir uniquement des lettres ❌";
    }
    elseif (!is_numeric($scoreeco)) {
        $message = "scoreeco doit être un nombre ❌";
    }
    elseif (!is_numeric($classement)) {
        $message = "classement doit être un nombre ❌";
    } else {
        // Update the quartier using the Controller's method
        $quartierC->modifierQuartier($idq, $nomq, $ville, $scoreeco, $classement, $localisation, $latitude, $longitude);
        header('Location: Gquartier.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URBAVERSE Admin - Quartiers</title>
    <link rel="stylesheet" href="../assets/css/style11.css">
    <link rel="stylesheet" href="css/backoffice.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <li><a href="/WaveNet/views/backoffice/Gquartier.php" class="active"><i class="fas fa-map-marker-alt"></i> Quartiers</a></li>
                <li><a href="/WaveNet/views/backoffice/backinfra.php"><i class="fas fa-building"></i> Infrastructures</a></li>
                <li><a href="/WaveNet/views/backoffice/gsignalement.php"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
                <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <h1>Modifier Quartier</h1>
        </div>

        <section class="backoffice-section">
            <?php if ($message): ?>
                <div class="alert <?= strpos($message, '❌') ? 'alert-error' : 'alert-success' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="idq">ID Quartier</label>
                        <input type="text" id="idq" name="idq" value="<?= htmlspecialchars($e['idq'] ?? '') ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label for="nomq">Nom du quartier</label>
                        <input type="text" id="nomq" name="nomq" value="<?= htmlspecialchars($e['nomq'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" value="<?= isset($e['ville']) ? htmlspecialchars($e['ville']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="scoreeco">Score écologique (0-100)</label>
                        <input type="number" id="scoreeco" name="scoreeco" min="0" max="100" value="<?= htmlspecialchars($e['scoreeco'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="classement">Classement</label>
                        <input type="number" id="classement" name="classement" value="<?= isset($e['classement']) ? htmlspecialchars($e['classement']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="localisation">Localisation (Adresse complète)</label>
                        <input type="text" id="localisation" name="localisation" value="<?= isset($e['localisation']) ? htmlspecialchars($e['localisation']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="text" id="latitude" name="latitude" value="<?= isset($e['latitude']) ? htmlspecialchars($e['latitude']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="text" id="longitude" name="longitude" value="<?= isset($e['longitude']) ? htmlspecialchars($e['longitude']) : '' ?>">
                    </div>
                    <div class="form-actions">
                        <a href="Gquartier.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>