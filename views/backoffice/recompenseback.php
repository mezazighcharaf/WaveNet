<?php
require_once(__DIR__ . '/../../controller/partenaireController.php');
require_once(__DIR__ . '/../../controller/recompenseController.php');

// Contrôleurs
$controller = new PartenaireController();
$recController = new RecompenseController();

// Initialisation des variables d'erreur
$partenaireError = null;
$recompenseError = null;

// Gestion des actions pour les partenaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        try {
            $controller->create($_POST['nom_part'], $_POST['tel'], $_POST['mail'], $_POST['adresse']);
            header("Location: recompenseback.php");
            exit;
        } catch (Exception $e) {
            $partenaireError = $e->getMessage();
        }
    }

    if (isset($_POST['update'])) {
        try {
            $controller->update($_POST['id_part'], $_POST['nom_part'], $_POST['tel'], $_POST['mail'], $_POST['adresse']);
            header("Location: recompenseback.php");
            exit;
        } catch (Exception $e) {
            $partenaireError = $e->getMessage();
        }
    }

    if (isset($_POST['delete'])) {
        try {
            $controller->delete($_POST['id_part']);
            header("Location: recompenseback.php");
            exit;
        } catch (Exception $e) {
            $partenaireError = $e->getMessage();
        }
    }

    if (isset($_POST['add_rec'])) {
        try {
            $recController->create($_POST['nom_rec'], $_POST['description'], $_POST['cout'], $_POST['date_fin'], $_POST['id_part']);
            header("Location: recompenseback.php");
            exit;
        } catch (Exception $e) {
            $recompenseError = $e->getMessage();
        }
    }

    if (isset($_POST['update_rec'])) {
        try {
            $recController->update($_POST['id_rec'], $_POST['nom_rec'], $_POST['description'], $_POST['cout'], $_POST['date_fin'], $_POST['id_part']);
            header("Location: recompenseback.php");
            exit;
        } catch (Exception $e) {
            $recompenseError = $e->getMessage();
        }
    }

    if (isset($_POST['delete_rec'])) {
        try {
            $recController->delete($_POST['id_rec']);
            header("Location: recompenseback.php");
            exit;
        } catch (Exception $e) {
            $recompenseError = $e->getMessage();
        }
    }
}

