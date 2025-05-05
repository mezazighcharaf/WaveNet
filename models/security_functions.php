<?php
// Fichier: /WaveNet/models/security_functions.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../views/includes/config.php';

use GeoIp2\Database\Reader;

// Fonction pour obtenir les informations de géolocalisation à partir d'une IP
function getGeoInfo($ip) {
    try {
        $databasePath = __DIR__ . '/../data/GeoLite2-City.mmdb'; // Ajustez le chemin selon votre structure
        
        // Vérifier que le fichier de base de données existe
        if (!file_exists($databasePath)) {
            error_log("Database GeoIP not found at: " . $databasePath);
            return [
                'country' => 'Unknown',
                'city' => 'Unknown',
                'latitude' => 0,
                'longitude' => 0
            ];
        }
        
        $reader = new Reader($databasePath);
        try {
            $record = $reader->city($ip);
            
            $country = isset($record->country->names['fr']) ? $record->country->names['fr'] : 
                      (isset($record->country->names['en']) ? $record->country->names['en'] : 'Unknown');
            
            $city = isset($record->city->names['fr']) ? $record->city->names['fr'] : 
                  (isset($record->city->names['en']) ? $record->city->names['en'] : 'Unknown');
            
            $latitude = isset($record->location->latitude) ? $record->location->latitude : 0;
            $longitude = isset($record->location->longitude) ? $record->location->longitude : 0;
            
            return [
                'country' => $country,
                'city' => $city,
                'latitude' => $latitude,
                'longitude' => $longitude
            ];
        } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
            error_log("IP not found in database: " . $e->getMessage());
            return [
                'country' => 'Unknown',
                'city' => 'Unknown',
                'latitude' => 0,
                'longitude' => 0
            ];
        }
    } catch (Exception $e) {
        error_log("Error getting geo info: " . $e->getMessage());
        return [
            'country' => 'Unknown',
            'city' => 'Unknown',
            'latitude' => 0,
            'longitude' => 0
        ];
    }
}

// Journal des connexions
function logConnection($userId, $success, $failureReason = null) {
    $db = connectDB();
    
    // Utiliser l'IP du client
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Récupérer les infos de l'agent utilisateur
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Récupérer les informations géographiques
    $geoInfo = getGeoInfo($ip);
    
    try {
        $stmt = $db->prepare("INSERT INTO connexion_logs 
                             (id_utilisateur, date_connexion, ip_address, user_agent, country, city, latitude, longitude, success, failure_reason) 
                             VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $userId, 
            $ip, 
            $userAgent, 
            $geoInfo['country'], 
            $geoInfo['city'],
            $geoInfo['latitude'],
            $geoInfo['longitude'],
            $success ? 1 : 0, 
            $failureReason
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error logging connection: " . $e->getMessage());
        return false;
    }
}

// Vérifier si une connexion est inhabituelle
function checkUnusualLogin($userId, $ip) {
    $db = connectDB();
    
    // Récupérer les informations géographiques
    $geoInfo = getGeoInfo($ip);
    $country = $geoInfo['country'];
    
    try {
        // Vérifier si l'utilisateur s'est déjà connecté depuis ce pays ou cette IP
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM connexion_logs 
                             WHERE id_utilisateur = ? AND (ip_address = ? OR country = ?) 
                             AND success = 1 AND date_connexion > DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute([$userId, $ip, $country]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si aucune connexion récente depuis cette IP/pays, considérer comme inhabituel
        return ($result['count'] == 0);
    } catch (PDOException $e) {
        error_log("Error checking unusual login: " . $e->getMessage());
        return false; // En cas d'erreur, ne pas alarmer l'utilisateur
    }
}

// Fonction principale pour obtenir les suggestions de sécurité
function getSecuritySuggestions($userId) {
    $suggestions = [];
    $db = connectDB();
    
    try {
        // Récupérer les données de l'utilisateur
        $stmt = $db->prepare("SELECT u.*, 
            (SELECT MAX(date_changement) FROM password_history WHERE id_utilisateur = u.id_utilisateur) AS last_password_change,
            (SELECT COUNT(*) FROM connexion_logs WHERE id_utilisateur = u.id_utilisateur AND success = 0 AND date_connexion > DATE_SUB(NOW(), INTERVAL 7 DAY)) AS failed_attempts
            FROM UTILISATEUR u WHERE u.id_utilisateur = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) return $suggestions;
        
        // Vérifier A2F
        if (!isset($user['twofa_enabled']) || $user['twofa_enabled'] != 1) {
            $suggestions[] = [
                'type' => 'warning',
                'icon' => 'fa-shield-alt',
                'title' => 'Authentification à deux facteurs',
                'message' => 'Renforcez la sécurité de votre compte en activant l\'authentification à deux facteurs.',
                'action' => '/WaveNet/controller/UserController.php?action=gerer2FA',
                'action_text' => 'Activer l\'A2F'
            ];
        }
        
        // Vérifier l'âge du mot de passe
        if (!empty($user['last_password_change'])) {
            $passwordDate = new DateTime($user['last_password_change']);
            $now = new DateTime();
            $interval = $passwordDate->diff($now);
            
            if ($interval->days > 180) { // Plus de 6 mois
                $suggestions[] = [
                    'type' => 'warning',
                    'icon' => 'fa-key',
                    'title' => 'Mot de passe ancien',
                    'message' => 'Votre mot de passe date de plus de 6 mois. Il est recommandé de le changer régulièrement.',
                    'action' => '/WaveNet/views/frontoffice/editProfile.php',
                    'action_text' => 'Changer de mot de passe'
                ];
            }
        }
        
        // Vérifier les tentatives échouées
        if ($user['failed_attempts'] >= 3) {
            $suggestions[] = [
                'type' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'title' => 'Tentatives de connexion suspectes',
                'message' => 'Plusieurs tentatives de connexion ont échoué récemment. Si ce n\'était pas vous, changez votre mot de passe immédiatement.',
                'action' => '/WaveNet/views/frontoffice/editProfile.php',
                'action_text' => 'Changer mon mot de passe'
            ];
        }
        
        // Vérifier les connexions inhabituelles
        $unusualLogin = checkUnusualLogin($userId, $_SERVER['REMOTE_ADDR']);
        if ($unusualLogin) {
            $suggestions[] = [
                'type' => 'info',
                'icon' => 'fa-map-marker-alt',
                'title' => 'Nouvelle zone de connexion',
                'message' => 'Vous vous êtes connecté depuis un nouvel emplacement. Si ce n\'était pas vous, sécurisez votre compte.',
                'action' => '/WaveNet/views/frontoffice/account_activity.php',
                'action_text' => 'Voir l\'activité du compte'
            ];
        }
        
        return $suggestions;
    } catch (Exception $e) {
        error_log("Error getting security suggestions: " . $e->getMessage());
        return []; // En cas d'erreur, retourner un tableau vide
    }
}
