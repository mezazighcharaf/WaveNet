<?php
session_start();
require_once '../../Controller/AdminActionController.php';
require_once '../../Controller/AdminParticipantController.php';
require_once '../../models/ParticipantModel.php';
require_once '../../models/EcoActionModel.php';
require_once '../../views/includes/config.php';
require_once '../../vendor/fpdf/fpdf.php';
require_once '../../Controller/sendReminder.php';

// Initialiser les variables pour éviter les warnings
$formData = $_SESSION['formData'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['formData'], $_SESSION['errors']);

// Fonctions de sécurité pour éviter les undefined array keys
function safeGet($array, $key, $default = '') {
    return $array[$key] ?? $default;
}

$adminController = new AdminParticipantController();
$statsNiveaux = $adminController->getStatistiquesParNiveau();

// Instancier les modèles
$ecoActionModel = new EcoActionModel();
$participantModel = new ParticipantModel();

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
        $db = connectDB();
        $query = "SELECT nom_action FROM eco_action WHERE id_action = :id_action";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_action', $participantToModify['id_action'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nom_action = $result['nom_action'] ?? '';
    }
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestion des Actions Écologiques</title>
    <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
    <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
    <link rel="stylesheet" href="/WaveNet/views/backoffice/css/backoffice.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>

<body>
    <!-- SIDEBAR -->
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
                <li><a href="/WaveNet/views/backoffice/interventions.php"><i class="fas fa-tools"></i> Interventions</a></li>
                <li><a href="/WaveNet/views/backoffice/recompenseback.php"><i class="fas fa-gift"></i> Récompenses</a></li>
                <li><a href="/WaveNet/views/backoffice/eco_actionsB.php" class="active"><i class="fas fa-leaf"></i> Eco Actions</a></li>
                <li><a href="/WaveNet/views/backoffice/gererTransports.php"><i class="fas fa-car"></i> Types de Transport</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="content-header">
            <h1>Gestion des Actions Écologiques</h1>
            <div>
                <a href="/WaveNet/views/frontoffice/userDashboard.php" class="btn btn-primary">Accueil frontoffice</a>
            </div>
        </header>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="certificat.php" method="POST" class="backoffice-section">
            <h2>Générer un certificat</h2>
            <div class="form-group">
                <label for="nom_participant">Nom du participant :</label>
                <input type="text" id="nom_participant" name="nom" required>
                <button type="submit" class="btn btn-primary">Générer le certificat</button>
            </div>
        </form>

        <section class="backoffice-section">
            <h2>Ajouter une Action Écologique</h2>
            <!-- Formulaire Action -->
            <form method="POST" novalidate action="../../Controller/AdminActionController.php" id="ecoActionForm">
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

                <button type="submit" class="btn btn-primary"><?= isset($actionToModify) ? 'Modifier' : 'Ajouter' ?></button>
            </form>
        </section>

        <section class="backoffice-section">
            <!-- Table des actions -->
            <h2>Liste des Actions</h2>
            <div class="table-responsive">
                <table class="data-table">
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
                                    <a href="eco_actionsB.php?id_action=<?= $action['id_action'] ?>" class="btn btn-sm btn-primary">Modifier</a>
                                    <form method="POST" action="../../Controller/AdminActionController.php" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id_action" value="<?= $action['id_action'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="backoffice-section">
            <!-- Formulaire Participant -->
            <h2>Ajouter un Participant</h2>
            <form method="POST" novalidate action="../../Controller/AdminParticipantController.php">
                <input type="hidden" name="action" value="<?= isset($participantToModify) ? 'update' : 'add' ?>">
                <input type="hidden" name="id_participant" value="<?= safeGet($participantToModify, 'id_participant') ?>">

                <div class="form-group">
                    <label for="nom_participant">Nom</label>
                    <input type="text" name="nom_participant" id="nom_participant" 
                           value="<?= htmlspecialchars(safeGet($formData, 'nom_participant') ?: safeGet($participantToModify, 'nom_participant')) ?>" required>
                </div>

                <div class="form-group">
                    <label for="nom_action">Action associée</label>
                    <select name="nom_action" id="nom_action" required>
                        <option value="">Sélectionner une action</option>
                        <?php foreach ($ecoActions as $action): ?>
                            <option value="<?= htmlspecialchars($action['nom_action']) ?>" 
                                <?= (isset($participantToModify) && $participantToModify['id_action'] == $action['id_action']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($action['nom_action']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email_participant">Email</label>
                    <input type="email" name="email_participant" id="email_participant" 
                           value="<?= htmlspecialchars(safeGet($formData, 'email_participant') ?: safeGet($participantToModify, 'email_participant')) ?>" required>
                </div>

                <div class="form-group">
                    <label for="date_inscrit">Date d'inscription</label>
                    <input type="date" name="date_inscrit" id="date_inscrit" 
                           value="<?= htmlspecialchars(safeGet($formData, 'date_inscrit') ?: safeGet($participantToModify, 'date_inscrit')) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="niveau">Niveau</label>
                    <select name="niveau" id="niveau" required>
                        <option value="">Sélectionner un niveau</option>
                        <option value="Débutant" <?= (safeGet($formData, 'niveau') == 'Débutant' || safeGet($participantToModify, 'niveau') == 'Débutant') ? 'selected' : '' ?>>Débutant</option>
                        <option value="Intermédiaire" <?= (safeGet($formData, 'niveau') == 'Intermédiaire' || safeGet($participantToModify, 'niveau') == 'Intermédiaire') ? 'selected' : '' ?>>Intermédiaire</option>
                        <option value="Expert" <?= (safeGet($formData, 'niveau') == 'Expert' || safeGet($participantToModify, 'niveau') == 'Expert') ? 'selected' : '' ?>>Expert</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary"><?= isset($participantToModify) ? 'Modifier' : 'Ajouter' ?></button>
            </form>
        </section>

        <section class="backoffice-section participants-stats-container">
            <div class="participants-section">
                <h2>Liste des Participants</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="id-column">ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                                <th class="actions-column">Actions</th>
                                <th>Nom des actions</th>
                                <th class="niveau-column">Niveau</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participants as $participant): ?>
                                <tr>
                                    <td class="id-column"><?= htmlspecialchars($participant['id_participant']) ?></td>
                                    <td class="text-truncate"><?= htmlspecialchars($participant['nom_participant']) ?></td>
                                    <td class="text-truncate"><?= htmlspecialchars($participant['email_participant']) ?></td>
                                    <td><?= htmlspecialchars($participant['date_inscrit']) ?></td>
                                    <td class="actions-column">
                                        <a href="eco_actionsB.php?id_participant=<?= $participant['id_participant'] ?>" class="btn btn-sm">Modifier</a>
                                        <form method="POST" action="../../Controller/AdminParticipantController.php" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_participant" value="<?= $participant['id_participant'] ?>">
                                            <button type="submit" class="btn btn-sm">Suppr</button>
                                        </form>
                                    </td>
                                    <td class="text-truncate">
                                        <?php 
                                        // Récupérer le nom de l'action associée
                                        if (isset($participant['id_action'])) {
                                            $db = connectDB();
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
                                    <td class="niveau-column"><?= htmlspecialchars($participant['niveau']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="statistiques-section">
                <h2>Statistiques des Participants par Niveau</h2>
                <div class="chart-wrapper">
                    <canvas id="niveauChart"></canvas>
                </div>

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
                                    '#2ecc71', '#f1c40f', '#2e4f3e', '#e74c3c', '#3498db', '#9b59b6'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
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
            </div>
        </section>
    </main>
</body>
</html>