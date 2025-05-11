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

// Check if ID is provided
if(!isset($_GET['id'])) {
    header('Location: index.php?message=ID du défi non spécifié');
    exit();
}

$id = $_GET['id'];
$defi = $defiController->getDefi($id);

if(!$defi) {
    header('Location: index.php?message=Défi non trouvé');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du défi - Urbaverse</title>
    <link rel="stylesheet" href="../../../assets/css/backoffice.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles pour la page de détail du défi */
        .defi-details {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            padding: 40px;
            margin: 30px 0;
        }
        
        .defi-header {
            display: flex;
            flex-direction: column;
            margin-bottom: 36px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .defi-title-container {
            margin-bottom: 24px;
        }
        
        .defi-title {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 12px 0;
            line-height: 1.3;
        }
        
        .defi-id {
            color: #7f8c8d;
            font-size: 1rem;
            margin-top: 4px;
            display: inline-block;
            margin-right: 16px;
        }
        
        .defi-status {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 8px;
        }
        
        .status-active {
            background-color: #e1f5e9;
            color: #27ae60;
        }
        
        .status-inactive {
            background-color: #f5e5e1;
            color: #e74c3c;
        }
        
        .status-upcoming {
            background-color: #e1ebf5;
            color: #3498db;
        }
        
        .defi-actions {
            display: flex;
            gap: 16px;
            margin-top: 24px;
        }
        
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-edit {
            background-color: #3498db;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-back {
            background-color: #f2f3f4;
            color: #34495e;
        }
        
        .btn-back:hover {
            background-color: #e6e9ea;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .defi-section {
            background: #f9fafb;
            border-radius: 10px;
            padding: 32px;
            margin-bottom: 36px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 24px;
            color: #34495e;
            border-bottom: 2px solid rgba(52, 152, 219, 0.2);
            padding-bottom: 12px;
        }
        
        .defi-property {
            margin-bottom: 24px;
        }
        
        .property-label {
            font-weight: 500;
            color: #7f8c8d;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .property-value {
            font-size: 1.05rem;
            color: #2c3e50;
            line-height: 1.6;
        }
        
        .defi-meta {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
        }
        
        .defi-dates {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .badge-facile {
            background-color: #e8f6e9;
            color: #27ae60;
        }
        
        .badge-intermediaire {
            background-color: #fef5e8;
            color: #f39c12;
        }
        
        .badge-difficile {
            background-color: #fdeaed;
            color: #e74c3c;
        }
        
        @media (max-width: 768px) {
            .defi-details {
                padding: 24px;
                margin: 15px 0;
            }
            
            .defi-header {
                margin-bottom: 24px;
            }
            
            .defi-actions {
                flex-direction: column;
                gap: 12px;
            }
            
            .defi-meta, .defi-dates {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .defi-section {
                padding: 20px;
                margin-bottom: 24px;
            }
            
            .defi-title {
                font-size: 1.5rem;
            }
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
            <h1>Détails du défi</h1>
            <div class="user-info">
                <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin'; ?></span>
                <a href="../../frontoffice/index.php" class="home-button">Accueil</a>
            </div>
        </header>

        <div class="defi-details">
            <div class="defi-header">
                <div class="defi-title-container">
                    <h2 class="defi-title"><?php echo htmlspecialchars($defi->Titre_D); ?></h2>
                    <div>
                        <span class="defi-id">Identifiant: #<?php echo $defi->Id_Defi; ?></span>
                        <span class="defi-status <?php 
                            if($defi->Statut_D == 'Actif') echo 'status-active';
                            else if($defi->Statut_D == 'Inactif') echo 'status-inactive';
                            else echo 'status-upcoming';
                        ?>">
                            <?php echo htmlspecialchars($defi->Statut_D); ?>
                        </span>
                    </div>
                </div>
                
                <div class="defi-actions">
                    <a href="index.php" class="btn-action btn-back">
                        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Retour à la liste
                    </a>
                    <a href="edit.php?id=<?php echo $defi->Id_Defi; ?>" class="btn-action btn-edit">
                        <i class="fas fa-edit" style="margin-right: 8px;"></i> Modifier
                    </a>
                    <a href="delete.php?id=<?php echo $defi->Id_Defi; ?>" class="btn-action btn-delete" 
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce défi ?');">
                        <i class="fas fa-trash" style="margin-right: 8px;"></i> Supprimer
                    </a>
                </div>
            </div>
            
            <div class="defi-section">
                <h3 class="section-title">Description</h3>
                <div class="defi-property">
                    <div class="property-value"><?php echo nl2br(htmlspecialchars($defi->Description_D)); ?></div>
                </div>
            </div>
            
            <div class="defi-section">
                <h3 class="section-title">Objectif</h3>
                <div class="defi-property">
                    <div class="property-value"><?php echo nl2br(htmlspecialchars($defi->Objectif)); ?></div>
                </div>
            </div>
            
            <div class="defi-section">
                <h3 class="section-title">Paramètres du défi</h3>
                
                <div class="defi-meta">
                    <div class="defi-property">
                        <div class="property-label">Points verts</div>
                        <div class="property-value"><strong><?php echo htmlspecialchars($defi->Points_verts); ?></strong> points</div>
                    </div>
                    
                    <div class="defi-property">
                        <div class="property-label">Difficulté</div>
                        <div class="property-value">
                            <span class="badge <?php 
                                if($defi->Difficulte == 'Facile') echo 'badge-facile';
                                else if($defi->Difficulte == 'Intermédiaire') echo 'badge-intermediaire';
                                else echo 'badge-difficile';
                            ?>">
                                <?php echo htmlspecialchars($defi->Difficulte); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="defi-property">
                        <div class="property-label">Quartier</div>
                        <div class="property-value">ID: <?php echo htmlspecialchars($defi->Id_Quartier); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="defi-section">
                <h3 class="section-title">Période</h3>
                
                <div class="defi-dates">
                    <div class="defi-property">
                        <div class="property-label">Date de début</div>
                        <div class="property-value"><?php echo htmlspecialchars(date('d/m/Y', strtotime($defi->Date_Debut))); ?></div>
                    </div>
                    
                    <div class="defi-property">
                        <div class="property-label">Date de fin</div>
                        <div class="property-value"><?php echo htmlspecialchars(date('d/m/Y', strtotime($defi->Date_Fin))); ?></div>
                    </div>
                    
                    <div class="defi-property">
                        <div class="property-label">Durée</div>
                        <div class="property-value">
                            <?php 
                                $debut = new DateTime($defi->Date_Debut);
                                $fin = new DateTime($defi->Date_Fin);
                                $diff = $debut->diff($fin);
                                echo $diff->days . ' jours';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Ajout de FontAwesome pour les icônes -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html> 