<?php
// Inclure le contrôleur pour récupérer les données des participants
require_once('../Controller/AdminParticipantController.php');
require_once('../Controller/AdminActionController.php');
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
            <li><a href="utilisateurs.html">Utilisateurs</a></li>
            <li><a href="Infrastructures.html">Infrastructures</a></li>
            <li><a href="defis.html">Défis</a></li>
            <li><a href="eco_actionsB.html" class="active">Eco Actions</a></li>
            <li><a href="recompenses.html">Récompenses</a></li>
            <li><a href="signalements.html">Signalements</a></li>
            <li><a href="#">Paramètres</a></li>
            <li><a href="#">Déconnexion</a></li>
            <li class="home-link"><a href="index.html">Accueil</a></li>
        </ul>
    </nav>

    <main>
        <h1>Actions Écologiques</h1>

        <form>
            <input type="text" placeholder="Nom de l'action" required>
            <textarea placeholder="Description de l'action" required></textarea>
            <input type="date" required>

            <select required>
                <option value="">Statut</option>
                <option value="encours">En cours</option>
                <option value="termine">Terminée</option>
                <option value="annule">Annulée</option>
            </select>

            <input type="number" placeholder="Points verts" required>

            <select required>
                <option value="">Catégorie</option>
                <option value="environnement">Environnement</option>
                <option value="biodiversite">Biodiversité</option>
                <option value="recyclage">Recyclage</option>
                <option value="energie">Énergie</option>
            </select>

            <button type="submit" class="btn-primary">Ajouter Action</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Nom</th><th>Description</th><th>Date</th><th>Statut</th><th>Points</th><th>Catégorie</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td><td>Nettoyage plage</td><td>Nettoyage collectif de la plage</td><td>2025-04-22</td><td>En cours</td><td>100</td><td>Environnement</td>
                    <td>
                        <button class="btn-edit">Modifier</button>
                        <button class="btn-delete">Supprimer</button>
                    </td>
                </tr>
                <!-- autres lignes ici -->
            </tbody>
        </table>

        <h1>Participants</h1>

        <form>
            <input type="text" placeholder="Nom du participant" required>
            <input type="email" placeholder="Email du participant" required>
            <input type="date" required>
            <button type="submit" class="btn-primary">Ajouter Participant</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Nom</th><th>Email</th><th>Date d'inscription</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td><td>Sarah Ben Ali</td><td>sarah@example.com</td><td>2025-04-01</td>
                    <td>
                        <button class="btn-edit">Modifier</button>
                        <button class="btn-delete">Supprimer</button>
                    </td>
                </tr>
                <!-- autres lignes ici -->
            </tbody>
        </table>

    </main>

</body>
</html>
