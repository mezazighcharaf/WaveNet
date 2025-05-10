<?php
// Adaptateur de configuration pour le module signalement
// Ce fichier résout le conflit entre les classes Config

// Inclure la configuration principale de l'application
require_once __DIR__ . '/../views/includes/config.php';

// Définir la classe Config si elle n'existe pas déjà
// Cette classe sera utilisée par le module signalement
if (!class_exists('Config')) {
    class Config {
        public static function getConnection() {
            // Utiliser la fonction connectDB() définie dans views/includes/config.php
            return connectDB();
        }
    }
}
?> 