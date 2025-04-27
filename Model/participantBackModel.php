<?php
// participantBackModel.php
require_once(__DIR__ . '/../Config/database.php');  // Include the Config class for the database connection

class ParticipantBackModel {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();  // Correct method name
    }

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
/*
    // Add a new participant
    public function addParticipant($nom_participant, $email_participant) {
        $query = "INSERT INTO participant (nom_participant, email_participant) VALUES (:nom_participant, :email_participant)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_participant', $nom_participant);
        $stmt->bindParam(':email_participant', $email_participant);
        return $stmt->execute();  // Execute the query and return if it was successful
    }
*/
    // Add a new participant
    public function addParticipant($nom_participant, $email_participant, $nom_action) {
        $query = "SELECT id_action FROM eco_action WHERE nom_action = :nom_action";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_action', $nom_action);  // Bind ID parameter
        $stmt->execute();
        $id_action= $stmt->fetch(PDO::FETCH_ASSOC)['id_action'];
        $query = "INSERT INTO participant (nom_participant, email_participant, id_action) VALUES (:nom_participant, :email_participant, :id_action)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_participant', $nom_participant);
        $stmt->bindParam(':email_participant', $email_participant);
        $stmt->bindParam(':id_action', $id_action, PDO::PARAM_INT);
        return $stmt->execute();  // Execute the query and return if it was successful
    }

    // Update a participant
    public function updateParticipant($id_participant, $nom_participant, $email_participant, $nom_action) {
        $query = "SELECT id_action FROM eco_action WHERE nom_action = :nom_action";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_action', $nom_action);  // Bind ID parameter
        $stmt->execute();
        $id_action= $stmt->fetch(PDO::FETCH_ASSOC)['id_action'];
        $query = "UPDATE participant SET nom_participant = :nom_participant, email_participant = :email_participant, id_action= :id_action WHERE id_participant = :id_participant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_participant', $id_participant, PDO::PARAM_INT);
        $stmt->bindParam(':id_action', $id_action, PDO::PARAM_INT);
        $stmt->bindParam(':nom_participant', $nom_participant);
        $stmt->bindParam(':email_participant', $email_participant);
        return $stmt->execute();  // Execute the query and return if it was successful
    }
/*
    // Register a participant
    public function participate($id_participant) {
        $query = "UPDATE participant SET is_participating = 1 WHERE id_participant = :id_participant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_participant', $id_participant);
        return $stmt->execute();  // Execute the update to register the participant
    }
$*/
    // Cancel a participant's registration
    public function cancelParticipation($id_participant) {
        $query = "DELETE FROM participant WHERE id_participant = :id_participant ";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_participant', $id_participant);
        return $stmt->execute();  // Execute the update to cancel the participant's registration
    }
}
?>