<?php
require_once __DIR__ . '/../../../controller/EtapeController.php';
?>

<div class="etapes-section">
    <h2 class="section-title">Étapes du défi</h2>
    
    <?php
    // Get etapes for this defi
    $etapeController = new EtapeController();
    $etapes = $etapeController->getEtapesByDefi($defi['Id_Defi']);
    
    if (empty($etapes)): 
    ?>
        <div class="no-etapes">
            <p>Aucune étape n'est disponible pour ce défi.</p>
        </div>
    <?php else: ?>
        <div class="etapes-container">
            <?php foreach($etapes as $etape): ?>
                <div class="etape-card">
                    <div class="etape-header">
                        <h3><?php echo htmlspecialchars($etape['Titre_E']); ?></h3>
                        <span class="etape-status <?php echo $etape['Statut_E'] == 'Actif' ? 'active' : 'inactive'; ?>">
                            <?php echo htmlspecialchars($etape['Statut_E']); ?>
                        </span>
                    </div>
                    <div class="etape-content">
                        <p><?php echo htmlspecialchars($etape['Description_E']); ?></p>
                        <div class="etape-meta">
                            <div class="etape-order">
                                <span class="label">Ordre:</span>
                                <span class="value"><?php echo htmlspecialchars($etape['Ordre']); ?></span>
                            </div>
                            <div class="etape-points">
                                <span class="label">Points bonus:</span>
                                <span class="value"><?php echo htmlspecialchars($etape['Points_Bonus']); ?> points</span>
                            </div>
                        </div>
                    </div>
                    <?php if($etape['Statut_E'] == 'Actif'): ?>
                        <div class="etape-actions">
                            <a href="../etapes/complete.php?id=<?php echo $etape['Id_etape']; ?>" class="btn btn-primary">
                                Valider cette étape
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>