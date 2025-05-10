<?php
// Ce fichier sert de pont vers notre adaptateur de configuration
// Il est placé à la racine pour être trouvé par les includes du module signalement

// Inclure directement notre adaptateur qui contient la définition de la classe Config
require_once __DIR__ . '/config/signalement_adapter.php';
?> 