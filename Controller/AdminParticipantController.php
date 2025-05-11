<?php
// AdminParticipantController.php
require_once(__DIR__ . '/../models/participantModel.php');

class AdminParticipantController {
    private $model;

    public function __construct() {
        $this->model = new ParticipantModel();
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;
            $errors = [];
            $formData = $_POST; // Pour sauvegarder les données saisies

            switch ($action) {
                case 'add':
                case 'update':
                    $id_participant = $_POST['id_participant'] ?? null; // utile pour update
                    $nom_participant = trim($_POST['nom_participant'] ?? '');
                    $email_participant = trim($_POST['email_participant'] ?? '');
                    $nom_action = trim($_POST['nom_action'] ?? '');
                    $niveau = trim($_POST['niveau'] ?? ''); // <-- AJOUTÉ : récupération du niveau

                    // CONTROLE DE SAISIE
                    if (empty($nom_participant)) {
                        $errors['nom_participant'] = 'Le nom du participant est requis.';
                    } elseif (strlen($nom_participant) < 3 || strlen($nom_participant) > 15) {
                        $errors['nom_participant'] = 'Le nom du participant doit contenir entre 3 et 15 caractères.';
                    }

                    if (empty($email_participant)) {
                        $errors['email_participant'] = 'L\'adresse email est requise.';
                    } elseif (!filter_var($email_participant, FILTER_VALIDATE_EMAIL)) {
                        $errors['email_participant'] = 'Adresse email invalide.';
                    }

                    if (empty($nom_action)) {
                        $errors['nom_action'] = 'Le nom de l\'action est requis.';
                    }

                    if (empty($niveau)) {
                        $errors['niveau'] = 'Le niveau est requis.';
                    }

                    if ($action === 'update' && empty($id_participant)) {
                        $errors['id_participant'] = 'Identifiant du participant manquant pour la mise à jour.';
                    }

                    if (empty($errors)) {
                        if ($action === 'add') {
                            $this->addParticipant($nom_participant, $email_participant, $nom_action, $niveau);
                            $_SESSION['success'] = 'Participant ajouté avec succès.';
                        } else {
                            $this->updateParticipant($id_participant, $nom_participant, $email_participant, $nom_action, $niveau);
                            $_SESSION['success'] = 'Participant mis à jour avec succès.';
                        }
                        header('Location: ../views/backoffice/eco_actionsB.php');
                        exit();
                    } else {
                        // Stocker les erreurs et les données du formulaire
                        $_SESSION['errors'] = $errors;
                        $_SESSION['formData'] = $formData;
                        header('Location: ../views/backoffice/eco_actionsB.php');
                        exit();
                    }
                    break;

                case 'delete':
                    $id_participant = $_POST['id_participant'] ?? null;

                    if (empty($id_participant)) {
                        $_SESSION['errors'] = ['id_participant' => 'Identifiant du participant manquant pour la suppression.'];
                    } else {
                        $this->cancelParticipation($id_participant);
                        $_SESSION['success'] = 'Participation annulée avec succès.';
                    }
                    header('Location: ../views/backoffice/eco_actionsB.php');
                    exit();
                    break;

                default:
                    $_SESSION['errors'] = ['action' => 'Action non valide.'];
                    header('Location: ../views/backoffice/eco_actionsB.php');
                    exit();
            }
        }
    }

    public function getAllParticipants() {
        return $this->model->getAllParticipants();
    }

    public function addParticipant($nom_participant, $email_participant, $nom_action, $niveau) { // <-- AJOUTÉ $niveau
        return $this->model->addParticipantBack($nom_participant, $email_participant, $nom_action, $niveau);
    }

    public function updateParticipant($id_participant, $nom_participant, $email_participant, $nom_action, $niveau) { // <-- AJOUTÉ $niveau
        return $this->model->updateParticipant($id_participant, $nom_participant, $email_participant, $nom_action, $niveau);
    }

    public function cancelParticipation($id_participant) {
        return $this->model->cancelParticipation($id_participant);
    }
    public function getStatistiquesParNiveau() {
        require_once(__DIR__ . '/../views/includes/config.php');
        $db = connectDB(); // Remplacement de Config::getConnexion() par connectDB()
    
        $query = "SELECT niveau, COUNT(*) as total FROM participant GROUP BY niveau";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $result;
    }
}

// Instancier et traiter la requête
$controller = new AdminParticipantController();
$controller->handleRequest();
?>
