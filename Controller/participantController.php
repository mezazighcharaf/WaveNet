<?php
require_once(__DIR__ . '/../Model/participantModel.php');  // Inclure le modèle

class ParticipantController {
    private $model;

    public function __construct() {
        $this->model = new ParticipantModel();  // Créer une instance du modèle
    }

    // Récupérer tous les participants
    public function getAllParticipant() {
        return $this->model->getAllParticipant();  // Retourner les données du modèle
    }

    // Ajouter un participant
    public function addParticipant($nom_participant, $email_participant) {
        return $this->model->addParticipant($nom_participant, $email_participant);  // Ajouter un participant
    }

    // Annuler la participation d'un participant
    public function cancelParticipation($id_participant) {
        return $this->model->cancelParticipation($id_participant);  // Annuler la participation
    }
    
    // Inscrire un participant à un événement/action
    public function participate($id_participant) {
        return $this->model->participate($id_participant);  // Inscrire un participant
    }
}
?>
