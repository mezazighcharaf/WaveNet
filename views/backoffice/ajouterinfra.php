<?php
include_once(__DIR__ . '/../../Controller/infraC.php');
include_once(__DIR__ . '/../../models/infra.php');

$message = null;
$infraC = new infraC();
$quartiers = $infraC->getQuartiers(); // Récupère les quartiers disponibles

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST['type'];
    $statut = $_POST['statut'];
    $idq = $_POST['idq'] ?? null; // Récupère l'id du quartier

    if (!empty($type) && !empty($statut) && !empty($idq)) {
        
        if (!preg_match('/^[\p{L} ]+$/u', $type)) {
            $message = "Le type doit contenir uniquement des lettres ❌";
        }
        elseif (!preg_match('/^[\p{L} ]+$/u', $statut)) {
            $message = "Le statut doit contenir uniquement des lettres ❌";
        }
        else {
            // Utiliser la méthode ajouterInfrastructure existante avec un objet infra
            // L'ID sera généré automatiquement par la base de données
            $infrastructure = new infra(0, $type, $statut); // On met 0 comme ID temporaire
            $infrastructure->setIdq($idq);
            $infraC->ajouterInfrastructure($infrastructure);
            $message = "Infrastructure ajoutée avec succès ✅";
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
    <title>WaveNet Admin - Infrastructures</title>
    <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css">
    <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
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
                <li><a href="/WaveNet/views/backoffice/Gquartier.php"><i class="fas fa-map-marker-alt"></i> Quartiers</a></li>
                <li><a href="/WaveNet/views/backoffice/backinfra.php" class="active"><i class="fas fa-building"></i> Infrastructures</a></li>
                <li><a href="/WaveNet/views/backoffice/gsignalement.php"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
                <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <h1>Ajouter une Infrastructure</h1>
            <div>
                <a href="/WaveNet/views/backoffice/backinfra.php" class="btn btn-secondary">← Retour</a>
            </div>
        </div>

        <section class="backoffice-section">
            <?php if ($message): ?>
                <div class="alert <?= strpos($message, '❌') ? 'alert-error' : 'alert-success' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" action="" novalidate>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <input type="text" id="type" name="type" required>
                    </div>

                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <input type="text" id="statut" name="statut" required>
                    </div>

                    <div class="form-group">
                        <label for="idq">Quartier</label>
                        <select id="idq" name="idq" required>
                            <option value="">Sélectionnez un quartier</option>
                            <?php foreach ($quartiers as $quartier): ?>
                                <option value="<?= htmlspecialchars($quartier['idq']) ?>">
                                    <?= htmlspecialchars($quartier['nomq'] . (isset($quartier['ville']) ? ' - ' . $quartier['ville'] : '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
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