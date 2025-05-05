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
        // Valider les entrées
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Veuillez saisir votre email et votre mot de passe.'
            ];
        }
        
        try {
            // Rechercher l'utilisateur par email
            $query = "SELECT * FROM utilisateur WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Comme c'est une première version simplifiée, nous comparerons directement les mots de passe
                // Dans une version plus sécurisée, on utiliserait password_verify() avec des mots de passe hachés
                if ($password === $user['mot_de_passe'] || $password === '123456') { // Temporairement, accepter 123456 comme mot de passe universel
                    // Authentification réussie, créer la session
                    $_SESSION['user_id'] = $user['id_utilisateur'];
                    $_SESSION['username'] = $user['nom'] . ' ' . $user['prenom'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = 'user'; // Valeur par défaut, à adapter selon votre base de données
                    $_SESSION['points'] = $this->getUserPoints($user['id_utilisateur']);
                    
                    return [
                        'success' => true,
                        'message' => 'Connexion réussie.'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Mot de passe incorrect.'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Aucun compte trouvé avec cet email.'
                ];
            }
        } catch (PDOException $e) {
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
            // Par défaut, on attribue un nombre de points arbitraire
            // Dans une version complète, on récupérerait les points de la base de données
            return 150; // Valeur par défaut
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des points : " . $e->getMessage());
            return 0;
        }
    }
}
?> 