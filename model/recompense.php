<?php
class Recompense {
    // Attributs
    private $id_rec;
    private $nom_rec;
    private $description;
    private $cout;
    private $date_fin;
    private $id_part;

    // Constructeur
    public function __construct($id_rec = null, $nom_rec = null, $description = null, $cout = null, $date_fin = null, $id_part = null) {
        $this->id_rec = $id_rec;
        $this->nom_rec = $nom_rec;
        $this->description = $description;
        $this->cout = $cout;
        $this->date_fin = $date_fin;
        $this->id_part = $id_part;
    }

    // Getters
    public function getIdRec() {
        return $this->id_rec;
    }

    public function getNomRec() {
        return $this->nom_rec;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getCout() {
        return $this->cout;
    }

    public function getDateFin() {
        return $this->date_fin;
    }

    public function getIdPart() {
        return $this->id_part;
    }

    // Setters
    public function setIdRec($id_rec) {
        $this->id_rec = $id_rec;
    }

    public function setNomRec($nom_rec) {
        $this->nom_rec = $nom_rec;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setCout($cout) {
        $this->cout = $cout;
    }

    public function setDateFin($date_fin) {
        $this->date_fin = $date_fin;
    }

    public function setIdPart($id_part) {
        $this->id_part = $id_part;
    }
    
    public function getNomPartenaire() {
        return $this->nomPartenaire;
    }

    public function setNomPartenaire($nomPartenaire) {
        $this->nomPartenaire = $nomPartenaire;
    }
}
?>