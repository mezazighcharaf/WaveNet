<?php
require_once '../config/database.php';

class EcoActionBackModel {
    private $conn;

    public function __construct() {
        $this->conn = config::getConnexion();
    }

    // CREATE
    public function addEcoAction($nom, $description, $date, $statut, $points_verts, $categorie) {
        $stmt = $this->conn->prepare("INSERT INTO eco_action (nom_action, description_action, date, etat, point_vert, categorie)
                                      VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$nom, $description, $date, $statut, $points_verts, $categorie]);
    }

    // READ ALL
    public function getAllEcoActions() {
        $stmt = $this->conn->query("SELECT * FROM eco_action");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ ONE
    public function getEcoActionById($id_action) {
        $stmt = $this->conn->prepare("SELECT * FROM eco_action WHERE id_action = ?");
        $stmt->execute([$id_action]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // UPDATE
    public function updateEcoAction($id_action, $nom, $description, $date, $statut, $points_verts, $categorie) {
        $stmt = $this->conn->prepare("UPDATE eco_action 
                                      SET nom_action = ?, description_action = ?, date = ?, etat = ?, point_vert = ?, categorie = ?
                                      WHERE id_action = ?");
        return $stmt->execute([$nom, $description, $date, $statut, $points_verts, $categorie, $id_action]);
    }

    // DELETE
    public function deleteEcoAction($id_action) {
        $stmt = $this->conn->prepare("DELETE FROM eco_action WHERE id_action = ?");
        return $stmt->execute([$id_action]);
    }

    private function validateInputs($data) {
        $errors = [];
    
        if (empty(trim($data['nom']))) {
            $errors[] = "Le nom est obligatoire.";
        } elseif (strlen(trim($data['nom'])) < 3) {
            $errors[] = "Le nom doit comporter au moins 3 caractères.";
        } elseif (strlen($data['nom']) > 255) {
            $errors[] = "Le nom ne doit pas dépasser 255 caractères.";
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $data['nom'])) {
            $errors[] = "Le nom ne doit contenir que des lettres et des espaces.";
        }
    
        if (empty(trim($data['description']))) {
            $errors[] = "La description est obligatoire.";
        }
    
        $dateNow = date('Y-m-d');
        if (empty($data['date'])) {
            $errors[] = "La date est obligatoire.";
        } elseif ($data['date'] < $dateNow) {
            $errors[] = "La date doit être postérieure ou égale à aujourd'hui.";
        }
    
        $statutsAutorises = ['encours', 'termine', 'annule'];
        if (empty($data['statut'])) {
            $errors[] = "Le statut est obligatoire.";
        } elseif (!in_array($data['statut'], $statutsAutorises)) {
            $errors[] = "Le statut sélectionné est invalide.";
        }
    
        if (!is_numeric($data['points_verts']) || $data['points_verts'] < 0) {
            $errors[] = "Les points verts doivent être un nombre positif.";
        }
    
        $categoriesAutorisees = ['environnement', 'biodiversite', 'recyclage', 'energie'];
        if (empty($data['categorie'])) {
            $errors[] = "La catégorie est obligatoire.";
        } elseif (!in_array($data['categorie'], $categoriesAutorisees)) {
            $errors[] = "La catégorie sélectionnée est invalide.";
        }
    
        return $errors;
    }
    
}
?>