// Récupération des données
$partenaires = $controller->listAll();
$recompenses = $recController->listAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
    <link rel="stylesheet" href="/WaveNet/views/assets/css/style11.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <title>Back Office - Gestion des Partenaires et Récompenses</title>
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
                <li><a href="/WaveNet/views/backoffice/recompenseback.php" class="active"><i class="fas fa-gift"></i> Récompenses</a></li>
                <li><a href="/WaveNet/views/backoffice/eco_actionsB.php"><i class="fas fa-leaf"></i> Eco Actions</a></li>
                <li><a href="/WaveNet/views/backoffice/gererTransports.php"><i class="fas fa-car"></i> Types de Transport</a></li>
                <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php"><i class="fas fa-home"></i> Accueil frontoffice</a></li>
            </ul>
        </nav>
    </aside>
     <!-- MAIN CONTENT -->
     <main class="main-content">
      <header class="content-header">
        <h1>Gestion des Partenaires et Récompenses</h1>
        <div>
          <a href="/WaveNet/views/frontoffice/userDashboard.php" class="btn btn-primary">Accueil frontoffice</a>
        </div>
      </header>

    <section class="backoffice-section">
        <h1>Gestion des Partenaires</h1>

        <?php if ($partenaireError): ?>
            <div class="error">
                <?php 
                    echo nl2br(htmlspecialchars($partenaireError)); 
                ?>
            </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($partenaires as $partenaire) : ?>
                <tr>
                    <form method="post" novalidate>
                        <td><?= $partenaire->getIdPart(); ?>
                            <input type="hidden" name="id_part" value="<?= $partenaire->getIdPart(); ?>">
                        </td>
                        <td class="editable" onclick="makeEditable(this)">
                            <span><?= htmlspecialchars($partenaire->getNomPart()); ?></span>
                            <input type="text" name="nom_part" value="<?= htmlspecialchars($partenaire->getNomPart()); ?>" style="display:none;">
                        </td>
                        <td class="editable" onclick="makeEditable(this)">
                            <span><?= $partenaire->getTel(); ?></span>
                            <input type="text" name="tel" value="<?= $partenaire->getTel(); ?>" style="display:none;">
                        </td>
                        <td class="editable" onclick="makeEditable(this)">
                            <span><?= htmlspecialchars($partenaire->getMail()); ?></span>
                            <input type="email" name="mail" value="<?= htmlspecialchars($partenaire->getMail()); ?>" style="display:none;">
                        </td>
                        <td class="editable" onclick="makeEditable(this)">
                            <span><?= htmlspecialchars($partenaire->getAdresse()); ?></span>
                            <input type="text" name="adresse" value="<?= htmlspecialchars($partenaire->getAdresse()); ?>" style="display:none;">
                        </td>
                        <td>
                            <button type="submit" name="update">Modifier</button>
                            <button type="submit" name="delete" onclick="return confirm('Supprimer ce partenaire ?');">Supprimer</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Ajouter un partenaire</h2>
        <form method="post" novalidate>
            <input type="text" name="nom_part" placeholder="Nom" required>
            <input type="tel" name="tel" placeholder="Téléphone" required>
            <input type="email" name="mail" placeholder="Email" required>
            <input type="text" name="adresse" placeholder="Adresse" required>
            <button type="submit" name="add">Ajouter</button>
        </form>
    </section>

    <section class="backoffice-section">
        <h1>Gestion des Récompenses</h1>

        <?php if ($recompenseError): ?>
            <div class="error">
                <?php echo nl2br(htmlspecialchars($recompenseError)); ?>
            </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Coût</th>
                    <th>Date fin</th>
                    <th>Partenaire</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recompenses as $rec) : ?>
                <tr>
                    <form method="post" novalidate>
                        <td><?= $rec->getIdRec(); ?>
                            <input type="hidden" name="id_rec" value="<?= $rec->getIdRec(); ?>">
                        </td>
                        <td class="editable" onclick="makeEditable(this)">
                            <span><?= htmlspecialchars($rec->getNomRec()); ?></span>
                            <input type="text" name="nom_rec" value="<?= htmlspecialchars($rec->getNomRec()); ?>" style="display:none;">
                        </td>
                        <td class="editable" onclick="makeEditable(this)">
                            <span><?= htmlspecialchars($rec->getDescription()); ?></span>
                            <input type="text" name="description" value="<?= htmlspecialchars($rec->getDescription()); ?>" style="display:none;">
                        </td>
                        <td class="editable" onclick="makeEditable(this)">
                            <span><?= $rec->getCout(); ?></span>
                            <input type="number" name="cout" value="<?= $rec->getCout(); ?>" style="display:none;">
                        </td>
                        <td class="editable" onclick="makeEditable(this)">
                            <span><?= $rec->getDateFin(); ?></span>
                            <input type="date" name="date_fin" value="<?= $rec->getDateFin(); ?>" style="display:none;">
                        </td>
                        <td>
                            <select name="id_part">
                                <?php foreach ($partenaires as $partenaire): ?>
                                    <option value="<?= $partenaire->getIdPart(); ?>" <?= $partenaire->getIdPart() == $rec->getIdPart() ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($partenaire->getNomPart()); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <button type="submit" name="update_rec">Modifier</button>
                            <button type="submit" name="delete_rec" onclick="return confirm('Supprimer cette récompense ?');">Supprimer</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Ajouter une récompense</h2>
        <form method="post" novalidate>
            <input type="text" name="nom_rec" placeholder="Nom" required>
            <input type="text" name="description" placeholder="Description" required>
            <input type="number" name="cout" placeholder="Coût" required >
            <input type="date" name="date_fin" required>
            <select name="id_part" required>
                <option value="">-- Sélectionner un partenaire --</option>
                <?php foreach ($partenaires as $partenaire): ?>
                    <option value="<?= $partenaire->getIdPart(); ?>">
                        <?= htmlspecialchars($partenaire->getNomPart()); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="add_rec">Ajouter</button>
        </form>
    </section>

    </main>

    <script>
        function makeEditable(cell) {
            const span = cell.querySelector('span');
            const input = cell.querySelector('input');
            if (span && input) {
                span.style.display = 'none';
                input.style.display = 'inline-block';
                input.focus();
            }
        }
    </script>

</body>
</html>