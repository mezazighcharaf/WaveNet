<?php
session_start();
require_once '../Controller/AdminActionController.php';
require_once '../Controller/AdminParticipantController.php';
require_once '../Model/ParticipantBackModel.php';
require_once '../Model/EcoActionBackModel.php';
require_once '../Config/database.php';
require_once '../libs/fpdf/fpdf.php';
require_once '../Controller/sendReminder.php';
require_once(__DIR__ . '/../Controller/AdminParticipantController.php');
$adminController = new AdminParticipantController();
$statsNiveaux = $adminController->getStatistiquesParNiveau();
// Récupérer le nom du participant à partir du formulaire (ou autre source)
$nom_participant = isset($_POST['nom_participant']) ? $_POST['nom_participant'] : 'Nom par défaut';  // Si aucun nom n'est donné, on met un nom par défaut


// Inclure le fichier avec la fonction sendReminder


// Exemple d'appel de la fonction
$participantEmail = 'boutaieb03yosr@gmail.com';  // Email du participant
$participantName = 'yosr';  // Nom du participant
$eventDate = '2025-05-10 10:00';  // Date de l'événement

// Appel de la fonction pour envoyer le rappel
//sendReminder($participantEmail, $participantName, $eventDate);

// Instancier les modèles
$ecoActionModel = new EcoActionBackModel();
$participantModel = new ParticipantBackModel();

// Récupérer toutes les actions écologiques
$ecoActions = $ecoActionModel->getAllEcoActions();

// Récupérer tous les participants
$participants = $participantModel->getAllParticipants();

// Vérifier si une action doit être modifiée
$actionToModify = null;
if (isset($_GET['id_action'])) {
    $actionToModify = $ecoActionModel->getEcoActionById($_GET['id_action']);
}

// Vérifier si un participant doit être modifié
$participantToModify = null;
$nom_action = '';
if (isset($_GET['id_participant'])) {
    $participantToModify = $participantModel->getParticipantById($_GET['id_participant']);
    if ($participantToModify && isset($participantToModify['id_action'])) {
        $db = Config::getConnexion();
        $query = "SELECT nom_action FROM eco_action WHERE id_action = :id_action";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_action', $participantToModify['id_action'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nom_action = $result['nom_action'] ?? '';
    }
}

