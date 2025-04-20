<?php
class Defi {
    private $id_defi;
    private $titre;
    private $description;
    private $date_debut;
    private $date_fin;
    private $statut;
    private $objectif;
    private $points_verts;
    private $difficulte;
    private $id_quartier;
    public function __construct($id_defi, $titre, $description, $objectif, $points_verts, $statut, $date_debut, $date_fin, $difficulte, $id_quartier) {
        $this->id_defi = $id_defi;
        $this->titre = $titre;
        $this->description = $description;
        $this->objectif = $objectif;
        $this->points_verts = $points_verts;
        $this->statut = $statut;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
        $this->difficulte = $difficulte;
        $this->id_quartier = $id_quartier;
    }
    public function getId() { return $this->id_defi; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getObjectif() { return $this->objectif; }
    public function getPointsVerts() { return $this->points_verts; }
    public function getStatut() { return $this->statut; }
    public function getDateDebut() { return $this->date_debut; }
    public function getDateFin() { return $this->date_fin; }
    public function getDifficulte() { return $this->difficulte; }
    public function getIdQuartier() { return $this->id_quartier; }
    public static function getDefisByQuartier($db, $id_quartier) {
        $stmt = $db->prepare("SELECT * FROM Defi WHERE Id_Quartier = :id_quartier");
        $stmt->execute(['id_quartier' => $id_quartier]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function countDefisEnCours($db, $id_utilisateur) {
        return 0;
    }
    public static function countDefisCompletes($db, $id_utilisateur) {
        return 0;
    }
    public static function getAllDefis($db) {
        $stmt = $db->prepare("SELECT * FROM Defi");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getDefiById($db, $id_defi) {
        $stmt = $db->prepare("SELECT * FROM Defi WHERE Id_Defi = :id_defi");
        $stmt->execute(['id_defi' => $id_defi]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
