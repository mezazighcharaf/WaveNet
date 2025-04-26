<?php

require_once '../Controller/AdminActionController.php';
$model = new EcoActionBackModel();
//$controller = new EcoActionController(); // Initialize the controller

// Fetch all eco actions
$ecoActions = $model->getAllEcoActions();

// Check if there's an action to modify
$actionToModify = null;
if (isset($_GET['id_action'])) {
    $actionToModify = $model->getEcoActionById($_GET['id_action']);
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
        <form method="POST"  novalidate action="../Controller/AdminActionController.php">
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
    </main>

</body>
</html>