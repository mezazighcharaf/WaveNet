<?php
session_start();

// Inclure le contrôleur d'authentification
require_once __DIR__ . '/../../controller/AuthController.php';
$authController = new AuthController();

// Déconnecter l'utilisateur
$authController->logout();

// Rediriger vers la page d'accueil
header('Location: index.php');
exit;
?> 