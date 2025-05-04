<?php
include_once(__DIR__ . '/../../Controller/infraC.php');

$message = null;
$infraC = new infraC();
$quartiers = $infraC->getQuartiers(); // Récupère les quartiers disponibles

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_infra = $_POST['id_infra'];
    $type = $_POST['type'];
    $statut = $_POST['statut'];
    $idq = $_POST['idq'] ?? null; // Récupère l'id du quartier

    if (!empty($id_infra) && !empty($type) && !empty($statut) && !empty($idq)) {
        
        if (!preg_match('/^\d+$/', $id_infra)) {
            $message = "L'identifiant doit contenir uniquement des chiffres ❌";
        }
        elseif (!preg_match('/^[\p{L} ]+$/u', $type)) {
            $message = "Le type doit contenir uniquement des lettres ❌";
        }
        elseif (!preg_match('/^[\p{L} ]+$/u', $statut)) {
            $message = "Le statut doit contenir uniquement des lettres ❌";
        }
        else {
            $existant = $infraC->recupererInfrastructureParId($id_infra);
            if ($existant !== false) {
                $message = "L'identifiant existe déjà, merci d'en choisir un autre ❌";
            } else {
                $infrastructure = new infra($id_infra, $type, $statut);
                $infrastructure->setIdq($idq); // Définir l'ID du quartier
                $infraC->ajouterInfrastructure($infrastructure);
                $message = "Infrastructure ajoutée avec succès ✅";
            }
        }
    } else {
        $message = "Tous les champs sont requis ❌";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URBAVERSE Admin - Infrastructures</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }
                
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: #4CAF50;
            color: white;
            border: none;
        }
        
        .btn-secondary {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Barre latérale -->
    <div class="sidebar">
        <h2>Urbaverse</h2>
        <ul>
            <li><a href="#">Dashboard</a></li>
            <li><a href="#">Signalements</a></li>
            <li><a href="#">Utilisateurs</a></li>
            <li><a href="#" class="active">Infrastructures</a></li>
            <li><a href="#">Paramètres</a></li>
        </ul>
    </div>

    <!-- Contenu principal -->
    <div class="main">
        <div class="header">
            <h1>Ajouter une Infrastructure</h1>
            <a href="backinfra.php" class="back-btn">← Retour</a>
        </div>

        <section class="infrastructures-section">
            <div class="section-container">
                <?php if ($message): ?>
                    <div class="alert <?= strpos($message, '❌') ? 'alert-error' : 'alert-success' ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" action="" novalidate>
                        <div class="form-group">
                            <label for="id_infra">Identifiant de l'infrastructure</label>
                            <input type="text" id="id_infra" name="id_infra" required>
                        </div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <input type="text" id="type" name="type" required>
                        </div>

                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <input type="text" id="statut" name="statut" required>
                        </div>

                        <div class="form-group">
                            <label for="idq">Quartier</label>
                            <select id="idq" name="idq" required>
                                <option value="">Sélectionnez un quartier</option>
                                <?php foreach ($quartiers as $quartier): ?>
                                    <option value="<?= htmlspecialchars($quartier['idq']) ?>">
                                        <?= htmlspecialchars($quartier['nomq'] . ' - ' . $quartier['ville']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="reset" class="btn btn-secondary">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</body>
</html>