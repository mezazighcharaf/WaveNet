<?php
require_once '../Model/participantModel.php';
require_once '../config/database.php';

class ParticipantController {
    private $participantModel;

    public function __construct() {
        $this->participantModel = new ParticipantModel($pdo);
    }

    public function addParticipant($nom, $email) {
        if (!empty($nom) && !empty($email)) {
            $this->participantModel->addParticipant($nom, $email);
            header('Location: ../View/eco_actionsB.php?status=success');
        } else {
            header('Location: ../View/eco_actionsB.php?status=error');
        }
    }

    public function updateParticipant($id, $nom, $email) {
        if (!empty($id) && !empty($nom) && !empty($email)) {
            $this->participantModel->updateParticipant($id, $nom, $email);
            header('Location: ../View/eco_actionsB.php?status=success');
        } else {
            header('Location: ../View/eco_actionsB.php?status=error');
        }
    }

    public function deleteParticipant($id) {
        if (!empty($id)) {
            $this->participantModel->deleteParticipant($id);
            header('Location: ../View/eco_actionsB.php?status=success');
        } else {
            header('Location: ../View/eco_actionsB.php?status=error');
        }
    }

    public function getAllParticipants() {
        $participants = $this->participantModel->getAllParticipants();
        include '../View/eco_actionsB.php';
    }

    public function getParticipantById($id) {
        $participant = $this->participantModel->getParticipantById($id);
        include '../View/eco_actions.php';
    }
}
?>
