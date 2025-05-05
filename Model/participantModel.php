<?php
// participantModel.php

require_once(__DIR__ . '/../Config/database.php');  // Include the Config class for the database connection

class ParticipantModel {
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

    // Add a new participant
    public function addParticipant($nom_participant, $email_participant ,$id_action) {
        $query = "INSERT INTO participant (nom_participant, email_participant) VALUES (:nom_participant, :email_participant)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_participant', $nom_participant);
        $stmt->bindParam(':email_participant', $email_participant);
        return $stmt->execute();  // Execute the query and return if it was successful
    }

}
?>