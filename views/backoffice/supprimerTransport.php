<?php
session_start();
// Vérifier l'authentification
if (!isset($_SESSION['user_id']) || $_SESSION['user_niveau'] !== 'admin') {
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

require_once '../../views/includes/config.php';
$db = connectDB();
$pageTitle = "Supprimer un Type de Transport";
$alertMessage = '';

// Récupérer l'ID du type de transport à supprimer
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Si aucun ID n'est fourni, rediriger vers la page de gestion
if ($id === 0) {
    header('Location: /WaveNet/views/backoffice/gererTransport.php');
    exit;
}

// Récupérer les données du type de transport
$transportType = null;
try {
    $stmt = $db->prepare("SELECT * FROM TRANSPORT_TYPE WHERE id = ?");
    $stmt->execute([$id]);
    $transportType = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transportType) {
        // Si le type de transport n'existe pas, rediriger
        $_SESSION['alertMessage'] = "<div class='alert alert-danger'>Type de transport non trouvé.</div>";
        header('Location: /WaveNet/views/backoffice/gererTransport.php');
        exit;
    }
} catch (PDOException $e) {
    $alertMessage = "<div class='alert alert-danger'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        // Vérifier si le type est utilisé
        $stmt = $db->prepare("SELECT COUNT(*) FROM TRANSPORT WHERE type_transport = ?");
        $stmt->execute([$transportType['nom']]);
        $isUsed = $stmt->fetchColumn() > 0;
        
        if ($isUsed) {
            $alertMessage = "<div class='alert alert-danger'>Ce type de transport est utilisé et ne peut pas être supprimé.</div>";
        } else {
            // Supprimer le type
            $stmt = $db->prepare("DELETE FROM TRANSPORT_TYPE WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['alertMessage'] = "<div class='alert alert-success'>Type de transport supprimé avec succès.</div>";
                header('Location: /WaveNet/views/backoffice/gererTransport.php');
                exit;
            } else {
                $alertMessage = "<div class='alert alert-danger'>Erreur lors de la suppression du type de transport.</div>";
            }
        }
    } catch (PDOException $e) {
        $alertMessage = "<div class='alert alert-danger'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $pageTitle ?> - WaveNet</title>
  <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css" />
  <link rel="stylesheet" href="/WaveNet/views/assets/css/admin-dashboard.css" />
  <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .alert {
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .alert-warning {
        background-color: rgba(255, 193, 7, 0.1);
        border: none;
        padding: 0;
    }
    
    .alert-warning h4 {
        color: #f57c00;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    
    .alert-danger {
        background-color: rgba(244, 67, 54, 0.1);
        border-color: #f44336;
        color: #d32f2f;
        padding: 1rem 1.5rem;
        border-left: 4px solid;
    }
    
    .table {
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .table th {
        background-color: #f8f9fc;
        font-weight: 600;
        color: #2e4f3e;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .text-danger {
        background-color: rgba(244, 67, 54, 0.1);
        color: #d32f2f;
        padding: 1rem;
        border-radius: 8px;
        margin: 1rem 0;
        border-left: 4px solid #f44336;
        font-weight: 500;
    }
    
    .btn {
        padding: 0.6rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-danger {
        background-color: #f44336;
        border-color: #f44336;
    }
    
    .btn-danger:hover {
        background-color: #d32f2f;
        border-color: #d32f2f;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .btn-secondary {
        background-color: #f2f2f2;
        border-color: #e0e0e0;
        color: #555;
    }
    
    .btn-secondary:hover {
        background-color: #e0e0e0;
        color: #333;
    }
  </style>
</head>
<body>
  <!-- SIDEBAR -->
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
        <li><a href="/WaveNet/views/backoffice/gsignalement.php"><i class="fas fa-exclamation-triangle"></i> Signalements</a></li>
        <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
        <li><a href="/WaveNet/views/backoffice/recompenseback.php"><i class="fas fa-gift"></i> Récompenses</a></li>
        <li><a href="/WaveNet/views/backoffice/eco_actionsB.php"><i class="fas fa-leaf"></i> Eco Actions</a></li>
        <li><a href="/WaveNet/views/backoffice/gererTransport.php" class="active"><i class="fas fa-car"></i> Types de Transport</a></li>
        <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
      </ul>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <header class="content-header">
      <h1><?= $pageTitle ?></h1>
      <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?> (Admin)</span>
        <a href="/WaveNet/controller/UserController.php?action=logout" class="home-button">Déconnexion</a>
      </div>
    </header>
    
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Supprimer le Type de Transport</h6>
                <a href="/WaveNet/views/backoffice/gererTransport.php" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
            <div class="card-body">
                <?= $alertMessage ?>
                
                <div class="alert alert-warning">
                    <h4><i class="fas fa-exclamation-triangle text-warning mr-2"></i> Confirmation de suppression</h4>
                    <p>Êtes-vous sûr de vouloir supprimer le type de transport suivant ?</p>
                    
                    <div class="mb-4">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">ID</th>
                                <td><?= isset($transportType['id']) ? $transportType['id'] : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Nom</th>
                                <td><?= htmlspecialchars($transportType['nom']) ?></td>
                            </tr>
                            <tr>
                                <th>Éco-index</th>
                                <td><?= number_format($transportType['eco_index'], 1) ?>/10</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td><?= htmlspecialchars($transportType['description'] ?? '') ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <p class="text-danger font-weight-bold">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        ATTENTION : Cette action est irréversible. Tous les enregistrements associés peuvent être affectés.
                    </p>
                    
                    <form method="post" action="/WaveNet/views/backoffice/supprimerTransport.php?id=<?= $id ?>">
                        <div class="form-group d-flex justify-content-between mt-4">
                            <a href="/WaveNet/views/backoffice/gererTransport.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Annuler
                            </a>
                            <button type="submit" name="confirm_delete" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Confirmer la suppression
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 