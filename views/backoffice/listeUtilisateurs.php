<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_niveau']) || $_SESSION['user_niveau'] !== 'admin') {
    header('Location: /WaveNet/views/frontoffice/login.php');
    exit;
}
require_once '../../views/includes/config.php';
require_once '../../models/Utilisateur.php';
$db = connectDB();
if (!$db) {
    die("Erreur: Impossible d'établir une connexion à la base de données.");
}
try {
    $stmt = $db->query("SELECT * FROM UTILISATEUR ORDER BY nom, prenom");
    $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Liste des utilisateurs | WaveNet</title>
    <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
    <link rel="stylesheet" href="/WaveNet/views/assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            <h1>WaveNet</h1>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="/WaveNet/views/backoffice/index.php">Dashboard</a></li>
                <li><a href="/WaveNet/views/backoffice/listeUtilisateurs.php" class="active">Utilisateurs</a></li>
                <li><a href="/WaveNet/views/backoffice/defis.php">Défis</a></li>
                <li><a href="/WaveNet/views/backoffice/quartiers.php">Quartiers</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php">Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>
    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="content-header">
            <h1>Gestion des utilisateurs</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?> (Admin)</span>
                <a href="/WaveNet/controller/UserController.php?action=logout" class="home-button">Déconnexion</a>
            </div>
        </header>
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background-color: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div style="background-color: #ffebee; color: #c62828; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <div class="chart-card" style="margin-top: 1.5rem;">
            <div class="chart-header">
                <h3>Liste des utilisateurs</h3>
                <a href="/WaveNet/views/backoffice/index.php" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.3rem 0.8rem;">
                    <i class="fas fa-arrow-left"></i> Retour au dashboard
                </a>
            </div>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Niveau</th>
                            <th>Points verts</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($utilisateurs)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem;">Aucun utilisateur trouvé</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($utilisateurs as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['id_utilisateur']); ?></td>
                                    <td class="user-info-cell">
                                        <div class="user-avatar"><?php echo strtoupper(substr($u['prenom'], 0, 1)); ?></div>
                                        <div class="user-details">
                                            <span class="user-name"><?php echo htmlspecialchars($u['nom']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['prenom']); ?></td>
                                    <td><span class="user-email"><?php echo htmlspecialchars($u['email']); ?></span></td>
                                    <td>
                                        <span class="status-badge <?php echo $u['niveau'] === 'admin' ? 'admin' : 'client'; ?>">
                                            <?php echo htmlspecialchars($u['niveau']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['points_verts']); ?></td>
                                    <td>
                                        <?php if (isset($u['bloque']) && $u['bloque'] == 1): ?>
                                            <span class="status-badge waiting">Bloqué</span>
                                        <?php else: ?>
                                            <span class="status-badge">Actif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['niveau'] !== 'admin' || $_SESSION['user_id'] != $u['id_utilisateur']): ?>
                                            <div class="action-buttons">
                                                <?php if (isset($u['bloque']) && $u['bloque'] == 1): ?>
                                                    <a href="/WaveNet/controller/UserController.php?action=debloquerUtilisateur&id=<?php echo $u['id_utilisateur']; ?>" 
                                                    class="action-button" onclick="return confirm('Confirmer le déblocage de cet utilisateur ?');">
                                                        <i class="fas fa-unlock"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/WaveNet/controller/UserController.php?action=bloquerUtilisateur&id=<?php echo $u['id_utilisateur']; ?>" 
                                                    class="action-button" onclick="return confirm('Confirmer le blocage de cet utilisateur ?');">
                                                        <i class="fas fa-ban"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($u['niveau'] === 'admin'): ?>
                                                    <a href="/WaveNet/controller/UserController.php?action=changerNiveau&id=<?php echo $u['id_utilisateur']; ?>&niveau=client" 
                                                    class="action-button" title="Rétrograder en client" onclick="return confirm('Confirmer le changement de niveau de cet utilisateur en client ?');">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/WaveNet/controller/UserController.php?action=changerNiveau&id=<?php echo $u['id_utilisateur']; ?>&niveau=admin" 
                                                    class="action-button" title="Promouvoir en admin" onclick="return confirm('Confirmer la promotion de cet utilisateur en administrateur ?');">
                                                        <i class="fas fa-user-shield"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="/WaveNet/controller/UserController.php?action=supprimerUtilisateurAdmin&id=<?php echo $u['id_utilisateur']; ?>" 
                                                class="action-button" onclick="return confirm('Confirmer la suppression de cet utilisateur ? Cette action est irréversible.');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--gray-500); font-style: italic;">Non disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        });
    </script>
</body>
</html>
