<?php
include_once "../../Controller/infraC.php";
include_once "../../Model/infra.php";

$infraC = new infraC(); // Changé de $infrastructureC à $infraC pour correspondre à la classe
$e = null;
$message = null;

if (isset($_GET['id'])) {
    $e = $infraC->recupererInfrastructureParId($_GET['id']);
}

if (
    isset($_POST['id_infra']) &&
    isset($_POST['type']) &&
    isset($_POST['statut']) 
) {
    $id_infra = $_POST['id_infra'];
    $type = $_POST['type'];
    $statut = $_POST['statut'];

    if (!preg_match('/^\d+$/', $id_infra)) {
        $message = "L'identifiant doit contenir uniquement des chiffres ❌";
    }
    
    elseif (!preg_match('/^[\p{L} ]+$/u', $type)) {
        $message = "Le type doit contenir uniquement des lettres ❌";
    }
    
    elseif (!preg_match('/^[\p{L} ]+$/u', $statut)) {
        $message = "Le statut doit contenir uniquement des lettres ❌";
    } else {
        $infrastructure = new infra($id_infra, $type, $statut); // Changé de 'infrastructure' à 'infra'
        $infraC->modifierInfrastructure($infrastructure, $_GET['id']);
        header('Location: backinfra.php');
        exit();
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

    <main class="main-content">
        <section class="form-section">
            <div class="section-container">
                <h1 class="section-title">Modifier Infrastructure</h1>
                
                <?php if ($message): ?>
                    <div class="alert <?= strpos($message, '❌') ? 'alert-error' : 'alert-success' ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="id_infra">ID Infrastructure</label>
                            <input type="text" name="id_infra" id="id_infra" class="form-control" value="<?= $e['id_infra'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="type">Type</label>
                            <input type="text" name="type" id="type" class="form-control" value="<?= $e['type'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="statut">Statut</label>
                            <input type="text" name="statut" id="statut" class="form-control" value="<?= $e['statut'] ?>" required>
                        </div>

                        <div class="form-actions">
                            <a href="backinfra.php" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
</body>
</html>