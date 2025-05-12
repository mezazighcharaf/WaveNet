<?php
session_start();

require_once __DIR__ . '/../../../controller/DefiController.php';
require_once __DIR__ . '/../../../controller/EtapeController.php';

$defiController = new DefiController();
$etapeController = new EtapeController();

$defis = $defiController->getAllDefis();
try {
    $etapes = $etapeController->getAllEtapes();
} catch (Exception $e) {
    $etapes = [];
}

// Pour associer les défis aux étapes
$defisAssoc = [];
foreach ($defiController->getAllDefis() as $defi) {
    $defisAssoc[$defi['Id_Defi']] = $defi['Titre_D'];
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
    <title>Dashboard - Urbaverse</title>
    <link rel="stylesheet" href="../../../assets/css/backoffice.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles supplémentaires pour corriger les problèmes d'affichage */
        .data-table {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th, .data-table td {
            padding: 12px;
            text-align: left;
            vertical-align: middle;
            border-bottom: 1px solid #e0e0e0;
            word-wrap: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Ajuster la largeur des colonnes */
        .data-table th:nth-child(1), .data-table td:nth-child(1) { width: 5%; } /* ID */
        .data-table th:nth-child(2), .data-table td:nth-child(2) { width: 15%; } /* Titre */
        .data-table th:nth-child(3), .data-table td:nth-child(3) { 
            width: 20%; 
            max-width: 200px;
        } /* Description */
        .data-table th:nth-child(4), .data-table td:nth-child(4) { width: 8%; } /* Points */
        .data-table th:nth-child(5), .data-table td:nth-child(5) { width: 10%; } /* Statut */
        .data-table th:nth-child(6), .data-table td:nth-child(6) { width: 15%; } /* Période */
        .data-table th:nth-child(7), .data-table td:nth-child(7) { width: 10%; } /* Difficulté */
        .data-table th:nth-child(8), .data-table td:nth-child(8) { width: 17%; } /* Actions */
        
        /* Limiter la hauteur des lignes pour éviter des cellules trop grandes */
        .data-table tr {
            max-height: 80px;
        }
        
        /* Style pour le texte tronqué */
        .truncated-text {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 60px;
        }
        
        /* Amélioration des boutons d'action */
        .actions-cell {
            white-space: nowrap;
            text-align: center;
        }
        
        .btn-view, .btn-edit, .btn-delete {
            display: inline-block;
            padding: 6px 10px;
            margin: 2px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
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
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="../defis/index.php">Défis</a></li>
                <li><a href="../etapes/index.php">Étapes</a></li>
                <li class="home-link"><a href="../../frontoffice/index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="content-header">
            <h1>Dashboard</h1>
            <div class="user-info">
                <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
                <a href="../../frontoffice/index.php" class="home-button">Accueil</a>
            </div>
        </header>

        <?php if($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Section des défis -->
        <div class="defi-form" style="margin-bottom: 2.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="form-title">Gestion des défis</h2>
                <a href="../defis/create.php" class="btn-submit" style="margin-bottom: 12px; background-color: #2196F3; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Ajouter un nouveau défi</a>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Points</th>
                            <th>Statut</th>
                            <th>Période</th>
                            <th>Difficulté</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(is_object($defis) && $defis->rowCount() > 0): ?>
                            <?php while($row = $defis->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : 'N/A'; ?></td>
                                    <td><?php echo isset($row['Titre_D']) ? $row['Titre_D'] : 'N/A'; ?></td>
                                    <td><?php echo isset($row['Description_D']) ? 
                                        '<div class="truncated-text">' . htmlspecialchars(substr($row['Description_D'], 0, 100) . (strlen($row['Description_D']) > 100 ? '...' : '')) . '</div>' 
                                        : 'N/A'; ?></td>
                                    <td><?php echo isset($row['Points_verts']) ? $row['Points_verts'] : 'N/A'; ?></td>
                                    <td>
                                        <?php if(isset($row['Statut_D'])): ?>
                                            <span class="status <?php 
                                                if($row['Statut_D'] == 'Actif') echo 'active';
                                                else if($row['Statut_D'] == 'Inactif') echo 'inactive';
                                                else echo 'upcoming';
                                            ?>">
                                                <?php echo $row['Statut_D']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status inactive">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $debut = isset($row['Date_Debut']) ? date('d/m/Y', strtotime($row['Date_Debut'])) : 'N/A';
                                            $fin = isset($row['Date_Fin']) ? date('d/m/Y', strtotime($row['Date_Fin'])) : 'N/A';
                                            echo $debut . ' - ' . $fin; 
                                        ?>
                                    </td>
                                    <td>
                                        <?php if(isset($row['Difficulte'])): ?>
                                            <span class="badge <?php 
                                                if($row['Difficulte'] == 'Facile') echo 'badge-facile';
                                                else if($row['Difficulte'] == 'Intermédiaire') echo 'badge-intermediaire';
                                                else echo 'badge-difficile';
                                            ?>">
                                                <?php echo $row['Difficulte']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span>N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="../defis/view.php?id=<?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : ''; ?>" class="btn-view">Voir</a>
                                        <a href="../defis/edit.php?id=<?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : ''; ?>" class="btn-edit">Modifier</a>
                                        <a href="../defis/index.php?action=delete&id=<?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : ''; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce défi?')">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center;">Aucun défi trouvé</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section des étapes -->
        <div class="defi-form">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="form-title">Gestion des étapes</h2>
                <a href="../etapes/create.php" class="btn-submit" style="margin-bottom: 12px; background-color: #2196F3; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Ajouter une étape</a>
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
                                <td colspan="8" style="text-align:center;">Aucune étape trouvée.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($etapes as $etape): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($etape['Id_etape']); ?></td>
                                    <td><?php echo htmlspecialchars($etape['Titre_E']); ?></td>
                                    <td><div class="truncated-text"><?php echo htmlspecialchars(mb_strimwidth($etape['Description_E'], 0, 50, '...')); ?></div></td>
                                    <td><?php echo htmlspecialchars($etape['Ordre']); ?></td>
                                    <td><?php echo htmlspecialchars($etape['Points_Bonus']); ?></td>
                                    <td><?php echo htmlspecialchars($etape['Statut_E']); ?></td>
                                    <td>
                                        <?php
                                            $idDefi = $etape['Id_Defi'];
                                            echo isset($defisAssoc[$idDefi]) ? htmlspecialchars($defisAssoc[$idDefi]) : 'Défi inconnu';
                                        ?>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="../etapes/view.php?id=<?php echo $etape['Id_etape']; ?>" class="btn-view">Voir</a>
                                        <a href="../etapes/edit.php?id=<?php echo $etape['Id_etape']; ?>" class="btn-edit">Modifier</a>
                                        <a href="../etapes/delete.php?id=<?php echo $etape['Id_etape']; ?>" class="btn-delete" onclick="return confirm('Voulez-vous vraiment supprimer cette étape ?')">Supprimer</a>
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