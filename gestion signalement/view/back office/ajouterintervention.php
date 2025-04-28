<?php
    include_once '../../controller/interventionctrl.php';
    include_once '../../controller/signalementctrl.php';
    include_once '../../model/intervention.php';
    include_once __DIR__ . '/../../../config.php';

    $interventionC = new InterventionC();
    $signalementC = new SignalementC();

    $error = "";
    $success = "";

    $listeSignalements = $signalementC->afficherSignalement();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST['id_signalement']) && !empty($_POST['statut'])) {
            $newIntervention = new Intervention();
            $newIntervention->setIdSignalement((int)$_POST['id_signalement']);
            $newIntervention->setStatut($_POST['statut']);

            $result = $interventionC->ajouterIntervention($newIntervention);

            if ($result !== false) {
                header('Location: afficherintervention.php?added=true');
                exit;
            } else {
                $error = "Une erreur est survenue lors de l'ajout de l'intervention.";
            }
        } else {
            $error = "Veuillez sélectionner un signalement et un statut.";
        }
    }

    $selectedSignalementId = $_GET['id_signalement'] ?? null;

    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Ajouter Intervention - Backoffice - Urbaverse</title>
        <link rel="stylesheet" href="css/backoffice.css" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
        <link href="css/sb-admin-2.min.css" rel="stylesheet">
    </head>
    <body>
        <aside class="sidebar">
          <div class="logo">
            <h1>Urbaverse</h1>
          </div>
          <nav class="sidebar-nav">
            <ul>
              <li><a href="index.php">Dashboard</a></li>
              <li><a href="index.php">Signalements</a></li>
              <li><a href="afficherintervention.php" class="active">Interventions</a></li>
              <li><a href="#users">Utilisateurs</a></li>
              <li><a href="#settings">Paramètres</a></li>
              <li class="home-link"><a href="../front office/index.html">Retour à l'accueil</a></li>
            </ul>
          </nav>
        </aside>

        <main class="main-content">
            <header class="content-header">
                <h1>Ajouter une Nouvelle Intervention</h1>
                <div class="user-info">
                    <span>Admin</span>
                </div>
            </header>

            <div class="container-fluid">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Créer une Intervention</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form class="user" action="ajouterintervention.php<?= $selectedSignalementId ? '?id_signalement='.$selectedSignalementId : '' ?>" method="POST">

                            <div class="form-group">
                                <label for="id_signalement">Signalement Concerné <span class="text-danger">*</span>:</label>
                                <select class="form-control" id="id_signalement" name="id_signalement">
                                    <option value="">-- Sélectionner un Signalement --</option>
                                    <?php if (!empty($listeSignalements)): ?>
                                        <?php foreach ($listeSignalements as $signalement): ?>
                                            <option value="<?= htmlspecialchars($signalement['id_signalement']) ?>"
                                                <?= ($selectedSignalementId == $signalement['id_signalement']) ? 'selected' : '' ?>>
                                                #<?= htmlspecialchars($signalement['id_signalement']) ?>: <?= htmlspecialchars($signalement['titre']) ?> (Statut: <?= htmlspecialchars($signalement['statut']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="" disabled>Aucun signalement disponible</option>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">Sélectionnez le signalement pour lequel créer une intervention.</small>
                            </div>

                           <div class="form-group">
                                <label for="statut">Statut Initial <span class="text-danger">*</span>:</label>
                                <select class="form-control" id="statut" name="statut">
                                    <option value="non traité" selected>Non traité</option>
                                    <option value="en cours">En cours</option>
                               </select>
                            </div>

                            <hr>

                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                <i class="fas fa-plus-circle fa-sm"></i> Créer l'Intervention
                            </button>
                        </form>

                         <div class="text-center mt-4">
                            <a href='afficherintervention.php' class="btn btn-secondary">
                                 <i class="fas fa-arrow-left fa-sm"></i> Annuler et Retour
                            </a>
                        </div>
                    </div>
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
            
            
            const textInputs = document.querySelectorAll('input[type="text"], textarea');
            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    nettoyerInput(this);
                });
            });
            
            
            form.addEventListener('submit', function(event) {
                let isValid = true;
                let errorMessages = [];
                
                
                const idSignalement = document.getElementById('id_signalement');
                if (!idSignalement.value) {
                    errorMessages.push('Veuillez sélectionner un signalement');
                    isValid = false;
                }
                
                
                const statut = document.getElementById('statut');
                if (!statut.value) {
                    errorMessages.push('Veuillez sélectionner un statut');
                    isValid = false;
                }
                
                
                textInputs.forEach(function(input) {
                    if (input.value && !contientUniquementLettresEtChiffres(input.value)) {
                        const labelText = input.previousElementSibling ? input.previousElementSibling.textContent : 'Ce champ';
                        errorMessages.push(labelText + ' ne doit contenir que des lettres, des chiffres et quelques caractères comme virgules et points');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    event.preventDefault();
                    alert('Erreurs :\n- ' + errorMessages.join('\n- '));
                }
            });
        });
        </script>
    </body>
    </html>