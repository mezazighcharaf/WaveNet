<?php
require_once '../config/database.php';  // Ensure your database connection is available

class ParticipantModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();  // Use your Config class for DB connection
    }

    // ADD a participant
    public function addParticipant($nom, $email, $date_inscrit) {
        $stmt = $this->pdo->prepare("INSERT INTO participant (nom_participant, email_participant, date_inscrit)
                                     VALUES (?, ?, ?)");
        return $stmt->execute([$nom, $email, $date_inscrit]);
    }

    // UPDATE a participant
    public function updateParticipant($id_participant, $nom, $email, $date_inscrit) {
        $stmt = $this->pdo->prepare("UPDATE participant 
                                     SET nom_participant = ?, email_participant = ?, date_inscrit = ? 
                                     WHERE id_participant = ?");
        return $stmt->execute([$nom, $email, $date_inscrit, $id_participant]);
    }

    // DELETE a participant
    public function deleteParticipant($id_participant) {
        $stmt = $this->pdo->prepare("DELETE FROM participant WHERE id_participant = ?");
        return $stmt->execute([$id_participant]);
    }

    // Get all participants
    public function getAllParticipants() {
        $stmt = $this->pdo->query("SELECT * FROM participant");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