// Récupérer les données saisies et erreurs
$formData = $_SESSION['formData'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['formData'], $_SESSION['errors']);
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Actions Écologiques</title>
    <link rel="stylesheet" href="eco_actionsB.css">
    <style>
        .error { color: red; font-size: 0.9em; margin-top: 5px; display: block; }
        .success { color: green; margin-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 8px; margin-top: 5px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: center; }
        th { background-color: #f2f2f2; }
        button { margin: 5px; }
    </style>
</head>

<body>
    <nav class="sidebar">
        <h2>WaveNet</h2>
        <ul>
            <li><a href="dashboard.html">Dashboard</a></li>
            <li><a href="utilisateurs.html">Utilisateurs</a></li>
            <li><a href="Infrastructures.html">Infrastructures</a></li>
            <li><a href="defis.html">Défis</a></li>
            <li><a href="eco_actionsB.php" class="active">Eco Action</a></li>
            <li><a href="recompenses.html">Récompenses</a></li>
            <li><a href="signalements.html">Signalements</a></li>
            <li><a href="#">Paramètres</a></li>
            <li><a href="#">Déconnexion</a></li>
            <li class="home-link"><a href="index.html">Retour à l'accueil</a></li>
        </ul>
    </nav>

    <main>
    <form action="certificat.php" method="POST">
    <label for="nom_participant">Nom du participant :</label>
    <input type="text" id="nom_participant" name="nom" required>
    <button type="submit">Générer le certificat</button>
</form>


        <h1>Gestion des Actions Écologiques</h1>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Formulaire Action -->
        <form method="POST" novalidate action="../Controller/AdminActionController.php" id="ecoActionForm">
            <input type="hidden" name="action" value="<?= isset($actionToModify) ? 'update' : 'add' ?>">
            <input type="hidden" name="id_action" value="<?= $actionToModify['id_action'] ?? '' ?>">

            <div class="form-group">
                <label for="nom">Nom de l'action</label>
                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($formData['nom'] ?? $actionToModify['nom_action'] ?? '') ?>" required>
                <?php if (isset($errors['nom'])): ?><span class="error"><?= htmlspecialchars($errors['nom']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" required><?= htmlspecialchars($formData['description'] ?? $actionToModify['description_action'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?><span class="error"><?= htmlspecialchars($errors['description']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" value="<?= htmlspecialchars($formData['date'] ?? $actionToModify['date'] ?? '') ?>" required>
                <?php if (isset($errors['date'])): ?><span class="error"><?= htmlspecialchars($errors['date']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="statut">Statut</label>
                <select name="statut" id="statut" required>
                    <option value="">Sélectionner</option>
                    <option value="encours" <?= (isset($formData['statut']) && $formData['statut'] == 'encours') || (isset($actionToModify) && $actionToModify['etat'] == 'encours') ? 'selected' : '' ?>>En cours</option>
                    <option value="termine" <?= (isset($formData['statut']) && $formData['statut'] == 'termine') || (isset($actionToModify) && $actionToModify['etat'] == 'termine') ? 'selected' : '' ?>>Terminée</option>
                    <option value="annule" <?= (isset($formData['statut']) && $formData['statut'] == 'annule') || (isset($actionToModify) && $actionToModify['etat'] == 'annule') ? 'selected' : '' ?>>Annulée</option>
                </select>
                <?php if (isset($errors['statut'])): ?><span class="error"><?= htmlspecialchars($errors['statut']) ?></span><?php endif; ?>
            </div>

            

            <div class="form-group">
                <label for="categorie">Catégorie</label>
                <select name="categorie" id="categorie" required>
                    <option value="">Sélectionner</option>
                    <option value="environnement" <?= (isset($formData['categorie']) && $formData['categorie'] == 'environnement') || (isset($actionToModify) && $actionToModify['categorie'] == 'environnement') ? 'selected' : '' ?>>Environnement</option>
<option value="biodiversité" <?= (isset($formData['categorie']) && $formData['categorie'] == 'biodiversité') || (isset($actionToModify) && $actionToModify['categorie'] == 'biodiversité') ? 'selected' : '' ?>>Biodiversité</option>
<option value="recyclage" <?= (isset($formData['categorie']) && $formData['categorie'] == 'recyclage') || (isset($actionToModify) && $actionToModify['categorie'] == 'recyclage') ? 'selected' : '' ?>>Recyclage</option>
<option value="energie" <?= (isset($formData['categorie']) && $formData['categorie'] == 'energie') || (isset($actionToModify) && $actionToModify['categorie'] == 'energie') ? 'selected' : '' ?>>Énergie</option>

                </select>
                <?php if (isset($errors['categorie'])): ?><span class="error"><?= htmlspecialchars($errors['categorie']) ?></span><?php endif; ?>
            </div>

            <button type="submit"><?= isset($actionToModify) ? 'Modifier' : 'Ajouter' ?></button>
        </form>

        <!-- Table des actions -->
        <h2>Liste des Actions</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Points Verts</th>
                    <th>Catégorie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ecoActions as $action): ?>
                    <tr>
                        <td><?= htmlspecialchars($action['id_action']) ?></td>
                        <td><?= htmlspecialchars($action['nom_action']) ?></td>
                        <td><?= htmlspecialchars($action['description_action']) ?></td>
                        <td><?= htmlspecialchars($action['date']) ?></td>
                        <td><?= htmlspecialchars($action['etat']) ?></td>
                        <td><?= htmlspecialchars($action['point_vert']) ?></td>
                        <td><?= htmlspecialchars($action['categorie']) ?></td>
                        <td>
                            <a href="eco_actionsB.php?id_action=<?= $action['id_action'] ?>"><button>Modifier</button></a>
                            <form method="POST" action="../Controller/AdminActionController.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_action" value="<?= $action['id_action'] ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

<!-- Formulaire Participant -->
<h1>Gestion des Participants</h1>
<form method="POST" novalidate action="../Controller/AdminParticipantController.php">
    <input type="hidden" name="action" value="<?= isset($participantToModify) ? 'update' : 'add' ?>">
    <input type="hidden" name="id_participant" value="<?= $participantToModify['id_participant'] ?? '' ?>">

    <div class="form-group">
        <label for="nom_participant">Nom</label>
        <input type="text" name="nom_participant" id="nom_participant" value="<?= htmlspecialchars($formData['nom_participant'] ?? $participantToModify['nom_participant'] ?? '') ?>" required>
        <?php if (isset($errors['nom_participant'])): ?><span class="error"><?= htmlspecialchars($errors['nom_participant']) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="nom_action">Action associée</label>
        <select name="nom_action" id="nom_action" required>
            <option value="">Sélectionner une action</option>
            <?php foreach ($ecoActions as $action): ?>
                <option value="<?= $action['nom_action'] ?>" <?= (isset($participantToModify) && $participantToModify['id_action'] == $action['id_action']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($action['nom_action']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['nom_action'])): ?><span class="error"><?= htmlspecialchars($errors['nom_action']) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="email_participant">Email</label>
        <input type="email" name="email_participant" id="email_participant" value="<?= htmlspecialchars($formData['email_participant'] ?? $participantToModify['email_participant'] ?? '') ?>" required>
        <?php if (isset($errors['email_participant'])): ?><span class="error"><?= htmlspecialchars($errors['email_participant']) ?></span><?php endif; ?>
    </div>

    <div class="form-group">
        <label for="date_inscrit">Date d'inscription</label>
        <input type="date" name="date_inscrit" id="date_inscrit" value="<?= htmlspecialchars($formData['date_inscrit'] ?? $participantToModify['date_inscrit'] ?? '') ?>" required>
        <?php if (isset($errors['date_inscrit'])): ?><span class="error"><?= htmlspecialchars($errors['date_inscrit']) ?></span><?php endif; ?>
    </div>
    <div class="form-group">

    <label for="niveau">Niveau</label>
    <select name="niveau" id="niveau" required>
        <option value="">Sélectionner un niveau</option>
        <option value="Débutant" <?= (isset($formData['niveau']) && $formData['niveau'] == 'Débutant') || (isset($participantToModify) && $participantToModify['niveau'] == 'Débutant') ? 'selected' : '' ?>>Débutant</option>
        <option value="Intermédiaire" <?= (isset($formData['niveau']) && $formData['niveau'] == 'Intermédiaire') || (isset($participantToModify) && $participantToModify['niveau'] == 'Intermédiaire') ? 'selected' : '' ?>>Intermédiaire</option>
        <option value="Expert" <?= (isset($formData['niveau']) && $formData['niveau'] == 'Expert') || (isset($participantToModify) && $participantToModify['niveau'] == 'Avancé') ? 'selected' : '' ?>>Avancé</option>
    </select>
    <?php if (isset($errors['niveau'])): ?><span class="error"><?= htmlspecialchars($errors['niveau']) ?></span><?php endif; ?>
</div>


    <button type="submit"><?= isset($participantToModify) ? 'Modifier' : 'Ajouter' ?></button>
</form>


        <!-- Table Participants -->
<h2>Liste des Participants</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Date d'inscription</th>
            <th>Actions</th>
            <th>Nom des actions</th>
            <th>Niveau</th><!-- Nouvelle colonne -->
        </tr>
    </thead>
    <tbody>
        <?php foreach ($participants as $participant): ?>
            <tr>
                <td><?= htmlspecialchars($participant['id_participant']) ?></td>
                <td><?= htmlspecialchars($participant['nom_participant']) ?></td>
                <td><?= htmlspecialchars($participant['email_participant']) ?></td>
                <td><?= htmlspecialchars($participant['date_inscrit']) ?></td>
                <td>
                    <a href="eco_actionsB.php?id_participant=<?= $participant['id_participant'] ?>"><button>Modifier</button></a>
                    <form method="POST" action="../Controller/AdminParticipantController.php" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_participant" value="<?= $participant['id_participant'] ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </td>
                <td>
                    <?php 
                    // Récupérer le nom de l'action associée
                    if (isset($participant['id_action'])) {
                        $db = Config::getConnexion();
                        $query = "SELECT nom_action FROM eco_action WHERE id_action = :id_action";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':id_action', $participant['id_action'], PDO::PARAM_INT);
                        $stmt->execute();
                        $action = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo htmlspecialchars($action['nom_action'] ?? 'Aucune action');
                    } else {
                        echo 'Aucune action';
                    }
                    ?>
                </td>
                <td><?= htmlspecialchars($participant['niveau']) ?></td>
            </tr>
            <td>
    <a href="eco_actionsB.php?id_participant=<?= $participant['id_participant'] ?>"></a>
    <form method="POST" action="send_reminder_action.php" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment envoyer un rappel à <?= htmlspecialchars($participant['nom_participant']) ?> ?');">
        <input type="hidden" name="id_participant" value="<?= $participant['id_participant'] ?>">
        <button type="submit">📩 Envoyer Rappel</button>
    </form>
</td>

        <?php endforeach; ?>
        
    </tbody>
    
    
</table>



    </main>

</section>
<section class="statistiques-section">
  <h2>Statistiques des Participants par Niveau</h2>

  <canvas id="niveauChart" width="400" height="400"></canvas>

  <?php
    // Préparer les données pour le graphique
    $labels = [];
    $data = [];
    foreach ($statsNiveaux as $stat) {
        $labels[] = htmlspecialchars($stat['niveau']);
        $data[] = $stat['total'];
    }
  ?>

  <script>
    const ctx = document.getElementById('niveauChart').getContext('2d');
    const niveauChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
          label: 'Pourcentage des niveaux',
          data: <?= json_encode($data) ?>,
          backgroundColor: [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.label || '';
                let value = context.parsed;
                let total = <?= array_sum($data) ?>;
                let percentage = (value / total * 100).toFixed(2) + '%';
                return label + ': ' + percentage;
              }
            }
          }
        }
      }
    });
  </script>
</body>
</html>