<?php
session_start(); 
$_SESSION['username'] = "neyrouz";
$_SESSION['email'] = "neyrouz@gmail.com";
require_once(__DIR__ . '/../../Controller/EcoActionController.php'); // Fixed path
$controller = new EcoActionController();

// Ensuite, récupérer l'état (GET)
$etat = isset($_GET['etat']) ? $_GET['etat'] : 'encours';

// Puis récupérer les actions après modifications
$actions = $controller->getActionsByEtat($etat);

// Variables pour le header
$pageTitle = 'Éco-Actions';
$activePage = 'eco_actions';

// Style CSS spécifique pour les éco-actions
$additionalCss = '<style>
  .actions-disponibles {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
  }
  
  .actions-disponibles form {
    margin-bottom: 25px;
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 8px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 15px;
  }
  
  .actions-disponibles select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: white;
    min-width: 150px;
  }
  
  .actions-disponibles button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
  }
  
  .actions-disponibles button:hover {
    background-color: #388E3C;
  }
  
  .action-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    padding: 20px;
    transition: transform 0.3s;
  }
  
  .action-card:hover {
    transform: translateY(-5px);
  }
  
  .action-header {
    border-bottom: 2px solid #f0f0f0;
    margin-bottom: 15px;
    padding-bottom: 10px;
  }
  
  .action-header h3 {
    color: #2E7D32;
    margin: 0;
    font-size: 1.5rem;
  }
  
  .action-description {
    line-height: 1.6;
    margin-bottom: 15px;
  }
  
  .action-details {
    list-style: none;
    padding: 0;
    margin-bottom: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
  }
  
  .action-details li {
    padding: 8px;
    background-color: #f9f9f9;
    border-radius: 5px;
  }
  
  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
  }
  
  .btn-primary {
    background-color: #4CAF50;
    color: white;
  }
  
  .btn-primary:hover {
    background-color: #388E3C;
  }
  
  .btn-secondary {
    background-color: #f44336;
    color: white;
  }
  
  .btn-secondary:hover {
    background-color: #d32f2f;
  }
</style>';

// Inclure le header commun
include_once '../includes/header.php';
?>

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
            <form method="POST" action="../../Controller/participantController.php" style="display:inline;">
                <input type="hidden" name="action_type" value="participer">
                <input type="hidden" name="id_action" value="<?= htmlspecialchars($action['id_action']) ?>">
                <button type="submit" class="btn btn-primary">Je participe</button>
            </form>

            <!-- Formulaire pour annuler -->
            <form method="POST" action="../../Controller/participantController.php" style="display:inline;">
                <input type="hidden" name="action_type" value="annuler">
                <input type="hidden" name="id_action" value="<?= htmlspecialchars($action['id_action']) ?>">
                <button type="submit" class="btn btn-secondary">J'annule ma participation</button>
            </form>
            
        </div>
        
    <?php endforeach; ?>
  </section>
</main>

<?php
// Inclure le footer commun
include_once '../includes/footer.php';
?> 
