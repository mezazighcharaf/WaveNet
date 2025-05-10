<?php
    include_once '../../controller/interventionctrl.php';
    include_once '../../controller/signalementctrl.php';
    include_once '../../models/intervention.php';
    include_once '../../config.php';

    $interventionC = new InterventionC();
    $signalementC = new SignalementC();

    $error = "";
    $success = "";
    $intervention_data = null;
    $id_intervention = null;

    if (isset($_GET['id'])) {
        $id_intervention = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    } elseif (isset($_POST['id_intervention'])) {
         $id_intervention = filter_var($_POST['id_intervention'], FILTER_VALIDATE_INT);
    }

    if ($id_intervention) {
        $intervention_data = $interventionC->getInterventionById($id_intervention);
        if (!$intervention_data) {
            header('Location: afficherintervention.php?error=notfound');
            exit;
        }
    } else {
         header('Location: afficherintervention.php?error=noid');
         exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
        if ($id_intervention && !empty($_POST['id_signalement']) && !empty($_POST['statut']) && !empty($_POST['date_intervention'])) {
             $updatedIntervention = new Intervention();
             $updatedIntervention->setIdIntervention((int)$id_intervention);
             $updatedIntervention->setIdSignalement((int)$_POST['id_signalement']);
             $updatedIntervention->setStatut($_POST['statut']);
             $date_from_form = date('Y-m-d H:i:s', strtotime($_POST['date_intervention']));
             $updatedIntervention->setDateIntervention($date_from_form);

            if ($interventionC->updateIntervention($updatedIntervention)) {
                 header('Location: afficherintervention.php?updated=true');
                 exit;
            } else {
                $error = "Erreur lors de la mise à jour de l'intervention.";
                 $intervention_data['id_signalement'] = $_POST['id_signalement'];
                 $intervention_data['statut'] = $_POST['statut'];
                 $intervention_data['date_intervention'] = $_POST['date_intervention'];
            }
        } else {
            $error = "Tous les champs requis ne sont pas remplis correctement.";
        }
    }

    $listeSignalements = $signalementC->afficherSignalement();

    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Modifier Intervention #<?= htmlspecialchars($id_intervention) ?> - WaveNet</title>
        <link rel="stylesheet" href="/WaveNet/views/assets/css/backoffice11.css" />
        <link rel="stylesheet" href="/WaveNet/views/assets/css/admin-dashboard.css" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" type="text/css">
        <link href="css/sb-admin-2.min.css" rel="stylesheet">
    </head>
    <body>
         <aside class="sidebar">
          <div class="logo">
            <img src="/WaveNet/views/assets/img/logo.png" alt="Logo WaveNet" class="logo-img">
            <h1>WaveNet</h1>
          </div>
          <nav class="sidebar-nav">
            <ul>
              <li><a href="/WaveNet/views/backoffice/index.php">Dashboard</a></li>
              <li><a href="/WaveNet/views/backoffice/listeUtilisateurs.php">Utilisateurs</a></li>
              <li><a href="/WaveNet/views/backoffice/defis.php">Défis</a></li>
              <li><a href="/WaveNet/views/backoffice/quartiers.php">Quartiers</a></li>
              <li><a href="/WaveNet/views/backoffice/gsignalement.php">Signalements</a></li>
              <li><a href="/WaveNet/views/backoffice/afficherintervention.php" class="active">Interventions</a></li>
              <li class="home-link"><a href="/WaveNet/views/frontoffice/userDashboard.php">Accueil frontoffice</a></li>
            </ul>
          </nav>
        </aside>

        <main class="main-content">
            <header class="content-header">
                 <h1>Modifier l'Intervention #<?= htmlspecialchars($id_intervention) ?></h1>
                 <div class="user-info">
                    <span>Admin</span>
                </div>
            </header>

            <div class="container-fluid">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($intervention_data): ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Modifier les Détails</h6>
                        </div>
                        <div class="card-body">
                            <form class="user" action="modifierintervention.php?id=<?= htmlspecialchars($id_intervention) ?>" method="POST">
                                 <input type="hidden" name="id_intervention" value="<?= htmlspecialchars($id_intervention) ?>">

                                <div class="form-group">
                                    <label for="id_signalement">Signalement Concerné <span class="text-danger">*</span>:</label>
                                    <select class="form-control" id="id_signalement" name="id_signalement" required>
                                        <option value="">-- Sélectionner un Signalement --</option>
                                         <?php if (!empty($listeSignalements)): ?>
                                            <?php foreach ($listeSignalements as $signalement): ?>
                                                <option value="<?= htmlspecialchars($signalement['id_signalement']) ?>"
                                                    <?= ($intervention_data['id_signalement'] == $signalement['id_signalement']) ? 'selected' : '' ?>>
                                                    #<?= htmlspecialchars($signalement['id_signalement']) ?>: <?= htmlspecialchars($signalement['titre']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                         <?php else: ?>
                                            <option value="" disabled>Aucun signalement disponible</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="date_intervention">Date Intervention <span class="text-danger">*</span>:</label>
                                    <input type="datetime-local" class="form-control" id="date_intervention" name="date_intervention"
                                           value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($intervention_data['date_intervention']))) ?>" required>
                                    <small class="form-text text-muted">Date et heure de l'intervention.</small>
                                 </div>

                                <div class="form-group">
                                    <label for="statut">Statut <span class="text-danger">*</span>:</label>
                                    <select class="form-control" id="statut" name="statut" required>
                                        <option value="Non traité" <?= $intervention_data['statut'] == 'Non traité' ? 'selected' : '' ?>>Non traité</option>
                                        <option value="En cours" <?= $intervention_data['statut'] == 'En cours' ? 'selected' : '' ?>>En cours</option>
                                        <option value="Traité" <?= $intervention_data['statut'] == 'Traité' ? 'selected' : '' ?>>Traité</option>
                                    </select>
                                </div>

                                <hr>

                                <button type="submit" name="update" class="btn btn-primary btn-user btn-block">
                                     <i class="fas fa-save fa-sm"></i> Enregistrer les Modifications
                                </button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">Impossible de charger les données de l'intervention.</div>
                <?php endif; ?>

                 <div class="text-center mt-4">
                    <a href='afficherintervention.php' class="btn btn-secondary">
                         <i class="fas fa-arrow-left fa-sm"></i> Annuler et Retour
                    </a>
                </div>
            </div>
        </main>

        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
        <script src="js/sb-admin-2.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form.user');
            
            
            function contientUniquementLettresEtChiffres(texte) {
                return /^[a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ,. '-]+$/.test(texte);
            }
            
            
            function nettoyerInput(input) {
                input.value = input.value.replace(/[^a-zA-Z0-9\sàáâäãåçéèêëîïôöùûüÿÀÁÂÄÃÅÇÉÈÊËÎÏÔÖÙÛÜŸ,. '-]/g, '');
            }
            
            
            const inputs = document.querySelectorAll('input[type="text"]');
            inputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    nettoyerInput(this);
                });
            });
            
            
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                
                const idSignalement = document.getElementById('id_signalement');
                if (!idSignalement.value) {
                    alert('Veuillez sélectionner un signalement');
                    isValid = false;
                }
                
                
                const dateIntervention = document.getElementById('date_intervention');
                if (!dateIntervention.value) {
                    alert('Veuillez spécifier une date d\'intervention');
                    isValid = false;
                }
                
                
                const statut = document.getElementById('statut');
                if (!statut.value) {
                    alert('Veuillez sélectionner un statut');
                    isValid = false;
                }
                
                
                inputs.forEach(function(input) {
                    if (input.value && !contientUniquementLettresEtChiffres(input.value)) {
                        alert('Le champ ' + input.previousElementSibling.textContent + ' ne doit contenir que des lettres, des chiffres et quelques caractères comme virgules et points');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
        </script>
    </body>
    </html>