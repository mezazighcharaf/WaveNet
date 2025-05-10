<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { 
    session_start();
}

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}

// Inclusion des fichiers nécessaires
require_once '../../views/includes/config.php';
require_once '../../models/signalement.php';
require_once '../../controller/signalementctrl.php';
require_once '../../controller/interventionctrl.php';

$pageTitle = 'Mes signalements';
$activePage = 'signalement';

// Récupération des signalements
$signalementC = new SignalementC();
$liste_signalements = $signalementC->afficherSignalement();

// Récupération des interventions
$interventionC = new InterventionC();
$liste_interventions = $interventionC->afficherIntervention();

// Organiser les interventions par ID de signalement pour un accès facile
$interventions_par_signalement = [];
foreach ($liste_interventions as $intervention) {
    if (!empty($intervention['id_signalement'])) {
        if (!isset($interventions_par_signalement[$intervention['id_signalement']])) {
            $interventions_par_signalement[$intervention['id_signalement']] = [];
        }
        $interventions_par_signalement[$intervention['id_signalement']][] = $intervention;
    }
}

require_once '../includes/userHeader.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | WaveNet</title>
    <link rel="stylesheet" href="../../views/assets/css/style11.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .card-header {
            background-color: var(--accent-green);
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body {
            padding: 1.5rem;
        }
        .signalement-details {
            margin-bottom: 1rem;
        }
        .detail-row {
            display: flex;
            margin-bottom: 0.75rem;
        }
        .detail-label {
            width: 150px;
            font-weight: 500;
            color: var(--dark-green);
        }
        .detail-value {
            flex: 1;
        }
        .intervention-list {
            margin-top: 1.5rem;
            border-top: 1px solid #eee;
            padding-top: 1.5rem;
        }
        .intervention-title {
            color: var(--dark-green);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .intervention-item {
            background-color: #f9f9f9;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 0.75rem;
        }
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: capitalize;
        }
        .status-en-attente {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-en-cours {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-resolu, .status-traité {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejete {
            background-color: #f8d7da;
            color: #721c24;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .empty-icon {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        .btn-add {
            background-color: var(--accent-green);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 1rem;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn-add:hover {
            background-color: var(--dark-green);
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin: 2rem 0 1.5rem;">
            <h1><?php echo $pageTitle; ?></h1>
            <a href="addSignalement.php" class="btn-add">
                <i class="fas fa-plus"></i> Nouveau signalement
            </a>
        </div>
        
        <?php if (empty($liste_signalements)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>Aucun signalement pour le moment</h3>
                <p>Vous n'avez pas encore fait de signalement. Cliquez sur le bouton ci-dessous pour signaler un problème.</p>
                <a href="addSignalement.php" class="btn-add">
                    <i class="fas fa-plus"></i> Ajouter un signalement
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($liste_signalements as $signalement): ?>
                <div class="card">
                    <div class="card-header">
                        <span><?php echo htmlspecialchars($signalement['titre']); ?></span>
                        <?php 
                            $statusClass = '';
                            $status = strtolower($signalement['statut']);
                            if ($status === 'non traité' || $status === 'en attente') {
                                $statusClass = 'status-en-attente';
                            } elseif ($status === 'en cours') {
                                $statusClass = 'status-en-cours';
                            } elseif ($status === 'traité' || $status === 'résolu') {
                                $statusClass = 'status-resolu';
                            } elseif ($status === 'rejeté') {
                                $statusClass = 'status-rejete';
                            }
                        ?>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($signalement['statut']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="signalement-details">
                            <div class="detail-row">
                                <div class="detail-label">Description:</div>
                                <div class="detail-value"><?php echo nl2br(htmlspecialchars($signalement['description'])); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Emplacement:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($signalement['emplacement']); ?></div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Date du signalement:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($signalement['date_signalement']); ?></div>
                            </div>
                        </div>
                        
                        <?php if (isset($interventions_par_signalement[$signalement['id_signalement']])): ?>
                            <div class="intervention-list">
                                <h3 class="intervention-title">Interventions associées</h3>
                                <?php foreach ($interventions_par_signalement[$signalement['id_signalement']] as $intervention): ?>
                                    <div class="intervention-item">
                                        <div class="detail-row">
                                            <div class="detail-label">Date:</div>
                                            <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($intervention['date_intervention'])); ?></div>
                                        </div>
                                        <div class="detail-row">
                                            <div class="detail-label">Statut:</div>
                                            <div class="detail-value">
                                                <?php 
                                                    $intStatusClass = '';
                                                    $intStatus = strtolower($intervention['statut']);
                                                    if ($intStatus === 'non traité') {
                                                        $intStatusClass = 'status-en-attente';
                                                    } elseif ($intStatus === 'en cours') {
                                                        $intStatusClass = 'status-en-cours';
                                                    } elseif ($intStatus === 'traité') {
                                                        $intStatusClass = 'status-traité';
                                                    }
                                                ?>
                                                <span class="status-badge <?php echo $intStatusClass; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($intervention['statut'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="intervention-list">
                                <p>Aucune intervention n'a encore été programmée pour ce signalement.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html> 