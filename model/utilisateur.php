<?php
class Utilisateur {
    private $id_utilisateur;
    private $nom;
    private $prenom;
    private $email;
    private $points_verts;

    // Getters et setters
    public function getIdUtilisateur() {
        return $this->id_utilisateur;
    }

    public function setIdUtilisateur($id_utilisateur) {
        $this->id_utilisateur = $id_utilisateur;
    }

    public function getNom() {
        return $this->nom;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getPointsVerts() {
        return $this->points_verts;
    }

    public function setPointsVerts($points_verts) {
        $this->points_verts = $points_verts;
    }
}
?>