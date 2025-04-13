<?php
require_once(__DIR__ . '/../Controller/EcoActionController.php'); // Include controller

// Create an instance of EcoActionController
$controller = new EcoActionController();

// Get all eco actions from the controller
$actions = $controller->getAllActions();
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
        <h1>Urbaverse</h1>
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
      <h2>🌱 Actions disponibles</h2>

      <?php foreach ($actions as $action): ?>
      <div class="action-card">
        <div class="action-header">
          <h3><?= htmlspecialchars($action['nom_action']) ?> <span class="action-id">#<?= $action['id_action'] ?></span></h3>
        </div>

        <p class="action-description">
          <strong>Description :</strong> <?= htmlspecialchars($action['description_action']) ?>
        </p>

        <ul class="action-details">
          <li><strong>Points verts :</strong> <?= $action['point_vert'] ?></li>
          <li><strong>Catégorie :</strong> <?= htmlspecialchars($action['categorie']) ?></li>
          <li><strong>État :</strong> <?= htmlspecialchars($action['etat']) ?></li>
          <li><strong>Date :</strong> <?= $action['date'] ?></li>
        </ul>
        <button class="btn-participer" id="btn-<?= $action['id_action'] ?>" onclick="participerAction(<?= $action['id_action'] ?>)">Je participe</button>

        <div class="confirmation-message" id="confirmation-<?= $action['id_action'] ?>" style="display: none;">
          🌱 Merci pour votre participation !
        </div>
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
  </script>
</body>
</html>
