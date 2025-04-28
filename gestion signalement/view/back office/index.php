<?php
include_once '../../Controller/signalementctrl.php';
include_once '../../../config.php';

$signalementC = new Signalementc();
$liste = $signalementC->afficherSignalement();

// Gérer la suppression
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $signalementC->deleteSignalement($_POST['delete_id']);
    // Rediriger pour éviter la resoumission du formulaire
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Backoffice - Signalements - Urbaverse</title>
  <link rel="stylesheet" href="css/backoffice.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="css/sb-admin-2.min.css" rel="stylesheet">
  <!-- Meta tags pour SEO -->
  <meta name="description" content="Gestion des signalements - Backoffice Urbaverse">
</head>
<body>
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo">
      <h1>Urbaverse</h1>
    </div>
    <nav class="sidebar-nav">
      <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="index.php" class="active">Signalements</a></li>
        <li><a href="afficherintervention.php">Interventions</a></li>
        <li><a href="utilisateurs.php">Utilisateurs</a></li>
        <li><a href="parametres.php">Paramètres</a></li>
        <li class="home-link"><a href="../front office/index.php">Retour au site</a></li>
      </ul>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <header class="content-header">
      <h1>Gestion des Signalements</h1>
      <div class="user-info">
        <span>Admin</span>
        <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i></a>
      </div>
    </header>

    <!-- Section Tableau des Signalements -->
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Liste des Signalements</h6>
                <a href="addsignalement.php" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> Ajouter
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
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
                            <?php foreach ($liste as $signalement): ?>
                                <tr>
                                <td><?= htmlspecialchars($signalement['id_signalement']) ?></td>
                                <td><?= htmlspecialchars($signalement['titre']) ?></td>
                                <td><?= htmlspecialchars(substr($signalement['description'], 0, 50)) . (strlen($signalement['description']) > 50 ? '...' : '') ?></td>
                                <td><?= htmlspecialchars($signalement['emplacement']) ?></td>
                                <td><?= htmlspecialchars($signalement['date_signalement']) ?></td>
                                <td><span class="badge badge-<?= $signalement['statut'] == 'traité' ? 'success' : ($signalement['statut'] == 'en cours' ? 'warning' : 'danger') ?>"><?= htmlspecialchars($signalement['statut']) ?></span></td>
                                <td>
                                    <!-- Formulaire de suppression -->
                                    <form action="index.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="delete_id" value="<?= htmlspecialchars($signalement['id_signalement']) ?>" />
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce signalement ?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                    <!-- Lien de modification -->
                                    <a href="modifiersignalement.php?id=<?= htmlspecialchars($signalement['id_signalement']) ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                    <!-- Lien vers les interventions liées -->
                                    <a href="afficherintervention.php?signalement=<?= htmlspecialchars($signalement['id_signalement']) ?>" class="btn btn-info btn-sm"><i class="fas fa-tasks"></i></a>
                                </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </main>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/sb-admin-2.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#dataTable').DataTable({
        "language": {
          "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json"
        }
      });
    });
  </script>
</body>
</html>