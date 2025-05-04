<?php
include_once '../../controller/signalementctrl.php';
$signalementC = new SignalementC();
$liste = $signalementC->afficherSignalement();

// Calcul des statistiques par type d'anomalie (titre)
$stats = [];
foreach ($liste as $s) {
    $type = $s['titre'];
    if (!isset($stats[$type])) $stats[$type] = 0;
    $stats[$type]++;
}
$labels = json_encode(array_keys($stats));
$values = json_encode(array_values($stats));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des Signalements</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #f9f9f9; }
        .container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #0001; padding: 30px; }
        h2 { color: #2e4f3e; text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Statistiques par type d'anomalie</h2>
        <canvas id="chartType"></canvas>
    </div>
    <script>
        const ctx = document.getElementById('chartType').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo $labels; ?>,
                datasets: [{
                    label: 'Nombre de signalements',
                    data: <?php echo $values; ?>,
                    backgroundColor: 'rgba(46, 79, 62, 0.7)',
                    borderColor: 'rgba(46, 79, 62, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html> 