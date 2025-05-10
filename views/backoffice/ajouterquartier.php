<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier l'inclusion du fichier
$quartierCFile = __DIR__ . '/../../controller/quartierC.php';
if (!file_exists($quartierCFile)) {
    die("Erreur : Le fichier $quartierCFile n'existe pas.");
}
include_once($quartierCFile);

// Vérifier si la classe existe
if (!class_exists('quartierC')) {
    die("Erreur : La classe quartierC n'est pas définie.");
}

$message = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nomq = $_POST['nomq'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $scoreeco = $_POST['scoreeco'] ?? '';
    $classement = $_POST['classement'] ?? '';
    $localisation = $_POST['localisation'] ?? '';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';

    if (
        !empty($nomq) && !empty($ville) &&
        !empty($scoreeco) && !empty($classement) && !empty($localisation)
    ) {
        if (!preg_match('/^[\p{L} ]+$/u', $nomq)) {
            $message = "Le nom du quartier doit contenir uniquement des lettres ❌";
        } elseif (!preg_match('/^[\p{L} ]+$/u', $ville)) {
            $message = "La ville doit contenir uniquement des lettres ❌";
        } elseif (!is_numeric($scoreeco)) {
            $message = "Le score écologique doit être un nombre ❌";
        } elseif (!is_numeric($classement)) {
            $message = "Le classement doit être un nombre ❌";
        } else {
            $quartierC = new quartierC();
            // Vérifier si la méthode existe
            if (!method_exists($quartierC, 'ajouterQuartierAutoIncrement')) {
                die("Erreur : La méthode ajouterQuartierAutoIncrement n'existe pas dans la classe quartierC.");
            }
            $result = $quartierC->ajouterQuartierAutoIncrement($nomq, $ville, $scoreeco, $classement, $localisation, $latitude, $longitude);
            if ($result) {
                $message = "Quartier ajouté avec succès ✅ ID: $result";
            } else {
                $message = "Échec de l'ajout du quartier ❌";
            }
        }
    } else {
        $message = "Tous les champs sont requis ❌";
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
            <h1>Ajouter un Quartier</h1>
            <div>
                <a href="Gquartier.php" class="btn btn-secondary">← Retour</a>
            </div>
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
                        <label for="nomq">Nom du quartier</label>
                        <input type="text" id="nomq" name="nomq" value="<?= htmlspecialchars($nomq ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" value="<?= htmlspecialchars($ville ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="scoreeco">Score écologique (0-100)</label>
                        <input type="number" id="scoreeco" name="scoreeco" min="0" max="100" value="<?= htmlspecialchars($scoreeco ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="classement">Classement</label>
                        <input type="number" id="classement" name="classement" value="<?= htmlspecialchars($classement ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="localisation">Localisation (Adresse complète)</label>
                        <input type="text" id="localisation" name="localisation" value="<?= htmlspecialchars($localisation ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="latitude">Latitude</label>
                        <input type="text" id="latitude" name="latitude" value="<?= htmlspecialchars($latitude ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="longitude">Longitude</label>
                        <input type="text" id="longitude" name="longitude" value="<?= htmlspecialchars($longitude ?? '') ?>">
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>