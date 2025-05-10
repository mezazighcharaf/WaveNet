<?php
include_once "../../Controller/infraC.php";
// Commentons cette ligne car le Model pourrait ne pas exister
// include_once "../../Model/infra.php";

$infraC = new infraC();
$e = null;
$message = null;

if (isset($_GET['id'])) {
    $e = $infraC->recupererInfrastructureParId($_GET['id']);
}

if (
    isset($_POST['id_infra']) &&
    isset($_POST['type']) &&
    isset($_POST['statut']) 
) {
    $id_infra = $_POST['id_infra'];
    $type = $_POST['type'];
    $statut = $_POST['statut'];
    $idq = isset($_POST['idq']) ? $_POST['idq'] : null;

    if (!preg_match('/^\d+$/', $id_infra)) {
        $message = "L'identifiant doit contenir uniquement des chiffres ❌";
    }
    
    elseif (!preg_match('/^[\p{L} ]+$/u', $type)) {
        $message = "Le type doit contenir uniquement des lettres ❌";
    }
    
    elseif (!preg_match('/^[\p{L} ]+$/u', $statut)) {
        $message = "Le statut doit contenir uniquement des lettres ❌";
    } else {
        // Mise à jour sans utiliser la classe modèle
        $infraC->modifierInfrastructure($id_infra, $type, $statut, $idq, $_GET['id']);
        header('Location: /WaveNet/views/backoffice/backinfra.php');
        exit();
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
            <h1>Modifier Infrastructure</h1>
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
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="id_infra">ID Infrastructure</label>
                        <input type="text" name="id_infra" id="id_infra" value="<?= isset($e['id_infra']) ? htmlspecialchars($e['id_infra']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="type">Type</label>
                        <input type="text" name="type" id="type" value="<?= isset($e['type']) ? htmlspecialchars($e['type']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <input type="text" name="statut" id="statut" value="<?= isset($e['statut']) ? htmlspecialchars($e['statut']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="idq">ID Quartier</label>
                        <input type="text" name="idq" id="idq" value="<?= isset($e['idq']) ? htmlspecialchars($e['idq']) : '' ?>">
                    </div>

                    <div class="form-actions">
                        <a href="/WaveNet/views/backoffice/backinfra.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>