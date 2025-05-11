<?php
    session_start();

    // Vérification de l'authentification
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Vous devez être connecté pour accéder à cette page.";
        header('Location: /WaveNet/views/frontoffice/login.php');
        exit;
    }

    // Vérification du niveau d'utilisateur (admin a accès direct, client est redirigé)
    $userNiveau = $_SESSION['user_niveau'] ?? 'client';
    if ($userNiveau !== 'admin') {
        header('Location: /WaveNet/views/frontoffice/userDashboard.php');
        exit;
    }

    include_once '../../controller/interventionctrl.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/WaveNet/views/includes/config.php';

    $interventionC = new InterventionC();
    $liste = $interventionC->afficherIntervention();

    $message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
        if ($interventionC->deleteIntervention($_POST['delete_id'])) {
             header('Location: interventions.php?deleted=true');
             exit;
        } else {
             $message = '<div class="alert alert-danger">Erreur lors de la suppression.</div>';
        }
    }

     if (isset($_GET['deleted']) && $_GET['deleted'] == 'true') {
         $message = '<div class="alert alert-success">Intervention supprimée avec succès.</div>';
     }
     if (isset($_GET['added']) && $_GET['added'] == 'true') {
        $message = '<div class="alert alert-success">Intervention ajoutée avec succès.</div>';
    }
    if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
        $message = '<div class="alert alert-success">Intervention mise à jour avec succès.</div>';
    }

    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <title>Backoffice - Interventions - WaveNet</title>
      <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
      <link rel="stylesheet" href="/WaveNet/views/assets/css/admin-dashboard.css" />
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css">
      <link href="css/sb-admin-2.min.css" rel="stylesheet">
       <style>
           .badge-non-traite { background-color: #6c757d; color: white; }
           .badge-en-cours { background-color: #ffc107; color: #333; }
           .badge-traite { background-color: #28a745; color: white; }
       </style>
    </head>
    <body>
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
            <li><a href="/WaveNet/views/backoffice/interventions.php" class="active"><i class="fas fa-tools"></i> Interventions</a></li>
            <li><a href="/WaveNet/views/backoffice/recompenseback.php"><i class="fas fa-gift"></i> Récompenses</a></li>
            <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
          </ul>
        </nav>
      </aside>

      <main class="main-content">
        <header class="content-header">
          <h1>Gestion des Interventions</h1>
          <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?> (Admin)</span>
            <a href="/WaveNet/controller/UserController.php?action=logout" class="logout-link"><i class="fas fa-sign-out-alt"></i></a>
          </div>
        </header>

        <div class="container-fluid">
            <?= $message ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Liste des Interventions</h6>
                     <a href="ajouterintervention.php" class="btn btn-success btn-sm">
                         <i class="fas fa-plus fa-sm"></i> Ajouter une Intervention
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTableIntervention" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Signalement Lié (ID)</th>
                                    <th>Titre Signalement</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($liste)): ?>
                                    <?php foreach ($liste as $intervention):
                                        $statut_class = 'badge-secondary';
                                        if ($intervention['statut'] === 'non traité') $statut_class = 'badge-non-traite';
                                        elseif ($intervention['statut'] === 'en cours') $statut_class = 'badge-en-cours';
                                        elseif ($intervention['statut'] === 'traité') $statut_class = 'badge-traite';
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($intervention['id_intervention']) ?></td>
                                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($intervention['date_intervention']))) ?></td>
                                            <td class="text-center">
                                                 <span class="badge <?= $statut_class ?>">
                                                     <?= htmlspecialchars(ucfirst($intervention['statut'])) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($intervention['id_signalement']): ?>
                                                    <a href="modifiersignalement.php?id=<?= htmlspecialchars($intervention['id_signalement']) ?>" title="Voir le signalement">
                                                        <?= htmlspecialchars($intervention['id_signalement']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                             <td><?= htmlspecialchars($intervention['signalement_titre'] ?? 'N/A') ?></td>
                                            <td class="text-center">
                                                <a href="modifierintervention.php?id=<?= htmlspecialchars($intervention['id_intervention']) ?>" class="btn btn-warning btn-sm" title="Modifier">
                                                     <i class="fas fa-edit"></i>
                                                 </a>
                                                <form action="interventions.php" method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette intervention ?');">
                                                    <input type="hidden" name="delete_id" value="<?= htmlspecialchars($intervention['id_intervention']) ?>" />
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                 <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Aucune intervention trouvée.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

      </main>

      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
      <script src="js/sb-admin-2.min.js"></script>

    </body>
    </html> 