<?php
require_once __DIR__ . '/../views/includes/config.php';

class EcoActionModel {
    private $db;

    public function __construct() {
        $this->db = connectDB(); // Utilisation de connectDB() comme dans RecompenseController.php
    }

    // ===== Methods from original EcoActionModel =====
    
    public function getAllEcoActions() {
        $sql = "SELECT * FROM eco_action";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    
        $actions = [];
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $actions[] = $row;
        }
    
        return $actions;
    }
    
    public function ajouterParticipant($id_action, $id_utilisateur) {
        $stmt = $this->db->prepare("INSERT INTO participant (id_utilisateur, id_action) VALUES (?, ?)");
        return $stmt->execute([$id_utilisateur, $id_action]);
    }

    public function annulerParticipation($id_action, $id_utilisateur) {
        $stmt = $this->db->prepare("DELETE FROM participant WHERE id_utilisateur = ? AND id_action = ?");
        return $stmt->execute([$id_utilisateur, $id_action]);
    }
    
    public function searchActionByName($search) {
        $sql = "SELECT * FROM eco_action WHERE nom_action LIKE :search";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getActionsByEtat($etat) {
        // Vérification si l'état est valide
        $validStates = ['encours', 'termine', 'annulé'];
        if (!in_array($etat, $validStates)) {
            $etat = 'encours'; // Valeur par défaut si l'état n'est pas valide
        }

        $sql = "SELECT * FROM eco_action WHERE etat = :etat ORDER BY date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':etat', $etat, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== Methods from EcoActionBackModel =====
    
    public function attribuerPointsParCategorie($categorie) {
        $categorie = strtolower(trim($categorie)); // Nettoyage : trim + minuscules
    
        switch ($categorie) {
            case 'recyclage':
                return 10;
            case 'energie':
                return 20;
            case 'environement':
                return 15;
            case 'biodiversité':
                return 12;
            default:
                return 5; // Valeur par défaut
        }
    }
    
    // CREATE
    public function addEcoAction($nom, $description, $date, $statut, $points_verts, $categorie) {
        $point_vert = $this->attribuerPointsParCategorie($categorie); // Attribution automatique
        $stmt = $this->db->prepare("INSERT INTO eco_action (nom_action, description_action, date, etat, point_vert, categorie)
                                  VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nom, $description, $date, $statut, $point_vert, $categorie]);
    }

    // READ ONE
    public function getEcoActionById($id_action) {
        $stmt = $this->db->prepare("SELECT * FROM eco_action WHERE id_action = ?");
        $stmt->execute([$id_action]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE
    public function updateEcoAction($id_action, $nom, $description, $date, $statut, $points_verts, $categorie) {
        $stmt = $this->db->prepare("UPDATE eco_action 
                                  SET nom_action = ?, description_action = ?, date = ?, etat = ?, point_vert = ?, categorie = ?
                                  WHERE id_action = ?");
        return $stmt->execute([$nom, $description, $date, $statut, $points_verts, $categorie, $id_action]);
    }

    // DELETE
    public function deleteEcoAction($id_action) {
        $stmt = $this->db->prepare("DELETE FROM eco_action WHERE id_action = ?");
        return $stmt->execute([$id_action]);
    }
}
?>