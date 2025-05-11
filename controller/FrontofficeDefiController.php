<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Defi.php';

class FrontofficeDefiController {
    private $db;
    private $defi;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->defi = new Defi($this->db);
    }
    
    // Retrieve all active defis
    public function getActiveDefis() {
        return $this->defi->readAllActive();
    }
    
    // Retrieve all defis
    public function getAllDefis($order = null) {
        if ($order === 'asc' || $order === 'desc') {
            return $this->getAllDefisSorted($order);
        } else {
            return $this->defi->readAll();
        }
    }
    
    // Retrieve a single defi by ID
    public function getDefi($id) {
        $this->defi->Id_Defi = $id;
        if($this->defi->readOne()) {
            return $this->defi;
        }
        return null;
    }
    
    // Retrieve a single defi by ID and return as array
    public function getDefiById($id) {
        $query = "SELECT * FROM defi WHERE Id_Defi = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Retrieve defis by difficulty
    public function getDefisByDifficulty($difficulty) {
        return $this->defi->readByDifficulty($difficulty);
    }
    
    // Retrieve most popular defis (by participation)
    public function getPopularDefis($limit = 4) {
        return $this->defi->readPopular($limit);
    }
    
    // Add a participation for a user to a defi
    public function participateInDefi($defiId, $userId) {
        try {
            // Vérifier si le défi existe et est actif
            $query = "SELECT * FROM defi WHERE Id_Defi = ? AND Statut_D = 'Actif' AND Date_Debut <= CURRENT_DATE AND Date_Fin >= CURRENT_DATE";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $defiId);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return "Ce défi n'est pas disponible actuellement.";
            }
            
            $defi = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Vérifier si l'utilisateur participe déjà
            $query = "SELECT * FROM participation WHERE Id_Defi = ? AND Id_Utilisateur = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $defiId);
            $stmt->bindParam(2, $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return "Vous participez déjà à ce défi.";
            }
            
            // Ajouter la participation
            $query = "INSERT INTO participation (Id_Defi, Id_Utilisateur, Date_Participation) VALUES (?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $defiId);
            $stmt->bindParam(2, $userId);
            
            if ($stmt->execute()) {
                // Mettre à jour les points de l'utilisateur
                $query = "UPDATE utilisateur SET Points_verts = Points_verts + ? WHERE Id_Utilisateur = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(1, $defi['Points_verts']);
                $stmt->bindParam(2, $userId);
                $stmt->execute();
                
                // Simuler l'ajout dans la session pour la démo
                if (!isset($_SESSION['participations'])) {
                    $_SESSION['participations'] = [];
                }
                
                $_SESSION['participations'][] = [
                    'defi_id' => $defiId,
                    'user_id' => $userId,
                    'date' => date('Y-m-d H:i:s')
                ];
                
                return true;
            } else {
                return "Erreur lors de l'enregistrement de votre participation.";
            }
        } catch (PDOException $e) {
            // Dans un environnement de production, loguer l'erreur et retourner un message générique
            // error_log($e->getMessage());
            return "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
    }

    // Vérifie si un utilisateur a déjà participé à un défi
    public function hasUserParticipated($userId, $defiId) {
        // Cette méthode simule la vérification de participation
        // Dans une application réelle, cette méthode vérifierait dans la base de données
        
        // Si les participations n'existent pas dans la session, l'utilisateur n'a pas participé
        if (!isset($_SESSION['participations'])) {
            return false;
        }
        
        // Parcourir les participations enregistrées dans la session
        foreach ($_SESSION['participations'] as $participation) {
            if ($participation['defi_id'] == $defiId && $participation['user_id'] == $userId) {
                return true;
            }
        }
        
        return false;
    }

    public function getAllDefisSorted($sortDirection = 'asc') {
        if ($sortDirection === 'asc') {
            return $this->defi->readAllOrderByTitleAsc();
        } else {
            return $this->defi->readAllOrderByTitleDesc();
        }
    }

    // Ajoutez cette méthode pour trier par statut
    public function getDefisByStatus($status) {
        return $this->defi->readByStatus($status);
    }

    /**
     * Récupère tous les défis auxquels un utilisateur a participé
     * 
     * @param string|int $userId ID de l'utilisateur
     * @return array Liste des défis avec informations de participation
     */
    public function getUserParticipations($userId) {
        try {
            // Requête pour récupérer les défis avec informations de participation
            $query = "SELECT d.*, p.Date_Participation 
                      FROM participation p
                      JOIN defi d ON p.Id_Defi = d.Id_Defi
                      WHERE p.Id_Utilisateur = ?
                      ORDER BY p.Date_Participation DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $userId);
            $stmt->execute();
            
            $participations = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $participations[] = $row;
            }
            
            return $participations;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des participations : " . $e->getMessage());
            return [];
        }
    }
}
?> 