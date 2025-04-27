<?php
// AdminParticipantController.php
require_once(__DIR__ . '/../Model/participantBackModel.php');  // Include the correct model

class AdminParticipantController {
    private $model;

    public function __construct() {
        $this->model = new ParticipantBackModel();  // Use the ParticipantBackModel for admin actions
    }

    // Handle POST requests
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            switch ($action) {
                case 'add':
                    $nom_participant = $_POST['nom_participant'];
                    $email_participant = $_POST['email_participant'];
                    $nom_action = $_POST['nom_action'];
                    $this->addParticipant($nom_participant, $email_participant,$nom_action);
                    break;

                case 'update':
                    $id_participant = $_POST['id_participant'];
                    $nom_participant = $_POST['nom_participant'];
                    $email_participant = $_POST['email_participant'];
                    $nom_action = $_POST['nom_action'];
                    $this->updateParticipant($id_participant, $nom_participant, $email_participant, $nom_action);
                    break;

                case 'delete':
                    $id_participant = $_POST['id_participant'];
                    $this->cancelParticipation($id_participant);
                    break;

                default:
                    // Invalid action
                    break;
            }

            // Redirect back to the view after processing
            header('Location: ../View/eco_actionsB.php');
            exit();
        }
    }

    // Get all participants
    public function getAllParticipants() {
        return $this->model->getAllParticipants();  // Fetch all participants from the model
    }

    // Add a participant
    public function addParticipant($nom_participant, $email_participant, $nom_action) {
        return $this->model->addParticipant($nom_participant, $email_participant, $nom_action);  // Add participant via model
    }

    // Update a participant
    public function updateParticipant($id_participant, $nom_participant, $email_participant, $nom_action) {
        return $this->model->updateParticipant($id_participant, $nom_participant, $email_participant, $nom_action);  // Update participant via model
    }

    // Cancel a participant's registration
    public function cancelParticipation($id_participant) {
        return $this->model->cancelParticipation($id_participant);  // Cancel participation via model
    }
}

// Instantiate the controller and handle the request
$controller = new AdminParticipantController();
$controller->handleRequest();
?>