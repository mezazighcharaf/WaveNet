<?php
require_once(__DIR__ . '/../Database/Database.php');  // Inclure la classe Database pour la connexion

class ParticipantModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();  // Créer une instance de connexion à la base de données
    }

    // Récupérer tous les participants
    public function getAllParticipant() {
        $query = "SELECT * FROM participants";  // Requête pour obtenir tous les participants
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);  // Retourner les résultats sous forme de tableau associatif
    }

    // Ajouter un participant
    public function addParticipant($nom_participant, $email_participant) {
        $query = "INSERT INTO participants (nom_participant, email_participant) VALUES (:nom_participant, :email_participant)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom_participant', $nom_participant);
        $stmt->bindParam(':email_participant', $email_participant);
        return $stmt->execute();  // Exécuter la requête d'ajout et retourner si elle a réussi
    }

    // Inscrire un participant
    public function participate($id_participant) {
        $query = "UPDATE participants SET is_participating = 1 WHERE id_participant = :id_participant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_participant', $id_participant);
        return $stmt->execute();  // Exécuter la mise à jour pour inscrire le participant
    }

    // Annuler la participation d'un participant
    public function cancelParticipation($id_participant) {
        $query = "UPDATE participants SET is_participating = 0 WHERE id_participant = :id_participant";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_participant', $id_participant);
        return $stmt->execute();  // Exécuter la mise à jour pour annuler la participation du participant
    }
}
?>
