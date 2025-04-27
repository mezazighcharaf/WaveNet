<?php
require_once '../Controller/AdminActionController.php';
require_once '../Controller/AdminParticipantController.php';
require_once '../Model/participantBackModel.php';  // Ensure this path is correct
require_once '../Config/database.php';
$ecoActionModel = new EcoActionBackModel();  // Model for eco actions
$participantModel = new ParticipantBackModel(); // Use the correct class name
// Fetch all eco actions
$ecoActions = $ecoActionModel->getAllEcoActions();
// Fetch all participants
$participants = $participantModel->getAllParticipants();
// Check if there's an action to modify
$actionToModify = null;
if (isset($_GET['id_action'])) {
    $actionToModify = $ecoActionModel->getEcoActionById($_GET['id_action']);
}
// Check if there's a participant to modify
$participantToModify = null;
if (isset($_GET['id_participant'])) {
    $db = Config::getConnexion();
    $participantToModify = $participantModel->getParticipantById($_GET['id_participant']);
    $query = "SELECT nom_action FROM eco_action WHERE id_action = :id_action";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_action', $participantToModify['id_action']);  // Bind ID parameter
    $stmt->execute();
    $nom_action= $stmt->fetch(PDO::FETCH_ASSOC)['nom_action'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Actions Écologiques</title>
    <link rel="stylesheet" href="eco_actionsB.css">
</head>
<body>
    <nav class="sidebar">
        <h2>WaveNet</h2>
        <ul>
            <li><a href="dashboard.html">Dashboard</a></li>
            <li><a href="utilisateurs.html">utilisateurs</a></li>
            <li><a href="Infrastructures.html">Infrastructures</a></li>
            <li><a href="defis.html">défis</a></li>
            <li><a href="eco_actionsB.php" class="active">Eco Action</a></li>
            <li><a href="recompenses.html">récompenses</a></li>
            <li><a href="signalements.html">signalements</a></li>
            <li><a href="#">Paramètres</a></li>
            <li><a href="#">Déconnexion</a></li>
            <li class="home-link"><a href="index.html">Retour à l'accueil</a></li>
        </ul>
    </nav>
    <main>
        <h1>Gestion des Actions Écologiques</h1>
        <!-- Form for adding or modifying an action -->
        <form method="POST" novalidate action="../Controller/AdminActionController.php">
            <input type="hidden" name="action" value="<?= isset($actionToModify) ? 'update' : 'add' ?>">
            <input type="hidden" name="id_action" value="<?= $actionToModify ? $actionToModify['id_action'] : '' ?>">
            <input type="text" name="nom" placeholder="Nom de l'action" value="<?= $actionToModify ? $actionToModify['nom_action'] : '' ?>" required>
            <textarea name="description" placeholder="Description" required><?= $actionToModify ? $actionToModify['description_action'] : '' ?></textarea>
            <input type="date" name="date" value="<?= $actionToModify ? $actionToModify['date'] : '' ?>" required>
            <select name="statut" required>
                <option value="">Statut</option>
                <option value="encours" <?= $actionToModify && $actionToModify['etat'] == 'encours' ? 'selected' : '' ?>>En cours</option>
                <option value="termine" <?= $actionToModify && $actionToModify['etat'] == 'termine' ? 'selected' : '' ?>>Terminée</option>
                <option value="annule" <?= $actionToModify && $actionToModify['etat'] == 'annule' ? 'selected' : '' ?>>Annulée</option>
            </select>
            <input type="text" name="points_verts" placeholder="Nombre de points verts" required>
            <select name="categorie" required>
                <option value="">Catégorie</option>
                <option value="environnement" <?= $actionToModify && $actionToModify['categorie'] == 'environnement' ? 'selected' : '' ?>>Environnement</option>
                <option value="biodiversite" <?= $actionToModify && $actionToModify['categorie'] == 'biodiversite' ? 'selected' : '' ?>>Biodiversité</option>
                <option value="recyclage" <?= $actionToModify && $actionToModify['categorie'] == 'recyclage' ? 'selected' : '' ?>>Recyclage</option>
                <option value="energie" <?= $actionToModify && $actionToModify['categorie'] == 'energie' ? 'selected' : '' ?>>Énergie</option>
            </select>
            <button type="submit"><?= isset($actionToModify) ? 'Modifier' : 'Ajouter' ?></button>
        </form>
        <!-- List of actions -->
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
                            <form action="../Controller/AdminActionController.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_action" value="<?= $action['id_action'] ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h1>Gestion des Participants</h1>
        <!-- Form for adding or modifying a participant -->
        <form method="POST" novalidate action="../Controller/AdminParticipantController.php">
            <input type="hidden" name="action" value="<?= isset($participantToModify) ? 'update' : 'add' ?>">
            <input type="hidden" name="id_participant" value="<?= $participantToModify ? $participantToModify['id_participant'] : '' ?>">
            <input type="text" name="nom_participant" placeholder="Nom du participant" value="<?= $participantToModify ? $participantToModify['nom_participant'] : '' ?>" required>
            <input type="text" name="nom_action" placeholder="Nom de l'action" value="<?= $participantToModify ? $nom_action : '' ?>" required>
            <input type="email" name="email_participant" placeholder="Email du participant" value="<?= $participantToModify ? $participantToModify['email_participant'] : '' ?>" required>
            <input type="date" name="date_inscrit" value="<?= $participantToModify ? $participantToModify['date_inscrit'] : '' ?>" required>
            <button type="submit"><?= isset($participantToModify) ? 'Modifier' : 'Ajouter' ?></button>
        </form>
        <!-- List of participants -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
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
                            <form action="../Controller/AdminParticipantController.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id_participant" value="<?= $participant['id_participant'] ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>