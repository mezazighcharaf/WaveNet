<?php
// ParticipantModel.php - Combined front and back participant models

require_once(__DIR__ . '/../views/includes/config.php');

class ParticipantModel {
    private $db;

    public function __construct() {
        $this->db = connectDB(); // Utilisation de connectDB() comme dans RecompenseController.php
    }

    // ===== Methods from original ParticipantModel =====
    
    // Get all participants
    public function getAllParticipants() {
        $query = "SELECT * FROM participant";  // Query to get all participants
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);  // Return results as an associative array
    }

    // Get a participant by ID
    public function getParticipantById($id_participant) {
        $query = "SELECT * FROM participant WHERE id_participant = :id_participant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_participant', $id_participant, PDO::PARAM_INT);  // Bind ID parameter
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);  // Return the participant's data if found, or false if not
    }

    // Add a new participant (from frontoffice)
    public function addParticipant($nom_participant, $email_participant, $id_action) {
        $query = "INSERT INTO participant (nom_participant, email_participant, id_action) VALUES (:nom_participant, :email_participant, :id_action)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_participant', $nom_participant);
        $stmt->bindParam(':email_participant', $email_participant);
        $stmt->bindParam(':id_action', $id_action);
        return $stmt->execute();  // Execute the query and return if it was successful
    }

    public function getParticipantbynameandemail($name, $email) {
        $query = "SELECT id_participant FROM participant WHERE email_participant = :email AND nom_participant = :nom";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':nom', $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function cancelParticipation($id_participant) {
        $query = "DELETE FROM participant WHERE id_participant = :id_participant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_participant', $id_participant, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    // ===== Methods from ParticipantBackModel =====
    
    // Add a new participant from backoffice (with niveau and action lookup)
    public function addParticipantBack($nom_participant, $email_participant, $nom_action, $niveau) {
        $query = "SELECT * FROM eco_action WHERE nom_action = :nom_action";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_action', $nom_action);
        $stmt->execute();
        $id_action = $stmt->fetch(PDO::FETCH_ASSOC)['id_action'];
        
        $query = "INSERT INTO participant (nom_participant, email_participant, id_action, niveau) VALUES (:nom_participant, :email_participant, :id_action, :niveau)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_participant', $nom_participant);
        $stmt->bindParam(':email_participant', $email_participant);
        $stmt->bindParam(':id_action', $id_action, PDO::PARAM_INT);
        $stmt->bindParam(':niveau', $niveau);
        return $stmt->execute();
    }

    // Update a participant
    public function updateParticipant($id_participant, $nom_participant, $email_participant, $nom_action, $niveau) {
        $query = "SELECT id_action FROM eco_action WHERE nom_action = :nom_action";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_action', $nom_action);
        $stmt->execute();
        $id_action = $stmt->fetch(PDO::FETCH_ASSOC)['id_action'];
        
        $query = "UPDATE participant SET nom_participant = :nom_participant, email_participant = :email_participant, id_action = :id_action, niveau = :niveau WHERE id_participant = :id_participant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_participant', $id_participant, PDO::PARAM_INT);
        $stmt->bindParam(':id_action', $id_action, PDO::PARAM_INT);
        $stmt->bindParam(':nom_participant', $nom_participant);
        $stmt->bindParam(':email_participant', $email_participant);
        $stmt->bindParam(':niveau', $niveau);
        return $stmt->execute();
    }
}
?>