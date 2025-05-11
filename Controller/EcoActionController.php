<?php
require_once(__DIR__ . '/../models/EcoActionModel.php');  // Include your model

class EcoActionController {
    private $model;

    public function __construct() {
        $this->model = new EcoActionModel();  // Create an instance of the model
    }

    // Get all eco actions
    public function getAllEcoActions() {
        return $this->model->getAllEcoActions();
    }

    // Get eco actions by etat
    public function getActionsByEtat($etat) {
        return $this->model->getActionsByEtat($etat);
    }

    // Search for eco actions by name
    public function searchActionByName($search) {
        return $this->model->searchActionByName($search);
    }

    // Add participant to eco action
    public function ajouterParticipant($id_action, $id_utilisateur) {
        return $this->model->ajouterParticipant($id_action, $id_utilisateur);
    }

    // Cancel participation
    public function annulerParticipation($id_action, $id_utilisateur) {
        return $this->model->annulerParticipation($id_action, $id_utilisateur);
    }
}
?>