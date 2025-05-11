<?php
// ParticipantController.php
session_start(); 
$_SESSION['username'] = "neyrouz";
$_SESSION['email'] = "neyrouz@gmail.com";

require_once(__DIR__ . '/../models/participantModel.php');  // Updated path to models directory

class ParticipantController {
    private $model;

    public function __construct() {
        $this->model = new ParticipantModel();  // Use the ParticipantModel for front-end actions
    }

    // Get all participants
    public function getAllParticipants() {
        return $this->model->getAllParticipants();  // Fetch all participants from the model
    }

    public function getParticipantbynameandemail($name,$email){
        return $this->model->getParticipantbynameandemail($name,$email);
    }

    // Add a participant
    public function addParticipant($nom_participant, $email_participant,$id_action) {
        // Ajouter des points verts pour la participation
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            require_once(__DIR__ . '/../models/Utilisateur.php');
            require_once(__DIR__ . '/../views/includes/config.php');
            $db = connectDB();
            $user = Utilisateur::findById($db, $_SESSION['user_id']);
            
            if ($user) {
                // Attribuer des points verts pour la participation (par exemple 10 points)
                $pointsVerts = $user->getPointsVerts() + 10;
                $user->setPointsVerts($pointsVerts);
                $user->update($db);
                
                // Mettre à jour la session
                $_SESSION['user_points'] = $pointsVerts;
                $_SESSION['success_message'] = "Participation enregistrée ! Vous avez gagné 10 points verts.";
            }
        }
        
        return $this->model->addParticipant($nom_participant, $email_participant,$id_action);  // Add participant via model
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
$controller = new ParticipantController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action_type'])) {
        $actionType = $_POST['action_type'];

        if ($actionType === 'participer') {
            $nom=$_SESSION['username'];
            $email=$_SESSION['email'];
            $id_action=$_POST['id_action'];
            $controller->addParticipant($nom, $email,$id_action);
            header ("location: ../views/frontoffice/eco_actions.php");
        } elseif ($actionType === 'annuler') {
            $nom=$_SESSION['username'];
            $email=$_SESSION['email'];
            $id_participant=$controller->getParticipantbynameandemail($nom,$email)['id_participant'];
            if (!$id_participant) {echo"participant not found!";}
            $controller->cancelParticipation($id_participant);
            header ("location: ../views/frontoffice/eco_actions.php");
        }
        // Add more conditions for other actions if needed
    }
}
?>