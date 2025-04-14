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
}
?>
