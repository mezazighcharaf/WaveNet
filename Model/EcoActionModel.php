<?php
require_once '../config/database.php'; // Ensure your database connection is available

class EcoActionModel {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();  // Correct method name
        // Créer une instance de connexion à la base de données
    }

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
        // Remplacer $this->conn par $this->db
        $stmt = $this->db->prepare("INSERT INTO participant (id_utilisateur, id_action) VALUES (?, ?)");
        return $stmt->execute([$id_utilisateur, $id_action]);
    }

    // Annuler la participation d'un utilisateur
    public function annulerParticipation($id_action, $id_utilisateur) {
        // Remplacer $this->conn par $this->db
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
     
    
    
    
// EcoActionModel.php
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



} 

?>
