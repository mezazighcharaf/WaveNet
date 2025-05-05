<?php
session_start();

// Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== 'demo_user') {
    header('Location: index.php');
    exit;
}

// Vérifier si le formulaire a été soumis
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inclure le contrôleur d'authentification
    require_once __DIR__ . '/../../controller/AuthController.php';
    $authController = new AuthController();
    
    // Récupérer les données du formulaire
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Tenter la connexion
    $result = $authController->login($email, $password);
    
    if ($result['success']) {
        // Connexion réussie, rediriger vers la page d'accueil
        header('Location: index.php');
        exit;
    } else {
        // Afficher le message d'erreur
        $error_message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Urbaverse</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #2c6e49;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        
        .logo-container {
            text-align: center;
            padding: 20px 0;
        }
        
        .logo-container img {
            max-width: 300px;
        }
        
        .links {
            margin: 20px 0;
            text-align: center;
        }
        
        .links a {
            color: #fff;
            margin: 0 10px;
            font-size: 18px;
            text-decoration: none;
        }
        
        .login-form {
            max-width: 500px;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .error-message {
            color: #f44336;
            margin-bottom: 15px;
        }
        
        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="../../assets/img/logo.jpg" alt="Urbaverse Logo">
        <h1 style="color: white;">Urbaverse</h1>
    </div>
    
    <div class="links">
        <a href="../../index.php">Accueil</a>
        <a href="defis.php">Défis</a>
        <a href="../backoffice/dashboard/index.php">Backoffice</a>
    </div>
    
    <div class="login-form">
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="submit-btn">Se connecter</button>
        </form>
    </div>
</body>
</html> 