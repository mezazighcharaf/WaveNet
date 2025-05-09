<?php
session_start(); 
$_SESSION['username'] = "neyrouz";
$_SESSION['email'] = "neyrouz@gmail.com";
require_once(__DIR__ . '/../Controller/EcoActionController.php'); // Inclure le contrôleur
$controller = new EcoActionController();


// Ensuite, récupérer l'état (GET)
$etat = isset($_GET['etat']) ? $_GET['etat'] : 'encours';

// Puis récupérer les actions après modifications
$actions = $controller->getActionsByEtat($etat);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éco-Actions</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
  <header class="main-header">
    <nav class="nav-container">
      <div class="logo">
        <h1>WaveNet</h1>
      </div>
      <ul class="nav-links">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="dashboard.php">User</a></li>
        <li><a href="blog.php">Infra</a></li>
        <li><a href="blog1.php">Défis</a></li>
        <li><a href="eco_actions.php" class="active">Eco action</a></li>
        <li><a href="blog2.php">Récompenses</a></li>
        <li><a href="report.php">Signalements</a></li>
        <li><a href="backoffice.php">Back Office</a></li>
      </ul>
      <div class="user-actions">
        <span class="points">Points verts: 150</span>
        <a href="#login" class="btn btn-secondary">Connexion</a>
      </div>
    </nav>
  </header>

  <main class="main-content">
    <section class="actions-disponibles">
      <form action="eco_actions.php" method="GET">
        <label for="etat">Filtrer par état :</label>
        <select name="etat" id="etat">
          <option value="encours" <?= $etat == 'encours' ? 'selected' : '' ?>>en cours</option>
          <option value="termine" <?= $etat == 'termine' ? 'selected' : '' ?>>terminé</option>
          <option value="annulé" <?= $etat == 'annulé' ? 'selected' : '' ?>>annulé</option>
        </select>

        <button type="submit">Filtrer</button>
      </form>

      <h2>🌱 Actions disponibles</h2>

    <?php foreach ($actions as $action): ?>
        <div class="action-card">
            <div class="action-header">
                <h3><?= htmlspecialchars($action['nom_action']) ?></h3>
            </div>

            <p class="action-description">
                <strong>Description :</strong> <?= htmlspecialchars($action['description_action']) ?>
            </p>

            <ul class="action-details">
                <li><strong>Points verts :</strong> <?= htmlspecialchars($action['point_vert']) ?></li>
                <li><strong>Catégorie :</strong> <?= htmlspecialchars($action['categorie']) ?></li>
                <li><strong>État :</strong> <?= htmlspecialchars($action['etat']) ?></li>
                <li><strong>Date :</strong> <?= htmlspecialchars($action['date']) ?></li>
            </ul>

            <!-- Formulaire pour participer -->
            <form method="POST" action="../Controller/participantController.php" style="display:inline;">
                <input type="hidden" name="action_type" value="participer">
                <input type="hidden" name="id_action" value="<?= htmlspecialchars($action['id_action']) ?>">
                <button type="submit" class="btn btn-primary">Je participe</button>
            </form>

            <!-- Formulaire pour annuler -->
            <form method="POST" action="../Controller/participantController.php" style="display:inline;">
                <input type="hidden" name="action_type" value="annuler">
                <input type="hidden" name="id_action" value="<?= htmlspecialchars($action['id_action']) ?>">
                <button type="submit" class="btn btn-secondary">J'annule ma participation</button>
            </form>
            
        </div>
        
    <?php endforeach; ?>
    </section>
  </main>

<!-- FOOTER -->
<footer>
    <div class="footer-content">
      <div class="footer-section">
        <h4>Urbaverse</h4>
        <p>Ensemble pour un avenir urbain durable</p>
      </div>
      <div class="footer-section">
        <h4>Liens Rapides</h4>
        <ul>
          <li><a href="#contact">Contact</a></li>
          <li><a href="#privacy">Confidentialité</a></li>
          <li><a href="#terms">Conditions</a></li>
          <li><a href="backoffice.html">Backoffice</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h4>Suivez-nous</h4>
        <div class="social-links">
          <a href="#twitter">Twitter</a>
          <a href="#facebook">Facebook</a>
          <a href="#instagram">Instagram</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 Urbaverse. Tous droits réservés.</p>
    </div>
  </footer>


  <script>
    function participerAction(actionId) {
      const confirmationElement = document.getElementById(`confirmation-${actionId}`);
      confirmationElement.style.display = 'block';
    }
    <script>
  </script>
</body>
</html> 
