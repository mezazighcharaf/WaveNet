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
    <title>Gestion des Signalements</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 240px;
            background-color: #2e4f3e;
            color: white;
            padding-top: 20px;
        }
        
        .sidebar .logo {
            padding: 0 20px 30px;
        }
        
        .sidebar .logo h1 {
            font-size: 24px;
            color: white;
        }
        
        .sidebar-nav ul {
            list-style: none;
        }
        
        .sidebar-nav a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px 20px;
            transition: background-color 0.2s;
        }
        
        .sidebar-nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-nav a.active {
            background-color: #f1c40f;
            color: #2e4f3e;
            font-weight: 600;
        }
        
        .main-content {
            flex: 1;
            background-color: #f9f9f9;
            padding: 20px;
        }
        
        .header-admin {
            text-align: right;
            margin-bottom: 10px;
        }
        
        h1.content-title {
            color: #2e4f3e;
            font-size: 28px;
            margin-bottom: 30px;
        }
        
        .content-section {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .content-section h2 {
            color: #2e4f3e;
            font-size: 20px;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .btn-add {
            float: right;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 15px;
            text-decoration: none;
            font-weight: 500;
        }
        
        .btn-add:hover {
            background-color: #3e8e41;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        table th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .status-non-traite {
            color: #e74c3c;
            font-weight: 500;
        }
        
        .btn-delete {
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
        }
        
        .btn-delete:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">
            <h1>Urbaverse</h1>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="affichesignalement.php" class="active">Signalements</a></li>
                <li><a href="afficherintervention.php">Interventions</a></li>
                <li><a href="utilisateurs.php">Utilisateurs</a></li>
                <li><a href="parametres.php">Paramètres</a></li>
                <li><a href="../front office/index.php">Retour au site</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-admin">
            Admin
        </div>
        
        <h1 class="content-title">Gestion des Signalements</h1>
        
        <div class="content-section">
            <h2>Liste des Signalements</h2>
            <a href="addsignalement.php" class="btn-add">+ Ajouter</a>
            
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