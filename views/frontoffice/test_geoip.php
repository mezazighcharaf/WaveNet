<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Modifier ce code pour éviter la modification des fichiers Composer
try {
    require_once '../../vendor/autoload.php';
    require_once '../../models/security_functions.php';
    
    echo "<h2>GeoIP Test</h2>";
    
    // Vérifier si le fichier de la base de données existe
    $dbPath = __DIR__ . '/../../data/GeoLite2-City.mmdb';
    if (!file_exists($dbPath)) {
        echo "<p style='color:red'>ERREUR: Fichier de base de données GeoLite2 introuvable à l'emplacement: $dbPath</p>";
        echo "<p>Veuillez télécharger le fichier GeoLite2-City.mmdb depuis MaxMind et le placer dans le dossier 'data'.</p>";
        exit();
    } else {
        echo "<p style='color:green'>Base de données GeoLite2 trouvée!</p>";
    }
    
    // IPs à tester
    $ips = array(
        '8.8.8.8', // Google DNS
        '208.67.222.222', // OpenDNS
        '1.1.1.1', // Cloudflare
        '127.0.0.1' // Localhost
    );
    
    echo "<h3>Résultats des tests:</h3>";
    
    foreach ($ips as $ip) {
        echo "<hr>";
        echo "<h4>Test pour l'IP: $ip</h4>";
        
        try {
            $geoInfo = getGeoInfo($ip);
            
            echo "<ul>";
            echo "<li><strong>Pays</strong>: " . htmlspecialchars($geoInfo['country']) . "</li>";
            echo "<li><strong>Ville</strong>: " . htmlspecialchars($geoInfo['city']) . "</li>";
            echo "<li><strong>Latitude</strong>: " . htmlspecialchars($geoInfo['latitude']) . "</li>";
            echo "<li><strong>Longitude</strong>: " . htmlspecialchars($geoInfo['longitude']) . "</li>";
            echo "</ul>";
        } catch (Exception $e) {
            echo "<p style='color:red'>Erreur lors du test de l'IP $ip: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Test avec l'IP actuelle du client</h3>";
    
    $clientIP = $_SERVER['REMOTE_ADDR'];
    echo "<p>Votre IP actuelle: <strong>" . htmlspecialchars($clientIP) . "</strong></p>";
    
    try {
        $geoInfo = getGeoInfo($clientIP);
        
        echo "<ul>";
        echo "<li><strong>Pays</strong>: " . htmlspecialchars($geoInfo['country']) . "</li>";
        echo "<li><strong>Ville</strong>: " . htmlspecialchars($geoInfo['city']) . "</li>";
        echo "<li><strong>Latitude</strong>: " . htmlspecialchars($geoInfo['latitude']) . "</li>";
        echo "<li><strong>Longitude</strong>: " . htmlspecialchars($geoInfo['longitude']) . "</li>";
        echo "</ul>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Erreur lors du test de votre IP: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>ERREUR GÉNÉRALE: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Trace: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></p>";
}
