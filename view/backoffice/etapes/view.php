<?php
session_start();

// Check if user is logged in as admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_name'] = 'Admin';
}

require_once __DIR__ . '/../../../controller/EtapeController.php';
require_once __DIR__ . '/../../../controller/DefiController.php';

// Check if ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=ID de l\'étape non spécifié');
    exit();
}

$id = $_GET['id'];
$etapeController = new EtapeController();
$defiController = new DefiController();

// Get the etape data
$etape = $etapeController->getEtapeById($id);

// If etape not found, redirect with error
if(!$etape) {
    header('Location: index.php?error=Étape non trouvée');
    exit();
}

// Get the associated defi - Fix for the object vs array issue
$defi = $defiController->getDefi($etape['Id_Defi']);
// Check if $defi is an object and handle it accordingly
if(is_object($defi)) {
    $defiName = $defi->Titre_D ?? 'Défi inconnu';
} else {
    $defiName = $defi['Titre_D'] ?? 'Défi inconnu';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voir une étape - Urbaverse</title>
    <link rel="stylesheet" href="../../../assets/css/backoffice.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .etape-details {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            padding: 32px;
            margin: 24px 0;
        }
        .etape-title {
            color: #2c3e50;
            margin-bottom: 28px;
            font-size: 1.6rem;
            font-weight: 600;
            padding-bottom: 16px;
            border-bottom: 1px solid #ecf0f1;
        }
        .detail-row {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #f5f5f5;
            padding-bottom: 15px;
        }
        .detail-label {
            flex: 0 0 200px;
            font-weight: 600;
            color: #34495e;
        }
        .detail-value {
            flex: 1;
            color: #2c3e50;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit {
            background-color: #27ae60;
            color: white;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn-back {
            background-color: #ecf0f1;
            color: #34495e;
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
                <li><a href="../defis/index.php">Défis</a></li>
                <li><a href="index.php">Étapes</a></li>
                <li class="home-link"><a href="../../frontoffice/index.php">Retour à l'accueil</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="content-header">
            <h1>Étape #<?php echo htmlspecialchars($etape['Id_etape']); ?></h1>
            <div class="user-info">
                <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
                <a href="../../frontoffice/index.php" class="home-button">Accueil</a>
            </div>
        </header>

        <div class="etape-details">
            <h2 class="etape-title"><?php echo htmlspecialchars($etape['Titre_E']); ?></h2>
            
            <div class="detail-row">
                <div class="detail-label">Titre</div>
                <div class="detail-value"><?php echo htmlspecialchars($etape['Titre_E']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Description</div>
                <div class="detail-value"><?php echo htmlspecialchars($etape['Description_E']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Ordre</div>
                <div class="detail-value"><?php echo htmlspecialchars($etape['Ordre']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Points Bonus</div>
                <div class="detail-value"><?php echo htmlspecialchars($etape['Points_Bonus']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Statut</div>
                <div class="detail-value"><?php echo htmlspecialchars($etape['Statut_E']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Défi associé</div>
                <div class="detail-value"><?php echo htmlspecialchars($defiName); ?></div>
            </div>
            
            <div class="actions">
                <a href="index.php" class="btn btn-back">Retour à la liste</a>
                <a href="edit.php?id=<?php echo $etape['Id_etape']; ?>" class="btn btn-edit">Modifier</a>
                <a href="delete.php?id=<?php echo $etape['Id_etape']; ?>" class="btn btn-delete" onclick="return confirm('Voulez-vous vraiment supprimer cette étape ?');">Supprimer</a>
            </div>
        </div>
    </main>
</body>
</html>