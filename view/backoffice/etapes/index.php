<?php
session_start();

require_once __DIR__ . '/../../../controller/EtapeController.php';
require_once __DIR__ . '/../../../controller/DefiController.php';

$etapeController = new EtapeController();
$defiController = new DefiController();

// Mettre à jour automatiquement les statuts des défis et des étapes
$defiController->updateDefiStatuses();
$etapeController->updateEtapeStatuses();

$etapes = $etapeController->getAllEtapes();
$defis = [];
foreach ($defiController->getAllDefis() as $defi) {
    $defis[$defi['Id_Defi']] = $defi['Titre_D'];
}

// Handle messages
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des étapes - Urbaverse</title>
    <link rel="stylesheet" href="../../../assets/css/backoffice.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            <a href="../../frontoffice/index.php" style="text-decoration: none; display: flex; align-items: center; justify-content: center; padding: 15px 0;">
                <img src="../../../assets/img/logo.jpg" alt="Logo Urbaverse" width="40" height="40" style="border-radius: 50%; margin-right: 10px; border: 2px solid var(--accent-color);">
                <h1 style="color: white; margin: 0; font-size: 22px;">Urbaverse</h1>
            </a>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../dashboard/index.php">Dashboard</a></li>
                <li><a href="../defis/index.php">Défis</a></li>
                <li><a href="../etapes/index.php">Étapes</a></li>
                <li class="home-link"><a href="../../frontoffice/index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- Header modernisé -->
        <div class="content-header">
            <div class="header-left">
                <h1>Gestion des étapes</h1>
            </div>
            <div class="header-right">
                <span class="user-badge"><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
                <a href="../../frontoffice/index.php" class="home-btn">Accueil</a>
            </div>
        </div>

        <?php if($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Barre de section modernisée -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Liste des étapes</h2>
                <a href="create.php" class="btn-add">Ajouter une étape</a>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Ordre</th>
                            <th>Points Bonus</th>
                            <th>Statut</th>
                            <th>Défi associé</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($etapes)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center;">Aucune étape trouvée</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($etapes as $etape): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($etape['Id_etape']); ?></td>
                                    <td><?php echo htmlspecialchars($etape['Titre_E']); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($etape['Description_E'], 0, 100, '...')); ?></td>
                                    <td><?php echo htmlspecialchars($etape['Ordre']); ?></td>
                                    <td><?php echo htmlspecialchars($etape['Points_Bonus']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower($etape['Statut_E']); ?>">
                                            <?php echo htmlspecialchars($etape['Statut_E']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $idDefi = $etape['Id_Defi'];
                                            echo htmlspecialchars($etapeController->getDefiNameById($idDefi));
                                        ?>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="view.php?id=<?php echo $etape['Id_etape']; ?>" class="btn-view">Voir</a>
                                        <a href="edit.php?id=<?php echo $etape['Id_etape']; ?>" class="btn-edit">Modifier</a>
                                        <a href="delete.php?id=<?php echo $etape['Id_etape']; ?>" class="btn-delete" onclick="return confirm('Voulez-vous vraiment supprimer cette étape ?')">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>