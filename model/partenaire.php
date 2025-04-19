<?php

class Partenaire {
    // Attributs
    private $id_part;
    private $nom_part;
    private $tel;
    private $mail;
    private $adresse;

    // Constructeur
    public function __construct($id_part = null, $nom_part = null, $tel = null, $mail = null, $adresse = null) {
        $this->id_part = $id_part;
        $this->nom_part = $nom_part;
        $this->tel = $tel;
        $this->mail = $mail;
        $this->adresse = $adresse;
    }

    // Getters
    public function getIdPart() {
        return $this->id_part;
    }

    public function getNomPart() {
        return $this->nom_part;
    }

    public function getTel() {
        return $this->tel;
    }

    public function getMail() {
        return $this->mail;
    }

    public function getAdresse() {
        return $this->adresse;
    }

    // Setters
    public function setIdPart($id_part) {
        $this->id_part = $id_part;
    }

    public function setNomPart($nom_part) {
        $this->nom_part = $nom_part;
    }

    public function setTel($tel) {
        $this->tel = $tel;
    }

    public function setMail($mail) {
        $this->mail = $mail;
    }

    public function setAdresse($adresse) {
        $this->adresse = $adresse;
    }
}
?>