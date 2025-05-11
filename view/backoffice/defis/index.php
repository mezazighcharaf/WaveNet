<?php
session_start();

// At the top of the file, add:
require_once __DIR__ . '/../../../controller/DefiController.php';
require_once __DIR__ . '/../../../controller/EtapeController.php';

$defiController = new DefiController();
$etapeController = new EtapeController();

// Mise à jour normale avec la date actuelle
$defiController->updateDefiStatuses();
$etapeController->updateEtapeStatuses();

// Get all defis
$defis = $defiController->getAllDefis();

// Handle deletion if requested
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    if($defiController->deleteDefi($id)) {
        header('Location: index.php?message=Défi supprimé avec succès');
        exit();
    } else {
        $error = "Erreur lors de la suppression du défi";
    }
}

// Handle reset auto increment if requested
if(isset($_GET['action']) && $_GET['action'] == 'reset_auto_increment') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $query = "ALTER TABLE defi AUTO_INCREMENT = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        header('Location: index.php?message=Compteur d\'ID réinitialisé avec succès');
        exit();
    } catch(PDOException $e) {
        $error = "Erreur lors de la réinitialisation du compteur d'ID";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des défis - Urbaverse</title>
    <link rel="stylesheet" href="../../../assets/css/backoffice.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0f7f0;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #2c5e3e;
            font-size: 28px;
            margin: 0;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
        }
        
        .admin-info a {
            color: #6a1b9a;
            text-decoration: none;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 18px;
            color: #333;
            margin: 0;
        }
        
        .btn-add {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: #2c5e3e;
            color: white;
        }
        
        th, td {
            padding: 8px 10px;
            text-align: left;
            font-size: 14px;
        }
        
        .btn-view, .btn-edit, .btn-delete {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 3px;
            font-size: 13px;
        }
        
        .btn-view {
            background-color: #e0f2f1;
            color: #0288d1;
        }
        
        .btn-edit {
            background-color: #e8f5e9;
            color: #4caf50;
        }
        
        .btn-delete {
            background-color: #ffebee;
            color: #f44336;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            background-color: #e3f2fd;
            color: #1976D2;
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
                <li><a href="index.php">Défis</a></li>
                <li><a href="../etapes/index.php">Étapes</a></li>
                <li class="home-link"><a href="../../frontoffice/index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="header" style="margin-bottom: 2.5rem;">
            <h1>Gestion des défis</h1>
            <div class="admin-info">
                Admin <a href="../../frontoffice/index.php">Accueil</a>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Liste des défis écologiques</h2>
                <a href="create.php" class="btn-add">Ajouter un nouveau défi</a>
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
                        <?php if($defis->rowCount() > 0): ?>
                            <?php while($row = $defis->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : 'N/A'; ?></td>
                                    <td><?php echo isset($row['Titre_D']) ? $row['Titre_D'] : 'N/A'; ?></td>
                                    <td><?php echo isset($row['Description_D']) ? (substr($row['Description_D'], 0, 100) . (strlen($row['Description_D']) > 100 ? '...' : '')) : 'N/A'; ?></td>
                                    <td><?php echo isset($row['Points_verts']) ? $row['Points_verts'] : 'N/A'; ?></td>
                                    <td>
                                        <?php if(isset($row['Statut_D'])): ?>
                                            <span class="status-badge">
                                                <?php echo $row['Statut_D']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $debut = isset($row['Date_Debut']) ? date('d/m/Y', strtotime($row['Date_Debut'])) : 'N/A';
                                            $fin = isset($row['Date_Fin']) ? date('d/m/Y', strtotime($row['Date_Fin'])) : 'N/A';
                                            echo $debut . ' - ' . $fin; 
                                        ?>
                                    </td>
                                    <td><?php echo isset($row['Difficulte']) ? $row['Difficulte'] : 'N/A'; ?></td>
                                    <td class="actions-cell">
                                        <a href="view.php?id=<?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : ''; ?>" class="btn-view">Voir</a>
                                        <a href="edit.php?id=<?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : ''; ?>" class="btn-edit">Modifier</a>
                                        <a href="index.php?action=delete&id=<?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : ''; ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce défi?')">Supprimer</a>
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
    </main>
</body>
</html>