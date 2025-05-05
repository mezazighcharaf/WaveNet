<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === 'demo_user') {
    // Rediriger vers la page de connexion
    header('Location: login.php');
    exit;
}

// Inclure le contrôleur d'authentification pour accéder aux informations de l'utilisateur
require_once __DIR__ . '/../../controller/AuthController.php';
$authController = new AuthController();

// Inclure le contrôleur des défis pour afficher les défis de l'utilisateur
require_once __DIR__ . '/../../controller/FrontofficeDefiController.php';
$defiController = new FrontofficeDefiController();

// Récupérer les participations de l'utilisateur
$participations = $defiController->getUserParticipations($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Urbaverse</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 1000px;
            margin: 100px auto 50px;
        }
        
        .profile-header {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background-color: var(--light-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: var(--dark-green);
        }
        
        .profile-info {
            flex: 1;
        }
        
        .profile-name {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        
        .profile-email {
            color: #666;
            margin-bottom: 15px;
        }
        
        .profile-stats {
            display: flex;
            gap: 30px;
        }
        
        .stat-item {
            background-color: #f9fbf9;
            padding: 15px 20px;
            border-radius: 10px;
            text-align: center;
            min-width: 120px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-green);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .profile-section {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .defi-card {
            display: flex;
            background-color: #f9fbf9;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .defi-card:hover {
            transform: translateY(-5px);
        }
        
        .defi-image {
            width: 120px;
            background-color: var(--light-green);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
        }
        
        .defi-content {
            flex: 1;
            padding: 20px;
        }
        
        .defi-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .defi-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .defi-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-points {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--accent-green);
        }
        
        .badge-date {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }
        
        .badge-status {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .defi-description {
            color: #666;
            margin-bottom: 15px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .defi-actions {
            text-align: right;
        }
        
        .btn-view {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--accent-green);
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        
        .btn-view:hover {
            background-color: #3c8c40;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 0;
            color: #666;
        }
        
        .empty-state i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            margin-bottom: 20px;
        }
        
        .btn-explore {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--accent-green);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .btn-explore:hover {
            background-color: #3c8c40;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="../../assets/img/logo.jpg" alt="Logo Urbaverse">
                    <span style="color: white; font-weight: 700; font-size: 24px;">Urbaverse</span>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="defis.php">Défis</a></li>
                    <li><a href="../backoffice/dashboard/index.php">Backoffice</a></li>
                </ul>
            </nav>
            <div class="user-info">
                <?php if(isset($_SESSION['points'])): ?>
                    <span><i class="fas fa-leaf"></i> <?php echo $_SESSION['points']; ?> points</span>
                <?php else: ?>
                    <span><i class="fas fa-leaf"></i> 0 points</span>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['username']) && $_SESSION['user_id'] !== 'demo_user'): ?>
                    <span><i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?></span>
                    <a href="logout.php" style="color: white; margin-left: 10px;"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login.php" style="text-decoration: none;"><span><i class="fas fa-user"></i> Invité (Connexion)</span></a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- PROFILE CONTENT -->
    <div class="container profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                <h1 class="profile-name"><?php echo $_SESSION['username']; ?></h1>
                <p class="profile-email"><?php echo $_SESSION['email']; ?></p>
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $_SESSION['points']; ?></div>
                        <div class="stat-label">Points verts</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($participations); ?></div>
                        <div class="stat-label">Défis participés</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mes défis participés -->
        <div class="profile-section">
            <h2 class="section-title">Mes défis participés</h2>
            
            <?php if (empty($participations)): ?>
                <div class="empty-state">
                    <i class="fas fa-tasks"></i>
                    <p>Vous n'avez pas encore participé à des défis.</p>
                    <a href="defis.php" class="btn-explore">Explorer les défis</a>
                </div>
            <?php else: ?>
                <?php foreach ($participations as $participation): ?>
                    <div class="defi-card">
                        <div class="defi-image">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="defi-content">
                            <h3 class="defi-title"><?php echo htmlspecialchars($participation['Titre_D']); ?></h3>
                            <div class="defi-meta">
                                <span class="defi-badge badge-points"><?php echo $participation['Points_verts']; ?> points</span>
                                <span class="defi-badge badge-date">Participé le <?php echo date('d/m/Y', strtotime($participation['Date_Participation'])); ?></span>
                                <span class="defi-badge badge-status"><?php echo $participation['Statut_D']; ?></span>
                            </div>
                            <p class="defi-description"><?php echo htmlspecialchars(substr($participation['Description_D'], 0, 150)); ?>...</p>
                            <div class="defi-actions">
                                <a href="defi.php?id=<?php echo $participation['Id_Defi']; ?>" class="btn-view">Voir le défi</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="../../assets/img/logo.jpg" alt="Logo Urbaverse" style="width: 80px; height: 80px; border-radius: 50%;">
                    <p>Ensemble, rendons notre quartier plus vert et plus durable.</p>
                </div>
                <div class="footer-links">
                    <h3>Liens rapides</h3>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="defis.php">Défis</a></li>
                        <li><a href="../backoffice/dashboard/index.php">Backoffice</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact</h3>
                    <p><i class="fas fa-envelope"></i> contact@urbaverse.fr</p>
                    <p><i class="fas fa-phone"></i> +33 1 23 45 67 89</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Urbaverse. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html> 