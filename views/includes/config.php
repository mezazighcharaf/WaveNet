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
if (!function_exists('setMessage')) {
    function setMessage($type, $message) {
        $_SESSION['message'] = [
            'type' => $type, 
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
