<?php
// ParticipantController.php

require_once(__DIR__ . '/../Model/participantModel.php');  // Include the correct model

class ParticipantController {
    private $model;

    public function __construct() {
        $this->model = new ParticipantModel();  // Use the ParticipantModel for front-end actions
    }

    // Get all participants
    public function getAllParticipants() {
        return $this->model->getAllParticipants();  // Fetch all participants from the model
    }

    // Add a participant
    public function addParticipant($nom_participant, $email_participant) {
        return $this->model->addParticipant($nom_participant, $email_participant);  // Add participant via model
    }

    // Cancel a participant's registration
    public function cancelParticipation($id_participant) {
        return $this->model->cancelParticipation($id_participant);  // Cancel participation via model
    }

    // Register a participant
    public function participate($id_participant) {
        return $this->model->participate($id_participant);  // Register a participant via model
    }
}
?>