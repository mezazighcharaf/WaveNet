<?php
include_once(__DIR__ . '/../../Controller/quartierC.php');

$message = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idq = $_POST['idq'];
    $nomq = $_POST['nomq'];
    $ville = $_POST['ville'];
    $scoreeco = $_POST['scoreeco'];
    $classement = $_POST['classement'];

    // Vérifications côté serveur (pas HTML5)
    if (
        !empty($idq) && !empty($nomq) && !empty($ville) &&
        !empty($scoreeco) && !empty($classement) 
    ) {
        // Contrôle idq : 8 chiffres
// Contrôle idq : 8 chiffres
if (!preg_match('/^\d{8}$/', $idq)) {
    $message = "L'idqntifiant doit contenir exactement 8 chiffres ❌";
}

// Contrôle nomq : uniquement lettres (espaces autorisés)
elseif (!preg_match('/^[\p{L} ]+$/u', $nomq)) {
    $message = "Le nomq doit contenir uniquement des lettres ❌";
}
elseif (!preg_match('/^[\p{L} ]+$/u', $ville)) {
    $message = "La ville doit contenir uniquement des lettres ❌";
}
// Contrôle scoreeco : uniquement lettres (espaces autorisés)
elseif (!is_numeric($scoreeco)) {
    $message = "scoreeco doit être un nombre ❌";
}

elseif (!is_numeric($classement)) {
    $message = "classement doit être un nombre ❌";
}

else {
    $quartierC = new quartierC();

    $existant = $quartierC->recupererQuartierparId($idq);
    if ($existant !== false) {
        $message = "L'idqntifiant existe déjà, merci d'en choisir un autre ❌";
    } else {
        
        $quartier = new quartier($idq, $nomq, $ville, $scoreeco, $classement);
        $quartierC->ajouterQuartier($quartier);
        $message = "quartier ajouté avec succès ✅";
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
    <title>URBAVERSE Admin - Quartiers</title>
    <link rel="stylesheet" href="index.css">
    <style>
        /* ===================== */
        /* VARIABLES & RESET     */
        /* ===================== */
        :root {
            --dark-green: #2e4f3e;
            --light-green: #ecf7ed;
            --accent-green: #4caf50;
            --text-color: #333;
            --white: #fff;
            --max-width: 1200px;
            --border-radius: 12px;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: "Inter", sans-serif;
            background-color: var(--light-green);
            color: var(--text-color);
            line-height: 1.6;
            padding-top: 80px;
        }

        /* ===================== */
        /* FORMULAIRE            */
        /* ===================== */
        .form-section {
            padding: 2rem 1rem;
        }

        .section-container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 1rem;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--accent-green);
            margin-bottom: 1.5rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background-color: var(--accent-green);
            margin-top: 0.5rem;
        }

        .form-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-green);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-family: inherit;
            font-size: 1rem;
            transition: border-color var(--transition-speed);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent-green);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        /* ===================== */
        /* BOUTONS               */
        /* ===================== */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            transition: all var(--transition-speed);
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--accent-green);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: #43a047;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--white);
            color: var(--accent-green);
            border: 1px solid var(--accent-green);
        }

        .btn-secondary:hover {
            background-color: var(--accent-green);
            color: var(--white);
        }

        /* ===================== */
        /* MESSAGES              */
        /* ===================== */
        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* ===================== */
        /* HEADER & FOOTER       */
        /* ===================== */
        /* (Utiliser les mêmes styles que dans le CSS principal) */
    </style>
</head>
<body class="backoffice">
    <header class="main-header">
        <div class="header-container">
            <h1 class="logo">URBAVERSE <span>Admin</span></h1>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="#">Tableau de bord</a></li>
                    <li><a href="#" class="active">Quartiers</a></li>
                    <li><a href="#">Infrastructures</a></li>
                    <li><a href="#">Statistiques</a></li>
                    <li><a href="#" class="btn-logout">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <section class="form-section">
            <div class="section-container">
                <h1 class="section-title">Ajouter un Quartier</h1>
                
                <?php if ($message): ?>
                    <div class="alert <?= strpos($message, '❌') ? 'alert-error' : 'alert-success' ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="idq">Identifiant du quartier (8 chiffres)</label>
                            <input type="text" id="idq" name="idq" required pattern="\d{8}" title="8 chiffres requis">
                        </div>

                        <div class="form-group">
                            <label for="nomq">Nom du quartier</label>
                            <input type="text" id="nomq" name="nomq" required>
                        </div>

                        <div class="form-group">
                            <label for="ville">Ville</label>
                            <input type="text" id="ville" name="ville" required>
                        </div>

                        <div class="form-group">
                            <label for="scoreeco">Score écologique (0-100)</label>
                            <input type="number" id="scoreeco" name="scoreeco" min="0" max="100" required>
                        </div>

                        <div class="form-group">
                            <label for="classement">Classement</label>
                            <input type="number" id="classement" name="classement" min="1" required>
                        </div>

                        <div class="form-actions">
                            <a href="index.php" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>URBAVERSE</h3>
                <p>Gestion des quartiers durables</p>
            </div>
            <div class="footer-section">
                <h3>LIENS RAPIDES</h3>
                <ul>
                    <li><a href="#">Tableau de bord</a></li>
                    <li><a href="#">Quartiers</a></li>
                    <li><a href="#">Infrastructures</a></li>
                </ul>
            </div>
            <div class="copyright">
                <p>© 2025 URBAVERSE. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>