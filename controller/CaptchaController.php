<?php

if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    CaptchaController::route();
}

class CaptchaController {
    /**
     * Valide le CAPTCHA.
     */
    public function validate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['captcha_attempts'])) {
            $_SESSION['captcha_attempts'] = 0;
        }

        if ($_SESSION['captcha_attempts'] >= 3) {
            $_SESSION['error'] = "Vous avez dépassé le nombre maximum de tentatives. Veuillez réessayer plus tard.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }

        $captchaScore = isset($_POST['captcha_score']) ? floatval($_POST['captcha_score']) : 0;
        $captchaElements = isset($_POST['captcha_elements']) ? json_decode($_POST['captcha_elements'], true) : [];
        $captchaToken = isset($_POST['captcha_token']) ? $_POST['captcha_token'] : '';

        if (!isset($_SESSION['captcha_token']) || $_SESSION['captcha_token'] !== $captchaToken) {
            $_SESSION['captcha_attempts']++;
            $_SESSION['error'] = "Token CAPTCHA invalide. Veuillez réessayer.";
            header('Location: /WaveNet/views/frontoffice/login.php');
            exit;
        }

        $totalCount = is_array($captchaElements) ? count($captchaElements) : 0;
        
        error_log("[CaptchaController] Validation - Reçu: totalCount={$totalCount}, captchaScore=".var_export($captchaScore, true));
        
        $isValid = ($totalCount >= 3 && $captchaScore >= (float)50);

        if (!$isValid) {
            $_SESSION['captcha_attempts']++;
            if ($totalCount < 3) {
                 $_SESSION['error'] = "Vérification échouée. Veuillez placer au moins 3 éléments. 🧩";
            } elseif ($captchaScore < 50.0) {
                 $_SESSION['error'] = "Vérification échouée. Le score écologique doit être d'au moins 50. 🌱";
            } else {
                 $_SESSION['error'] = "Vérification échouée. Veuillez réessayer. 🤔"; 
            }
            header('Location: /WaveNet/views/frontoffice/login.php?keep_attempts=1'); 
            exit;
        }

        $_SESSION['captcha_validated'] = true;
        unset($_SESSION['captcha_token']);
        
        // Le commentaire important sur la responsabilité de cette fonction est conservé
        // **Important** : Le reste du code de cette fonction (récupération user ID, connexion, etc.)
        // devrait idéalement être déplacé dans UserController::finalizeLoginAfterCaptcha.
        // Cette fonction 'validate' ne devrait QUE valider le captcha et mettre un flag en session.
        
        $_SESSION['success'] = 'Vérification réussie ! Vous pouvez maintenant vous connecter.'; 
        header('Location: /WaveNet/views/frontoffice/login.php?keep_attempts=1&captcha_success=1');
        exit;
    }

    /**
     * Vérifie si le token CAPTCHA est valide pour l'utilisateur en cours de connexion.
     */
    public function checkToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tmp_user_id'])) {
            return false;
        }

        if (!isset($_SESSION['validated_captcha_token'])) {
            return false;
        }

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();

            $tableExists = $this->checkCaptchaTokensTable($db);
            if (!$tableExists) {
                return false;
            }

            $stmt = $db->prepare("SELECT id_token FROM CAPTCHA_TOKENS WHERE token = ? AND id_utilisateur = ? AND utilisé = 0");
            $stmt->execute([$_SESSION['validated_captcha_token'], $_SESSION['tmp_user_id']]);
            
            return $stmt->fetch() !== false;

        } catch (Exception $e) {
            error_log("Erreur lors de la vérification du token CAPTCHA: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Marque un token comme utilisé après connexion réussie.
     */
    public function useToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tmp_user_id']) || !isset($_SESSION['validated_captcha_token'])) {
            return false;
        }

        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();

            $stmt = $db->prepare("UPDATE CAPTCHA_TOKENS SET utilisé = 1 WHERE token = ? AND id_utilisateur = ?");
            $result = $stmt->execute([$_SESSION['validated_captcha_token'], $_SESSION['tmp_user_id']]);

            unset($_SESSION['validated_captcha_token']);
            unset($_SESSION['tmp_user_id']);

            return $result;

        } catch (Exception $e) {
            error_log("Erreur lors de l'utilisation du token CAPTCHA: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si la table CAPTCHA_TOKENS existe.
     */
    private function checkCaptchaTokensTable($db) {
        $stmt = $db->query("SHOW TABLES LIKE 'CAPTCHA_TOKENS'");
        return $stmt->rowCount() > 0;
    }

    /**
     * Nettoie les anciens tokens CAPTCHA non utilisés.
     */
    public function cleanupOldTokens() {
        try {
            require_once __DIR__ . '/../views/includes/config.php';
            $db = connectDB();

            if (!$this->checkCaptchaTokensTable($db)) {
                return;
            }

            $stmt = $db->prepare("DELETE FROM CAPTCHA_TOKENS WHERE utilisé = 0 AND date_creation < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stmt->execute();

        } catch (Exception $e) {
            error_log("Erreur lors du nettoyage des anciens tokens CAPTCHA: " . $e->getMessage());
        }
    }

    /**
     * Génère et renvoie le fragment HTML pour le CAPTCHA interactif.
     */
    public function getEcoCaptchaHTML() {
        header('Content-Type: text/html; charset=utf-8');

        // Vérifier si le mode autonome est demandé
        $standalone = isset($_GET['standalone']) && $_GET['standalone'] == '1';

        if ($standalone) {
            // Générer une page complète et isolée pour le CAPTCHA
            ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de sécurité</title>
    <!-- Inclure FontAwesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Style isolé pour éviter tout conflit -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        #captcha-wrapper {
            max-width: 380px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-top: 0;
        }
        p {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }
        /* Assurez-vous que ces styles sont compatibles avec eco-captcha.php */
        .element {
            width: 48px;
            height: 48px;
            background-color: white;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            position: relative;
            padding: 0;
            margin: 0 auto;
        }
        .element i {
            font-size: 1.5rem;
            margin: 0;
        }
        .element:hover {
            transform: scale(1.08);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .element.durable {
            border-color: #81c784;
            background-color: #e8f5e9;
        }
        .element.durable i { color: #4caf50; }
        .element.non-durable {
            border-color: #e57373;
            background-color: #ffebee;
        }
        .element.non-durable i { color: #f44336; }
        
        .elements-list {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            justify-content: center;
            margin-top: 15px;
        }
        
        #city-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background-color: #f8f8f8;
            border-radius: 8px;
            margin: 0 auto 15px;
            min-height: 60px;
            width: 100%;
            max-width: 350px;
            border: 2px dashed #ccc;
        }
    </style>
</head>
<body>
    <div id="captcha-wrapper">
        <h2>Vérification de sécurité</h2>
        <p>Complétez ce CAPTCHA pour continuer</p>
        
        <?php require_once __DIR__ . '/../views/includes/eco-captcha.php'; ?>
    </div>
    
    <script>
    // Script pour communiquer avec la fenêtre parente
    function sendSuccessToParent(data) {
        if (window.opener && !window.opener.closed && window.opener.handleExternalCaptchaSuccess) {
            window.opener.handleExternalCaptchaSuccess(data);
        } else {
            alert("Vérification réussie! Vous pouvez fermer cette fenêtre et continuer sur la page principale.");
        }
    }
    
    // Remplacer la fonction de succès standard
    window.handleEcoCaptchaSuccess = function(data) {
        console.log("CAPTCHA validé avec succès!");
        // Envoyer à la fenêtre parente
        sendSuccessToParent(data);
    };
    </script>
</body>
</html>
            <?php
            exit;
        }
        
        // Mode normal : inclure seulement le fragment HTML
        require_once __DIR__ . '/../views/includes/eco-captcha.php';
        exit;
    }

    /**
     * Charge le contenu du captcha et le renvoie.
     * Cette méthode est appelée via AJAX.
     */
    public function load() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Générer un token aléatoire pour le captcha
        $_SESSION['captcha_token'] = bin2hex(random_bytes(16));
        
        // Renvoyer le contenu HTML du captcha
        $this->getEcoCaptchaHTML();
    }

    /**
     * Router pour les actions du CaptchaController.
     */
    public static function route() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $controller = new CaptchaController();
        $action = isset($_GET['action']) ? $_GET['action'] : 'default';
        
        switch ($action) {
            case 'getEcoCaptchaHTML':
                $controller->getEcoCaptchaHTML();
                break;
            case 'load':
                $controller->load();
                break;
            case 'validate':
                $controller->validate();
                break;
            case 'checkToken':
                echo json_encode(['valid' => $controller->checkToken()]);
                break;
            case 'useToken':
                echo json_encode(['success' => $controller->useToken()]);
                break;
            case 'cleanupOldTokens':
                $controller->cleanupOldTokens();
                echo json_encode(['success' => true]);
                break;
            default:
                // Log l'action inconnue au lieu de rediriger silencieusement
                error_log("[CaptchaController] Action inconnue reçue: " . $action);
                header("HTTP/1.1 404 Not Found");
                echo json_encode(['success' => false, 'message' => 'Action Captcha inconnue.']);
                exit;
        }
    }
}