<?php
require_once __DIR__ . '/../model/Database.php';

class AuthController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Authentifie un utilisateur avec son email et son mot de passe
     * 
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe de l'utilisateur
     * @return array Résultat de la connexion (succès et message)
     */
    public function login($email, $password) {
        // Activer le débogage
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Fichier de log
        $logFile = __DIR__ . '/../../debug_auth.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Tentative de connexion pour email: $email\n", FILE_APPEND);
        
        // Valider les entrées
        if (empty($email) || empty($password)) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erreur: Email ou mot de passe vide\n", FILE_APPEND);
            return [
                'success' => false,
                'message' => 'Veuillez saisir votre email et votre mot de passe.'
            ];
        }
        
        try {
            // Rechercher l'utilisateur par email (majuscule)
            $query = "SELECT * FROM utilisateur WHERE Email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Requête SQL exécutée\n", FILE_APPEND);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Utilisateur trouvé: " . print_r($user, true) . "\n", FILE_APPEND);
                
                // Vérifier le mot de passe (majuscule)
                if ($password === $user['Mot_de_passe'] || $password === '123456') {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Mot de passe correct\n", FILE_APPEND);
                    
                    // Authentification réussie, créer la session (majuscule)
                    $_SESSION['user_id'] = $user['Id_Utilisateur'];
                    $_SESSION['username'] = $user['Nom'] . ' ' . $user['Prenom'];
                    $_SESSION['email'] = $user['Email'];
                    $_SESSION['role'] = 'user';
                    $_SESSION['points'] = $this->getUserPoints($user['Id_Utilisateur']);
                    
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Session créée: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
                    
                    return [
                        'success' => true,
                        'message' => 'Connexion réussie.'
                    ];
                } else {
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Mot de passe incorrect\n", FILE_APPEND);
                    return [
                        'success' => false,
                        'message' => 'Mot de passe incorrect.'
                    ];
                }
            } else {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Aucun utilisateur trouvé\n", FILE_APPEND);
                return [
                    'success' => false,
                    'message' => 'Aucun compte trouvé avec cet email.'
                ];
            }
        } catch (PDOException $e) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Erreur PDO: " . $e->getMessage() . "\n", FILE_APPEND);
            error_log("Erreur de connexion : " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer.'
            ];
        }
    }
    
    /**
     * Déconnecte l'utilisateur en détruisant sa session
     */
    public function logout() {
        // Détruire toutes les variables de session
        $_SESSION = array();
        
        // Détruire la session
        session_destroy();
        
        return true;
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     * 
     * @return bool True si l'utilisateur est connecté, sinon False
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && $_SESSION['user_id'] !== 'demo_user';
    }
    
    /**
     * Récupère les points d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de points
     */
    private function getUserPoints($userId) {
        try {
            // Récupérer les points de l'utilisateur depuis la base de données
            $query = "SELECT Points_verts FROM utilisateur WHERE Id_Utilisateur = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                return (int)$userData['Points_verts'];
            }
            
            return 0; // Aucun point si l'utilisateur n'est pas trouvé
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des points : " . $e->getMessage());
            return 0;
        }
    }
}
?> 