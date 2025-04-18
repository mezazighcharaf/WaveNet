<?php
session_start();

// Check if user is logged in as admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    // Just mock admin role for demonstration since there's no login
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Admin';
}

require_once __DIR__ . '/../../../controller/DefiController.php';

// Initialize controller
$defiController = new DefiController();

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
        /* Styles de tableau améliorés */
        .content-table {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-top: 2rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: #f8f9fa;
        }
        
        th {
            padding: 14px 18px;
            text-align: left;
            font-weight: 600;
            color: #34495e;
            font-size: 0.9rem;
            border-bottom: 1px solid #ecf0f1;
        }
        
        td {
            padding: 16px 18px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: middle;
            font-size: 0.95rem;
        }
        
        tbody tr:hover {
            background-color: #f9fafb;
        }
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-small {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-view {
            background-color: #e1ebf5;
            color: #3498db;
        }
        
        .btn-view:hover {
            background-color: #d0e2f2;
        }
        
        .btn-edit {
            background-color: #e1f5e9;
            color: #27ae60;
        }
        
        .btn-edit:hover {
            background-color: #d0f0db;
        }
        
        .btn-delete {
            background-color: #fdeaed;
            color: #e74c3c;
        }
        
        .btn-delete:hover {
            background-color: #fcdee2;
        }
        
        /* Statut badges */
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
        }
        
        .status.active {
            background-color: #e1f5e9;
            color: #27ae60;
        }
        
        .status.inactive {
            background-color: #fdeaed;
            color: #e74c3c;
        }
        
        .status.upcoming {
            background-color: #e1ebf5;
            color: #3498db;
        }
        
        /* Page title and action button */
        .header-with-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .defi-table-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            padding: 2rem;
            margin: 24px 0;
        }
        
        .truncate {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .truncate-content {
            max-width: 250px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .table-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .btn-add {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-add:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-reset {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            background-color: #f39c12;
            color: white;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            margin-right: 12px;
        }
        
        .btn-reset:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
        }
        
        .table-actions {
            display: flex;
            align-items: center;
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .alert-success {
            background-color: #e1f5e9;
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }
        
        .alert-danger {
            background-color: #fdeaed;
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
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
                <li><a href="index.php">Défis</a></li>
                <li class="home-link"><a href="../../frontoffice/index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="content-header">
            <h1>Gestion des défis</h1>
            <div class="user-info">
                <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
                <a href="../../frontoffice/index.php" class="home-button">Accueil</a>
            </div>
        </header>

        <?php if(isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <?php echo $_GET['message']; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="defi-table-container">
            <div class="table-header">
                <h2 class="table-title">Liste des défis écologiques</h2>
                <div class="table-actions">
                    <?php if($defis->rowCount() == 0): ?>
                        <a href="index.php?action=reset_auto_increment" class="btn-reset" onclick="return confirm('Réinitialiser le compteur d\'ID? Le prochain défi aura l\'ID 1.')">Réinitialiser compteur ID</a>
                    <?php endif; ?>
                    <a href="create.php" class="btn-add">Ajouter un nouveau défi</a>
                </div>
            </div>

            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Points</th>
                            <th>Statut</th>
                            <th>Période</th>
                            <th>Difficulté</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($defis->rowCount() > 0): ?>
                            <?php while($row = $defis->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : 'N/A'; ?></td>
                                    <td class="truncate"><?php echo isset($row['Titre_D']) ? $row['Titre_D'] : 'N/A'; ?></td>
                                    <td class="truncate-content"><?php echo isset($row['Description_D']) ? (substr($row['Description_D'], 0, 100) . (strlen($row['Description_D']) > 100 ? '...' : '')) : 'N/A'; ?></td>
                                    <td><strong><?php echo isset($row['Points_verts']) ? $row['Points_verts'] : 'N/A'; ?></strong></td>
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
                                    <td class="actions">
                                        <a href="view.php?id=<?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : ''; ?>" class="btn-small btn-view">Voir</a>
                                        <a href="edit.php?id=<?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : ''; ?>" class="btn-small btn-edit">Modifier</a>
                                        <a href="index.php?action=delete&id=<?php echo isset($row['Id_Defi']) ? $row['Id_Defi'] : ''; ?>" class="btn-small btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce défi?')">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Aucun défi trouvé</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html> 