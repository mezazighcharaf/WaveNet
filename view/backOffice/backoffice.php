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
            header("Location: backOffice.php");
            exit;
        } catch (Exception $e) {
            $partenaireError = $e->getMessage();
        }
    }

    if (isset($_POST['update'])) {
        try {
            $controller->update($_POST['id_part'], $_POST['nom_part'], $_POST['tel'], $_POST['mail'], $_POST['adresse']);
            header("Location: backOffice.php");
            exit;
        } catch (Exception $e) {
            $partenaireError = $e->getMessage();
        }
    }

    if (isset($_POST['delete'])) {
        try {
            $controller->delete($_POST['id_part']);
            header("Location: backOffice.php");
            exit;
        } catch (Exception $e) {
            $partenaireError = $e->getMessage();
        }
    }

    // Gestion des actions pour les récompenses
    if (isset($_POST['add_rec'])) {
        try {
            $recController->create($_POST['nom_rec'], $_POST['description'], $_POST['cout'], $_POST['date_fin'], $_POST['id_part']);
            header("Location: backOffice.php");
            exit;
        } catch (Exception $e) {
            $recompenseError = $e->getMessage();
        }
    }

    if (isset($_POST['update_rec'])) {
        try {
            $recController->update($_POST['id_rec'], $_POST['nom_rec'], $_POST['description'], $_POST['cout'], $_POST['date_fin'], $_POST['id_part']);
            header("Location: backOffice.php");
            exit;
        } catch (Exception $e) {
            $recompenseError = $e->getMessage();
        }
    }

    if (isset($_POST['delete_rec'])) {
        try {
            $recController->delete($_POST['id_rec']);
            header("Location: backOffice.php");
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
    <link rel="stylesheet" href="backoffice.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <title>Back Office - Gestion des Partenaires et Récompenses</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .editable { cursor: pointer; }
        input[type=text], input[type=email], input[type=number], input[type=date] { width: 120px; }
        .error { color: red; margin: 10px 0; padding: 10px; background-color: #ffeeee; border: 1px solid #ffcccc; }
        h1, h2 { margin-top: 30px; }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="logo">
        <h1>Urbaverse</h1>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li><a href="#dashboard" class="active">gestion recompenses</a></li>
          <li><a href="#reports">gestion utilisateurs</a></li>
          <li><a href="#users">gestion eco-actions</a></li>
          <li><a href="#settings">gestion infrastructures</a></li>
          <li><a href="#users">gestion signalements</a></li>
          <li><a href="#settings">gestion des défis</a></li>
          <li class="home-link"><a href="index.html">Retour à l'accueil</a></li>
        </ul>
      </nav>
    </aside>
     <!-- MAIN CONTENT -->
     <main class="main-content">
      <header class="content-header">
        <h1>Dashboard</h1>
        <div class="user-info">
          <span>Admin</span>
          <a href="index.html" class="home-button">Accueil</a>
        </div>
      </header>


    <h1>Gestion des Partenaires</h1>

    <?php if ($partenaireError): ?>
        <div class="error">
            <?php 
                // Remplacer les sauts de ligne par des <br> pour l'affichage HTML
                echo nl2br(htmlspecialchars($partenaireError)); 
            ?>
        </div>
    <?php endif; ?>

    <table>
        <!-- Le reste du code pour les partenaires reste inchangé -->
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
        <input type="text" name="tel" placeholder="Téléphone" required>
        <input type="email" name="mail" placeholder="Email" required>
        <input type="text" name="adresse" placeholder="Adresse" required>
        <button type="submit" name="add">Ajouter</button>
    </form>

<!-- ... (partie précédente inchangée jusqu'à la section des récompenses) ... -->

<h1>Gestion des Récompenses</h1>

<?php if ($recompenseError): ?>
    <div class="error">
        <?php echo nl2br(htmlspecialchars($recompenseError)); ?>
    </div>
<?php endif; ?>

<table>
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
            <form method="post">
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
<form method="post">
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

<!-- ... (suite du fichier inchangée) ... -->
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