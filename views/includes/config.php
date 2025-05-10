<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'wavenet');

if (!defined('SITE_NAME')) define('SITE_NAME', 'Urbaverse');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/WaveNet/');

if (!defined('ROOT_PATH')) define('ROOT_PATH', realpath(dirname(__DIR__, 2)));
if (!defined('INCLUDES_PATH')) define('INCLUDES_PATH', ROOT_PATH . '/views/includes');
if (!defined('ASSETS_PATH')) define('ASSETS_PATH', ROOT_PATH . '/views/assets');

// Fonction pour vérifier la validation 2FA
if (!function_exists('check2FAStatus')) {
    function check2FAStatus() {
        // Listes des URLs autorisées même sans vérification 2FA complète
        $allowedUrls = [
            '/WaveNet/controller/UserController.php',  // Pour verifier2FA
            '/WaveNet/views/frontoffice/verifier2FA.php',
            '/WaveNet/views/frontoffice/login.php',
            '/WaveNet/controller/UserController.php?action=logout'
        ];
        
        $currentUrl = $_SERVER['REQUEST_URI'];
        
        // Autoriser les URLs spécifiques même si 2FA est en attente
        foreach ($allowedUrls as $url) {
            if (strpos($currentUrl, $url) === 0) {
                return true;
            }
        }
        
        // Si l'authentification nécessite encore une vérification 2FA
        if (isset($_SESSION['temp_user_id']) && 
            (!isset($_SESSION['user_id']) || isset($_SESSION['auth_requires_2fa']))) {
            
            error_log("[check2FAStatus] Redirection vers verification 2FA depuis: " . $currentUrl);
            header('Location: /WaveNet/controller/UserController.php?action=verifier2FA');
            exit;
        }
        
        return true;
    }
}

// Appliquer la vérification 2FA pour les utilisateurs connectés ou en attente de 2FA
if (isset($_SESSION['user_id']) || isset($_SESSION['temp_user_id'])) {
    check2FAStatus();
}

if (!function_exists('connectDB')) {
    function connectDB() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return $pdo;
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            echo 'Erreur de connexion à la base de données: ' . $e->getMessage();
            die();
        }
    }
}

if (!function_exists('redirect')) {
    function redirect($path) {
        header('Location: ' . $path);
        exit;
    }
}

if (!function_exists('sanitize')) {
    function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// Gestion des messages flash
if (!function_exists('setMessage')) {
    function setMessage($type, $message) {
        $_SESSION['message'] = [
            'type' => $type, // 'success', 'error', 'warning', 'info'
            'text' => $message
        ];
    }
}

if (!function_exists('displayMessage')) {
    function displayMessage() {
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            $type = $message['type'];
            $text = $message['text'];
            echo "<div class='alert alert-{$type}'>{$text}</div>";
            unset($_SESSION['message']);
        }
    }
}