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
} // 👈 PAS de point-virgule ici !

?>
