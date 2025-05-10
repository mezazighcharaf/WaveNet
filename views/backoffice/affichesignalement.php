<?php
include_once __DIR__ . '/../../controller/signalementctrl.php';
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../model/signalement.php';

$signalementC = new Signalementc();
$liste = $signalementC->afficherSignalement();

if (isset($_POST['id'])) {
    $signalementC->deleteSignalement($_POST['id']);
    // Rediriger pour éviter la resoumission du formulaire
    header('Location: affichesignalement.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Signalements - Urbaverse</title>
    <link rel="stylesheet" href="../../../views/assets/css/backoffice11.css" />
    <link rel="stylesheet" href="../../../views/assets/css/admin-dashboard.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
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
                <li><a href="/WaveNet/views/backoffice/backinfra.php"><i class="fas fa-building"></i> Infrastructures</a></li>
                <li><a href="/WaveNet/views/backoffice/gsignalement.php" class="active"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
                <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-admin">
            Admin
        </div>
        
        <h1 class="content-title">Gestion des Signalements</h1>
        
        <a href="export_signalements_pdf.php" style="background:#2e4f3e;color:white;border-radius:4px;padding:8px 15px;text-decoration:none;font-weight:500;margin:20px 0 10px 0;display:inline-block;">Exporter en PDF</a>
        <div class="content-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="margin: 0;">Liste des Signalements</h2>
                <div>
                    <a href="addsignalement.php" class="btn-add" style="float:none;display:inline-block;">+ Ajouter</a>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Description</th>
                        <th>Emplacement</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($liste)): ?>
                        <?php foreach($liste as $signalement): ?>
                            <tr>
                                <td><?= htmlspecialchars($signalement['id_signalement']) ?></td>
                                <td><?= htmlspecialchars($signalement['titre']) ?></td>
                                <td><?= htmlspecialchars($signalement['description']) ?></td>
                                <td><?= htmlspecialchars($signalement['emplacement']) ?></td>
                                <td><?= htmlspecialchars($signalement['date_signalement']) ?></td>
                                <td class="status-non-traite"><?= htmlspecialchars($signalement['statut']) ?></td>
                                <td>
                                    <form action="affichesignalement.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($signalement['id_signalement']) ?>" />
                                        <button type="submit" class="btn-delete">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">Aucun signalement trouvé</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>