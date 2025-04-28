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
    
    /**
     * Met à jour automatiquement les statuts des défis en fonction de la date actuelle
     */
    public function updateDefiStatuses() {
        $today = date('Y-m-d');
        
        try {
            // Pour tester, affichons la date actuelle
            error_log("Date actuelle: " . $today);
            
            // Mettre à jour les défis "À venir" qui doivent commencer aujourd'hui ou avant
            $query1 = "UPDATE defi 
                      SET Statut_D = 'Actif' 
                      WHERE Statut_D = 'À venir' AND Date_Debut <= :today";
            $stmt1 = $this->db->prepare($query1);
            $stmt1->bindParam(':today', $today);
            $stmt1->execute();
            
            // Mettre à jour les défis "Actif" qui sont terminés
            $query2 = "UPDATE defi 
                      SET Statut_D = 'Terminé' 
                      WHERE Statut_D = 'Actif' AND Date_Fin < :today";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->bindParam(':today', $today);
            $stmt2->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour des statuts de défis: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour les statuts des défis avec une date spécifique (pour tests)
     */
    public function updateDefiStatusesWithDate($testDate) {
        try {
            // Mettre à jour les défis "À venir" qui doivent commencer à la date de test ou avant
            $query1 = "UPDATE defi 
                      SET Statut_D = 'Actif' 
                      WHERE Statut_D = 'À venir' AND Date_Debut <= :testDate";
            $stmt1 = $this->db->prepare($query1);
            $stmt1->bindParam(':testDate', $testDate);
            $stmt1->execute();
            
            // Mettre à jour les défis "Actif" qui sont terminés à la date de test
            $query2 = "UPDATE defi 
                      SET Statut_D = 'Terminé' 
                      WHERE Statut_D = 'Actif' AND Date_Fin < :testDate";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->bindParam(':testDate', $testDate);
            $stmt2->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour des statuts de défis avec date test: " . $e->getMessage());
            return false;
        }
    }
}
?>