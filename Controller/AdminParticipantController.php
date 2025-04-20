<?php
require_once '../Model/participantBackModel.php';
require_once '../config/database.php';

class AdminParticipantController {
    private $participantBackModel;

    public function __construct() {
        $this->participantBackModel = new ParticipantBackModel($pdo);
    }

    public function getPaginatedParticipants($page = 1) {
        $participants = $this->participantBackModel->getPaginatedParticipants($page, 10);
        include '../View/eco_actions.php';
    }

    public function searchParticipants($name) {
        $participants = $this->participantBackModel->searchParticipantsByName($name);
        include '../View/eco_Actions.php';
    }
}
?>
