<?php
require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/Defi.php';

class DefiController {
    private $db;
    private $defi;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->defi = new Defi($this->db);
    }
    
    // Retrieve all defis
    public function getAllDefis() {
        return $this->defi->readAll();
    }
    
    // Retrieve a single defi by ID
    public function getDefi($id) {
        $this->defi->Id_Defi = $id;
        if($this->defi->readOne()) {
            return $this->defi;
        }
        return null;
    }
    
    // Create a new defi
    public function createDefi($data) {
        // Set defi property values
        $this->defi->Titre_D = $data['Titre_D'];
        $this->defi->Description_D = $data['Description_D'];
        $this->defi->Objectif = $data['Objectif'];
        $this->defi->Points_verts = $data['Points_verts'];
        $this->defi->Statut_D = $data['Statut_D'];
        $this->defi->Date_Debut = $data['Date_Debut'];
        $this->defi->Date_Fin = $data['Date_Fin'];
        $this->defi->Difficulte = $data['Difficulte'];
        $this->defi->Id_Quartier = $data['Id_Quartier'];
        
        // Create the defi
        if($this->defi->create()) {
            return true;
        }
        return false;
    }
    
    // Update an existing defi
    public function updateDefi($id, $data) {
        // Set defi ID and property values
        $this->defi->Id_Defi = $id;
        $this->defi->Titre_D = $data['Titre_D'];
        $this->defi->Description_D = $data['Description_D'];
        $this->defi->Objectif = $data['Objectif'];
        $this->defi->Points_verts = $data['Points_verts'];
        $this->defi->Statut_D = $data['Statut_D'];
        $this->defi->Date_Debut = $data['Date_Debut'];
        $this->defi->Date_Fin = $data['Date_Fin'];
        $this->defi->Difficulte = $data['Difficulte'];
        $this->defi->Id_Quartier = $data['Id_Quartier'];
        
        // Update the defi
        if($this->defi->update()) {
            return true;
        }
        return false;
    }
    
    // Delete a defi
    public function deleteDefi($id) {
        $this->defi->Id_Defi = $id;
        if($this->defi->delete()) {
            // Vérifier si la table est vide et réinitialiser l'auto-increment si c'est le cas
            $this->resetAutoIncrementIfEmpty();
            return true;
        }
        return false;
    }
    
    // Réinitialiser l'auto-increment si la table est vide
    private function resetAutoIncrementIfEmpty() {
        $stmt = $this->defi->readAll();
        if($stmt->rowCount() == 0) {
            // La table est vide, réinitialiser l'auto-increment
            try {
                $query = "ALTER TABLE defi AUTO_INCREMENT = 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
            } catch(PDOException $e) {
                // Simplement ignorer l'erreur si la réinitialisation échoue
            }
        }
    }
}
?> 