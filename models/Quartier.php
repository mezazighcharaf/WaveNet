<?php
class Quartier {
    private $id_quartier;
    private $nom_quartier;
    public function __construct($nom_quartier) {
        $this->nom_quartier = $nom_quartier;
    }
    public function getId() { return $this->id_quartier; }
    public function getNomQuartier() { return $this->nom_quartier; }
    public function setNomQuartier($nom) { $this->nom_quartier = $nom; }
    public static function findById($db, $id) {
        $stmt = $db->prepare("SELECT * FROM QUARTIER WHERE id_quartier = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function findAll($db) {
        $stmt = $db->query("SELECT * FROM QUARTIER");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function create($db, $nom_quartier) {
        $stmt = $db->prepare("INSERT INTO QUARTIER (nom_quartier) VALUES (:nom_quartier)");
        return $stmt->execute(['nom_quartier' => $nom_quartier]);
    }
}
