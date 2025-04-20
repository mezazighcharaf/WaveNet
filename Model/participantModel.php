<?php
require_once(__DIR__ . '/../Config/database.php');

class ParticipantModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAllParticipants() {
        $stmt = $this->conn->prepare("SELECT * FROM participant");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addParticipant($nom, $email) {
        $stmt = $this->conn->prepare("INSERT INTO participant (nom_participant, email_participant, date_inscrit) VALUES (?, ?, NOW())");
        return $stmt->execute([$nom, $email]);
    }

    public function deleteParticipant($id) {
        $stmt = $this->conn->prepare("DELETE FROM participant WHERE id_participant = ?");
        return $stmt->execute([$id]);
    }

    public function updateParticipant($id, $nom, $email) {
        $stmt = $this->conn->prepare("UPDATE participant SET nom_participant = ?, email_participant = ? WHERE id_participant = ?");
        return $stmt->execute([$nom, $email, $id]);
    }

    public function getParticipantById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM participant WHERE id_participant = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
