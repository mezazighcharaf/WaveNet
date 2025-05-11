<?php
require_once __DIR__ . '/../../controller/EtapeController.php';
require_once __DIR__ . '/../../controller/DefiController.php';

$etapeController = new EtapeController();
$defiController = new DefiController();

$defi_id = isset($_GET['defi_id']) ? intval($_GET['defi_id']) : 0;
$defi = $defiController->getDefi($defi_id);
$etapes = $etapeController->getEtapesByDefi($defi_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Étapes du défi</title>
    <link rel="stylesheet" href="../../assets/css/frontoffice.css">
</head>
<body>
    <h1>Étapes du défi : <?php echo htmlspecialchars($defi ? $defi->Titre_D : 'Défi inconnu'); ?></h1>
    <a href="defis.php" class="btn btn-secondary">Retour aux défis</a>
    <div class="etapes-list">
        <?php if (empty($etapes)): ?>
            <p>Aucune étape pour ce défi.</p>
        <?php else: ?>
            <?php foreach($etapes as $etape): ?>
                <div class="etape-card">
                    <h3><?php echo htmlspecialchars($etape['Titre_E']); ?></h3>
                    <p><?php echo htmlspecialchars($etape['Description_E']); ?></p>
                    <span>Ordre : <?php echo htmlspecialchars($etape['Ordre']); ?></span>
                    <span>Points bonus : <?php echo htmlspecialchars($etape['Points_Bonus']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>