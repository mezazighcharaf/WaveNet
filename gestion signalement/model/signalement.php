<?php
class Signalement {
    private $id_signalement;
    private $titre;
    private $description;
    private $emplacement;
    private $date_signalement;
    private $statut;

    public function setIdSignalement($id_signalement) {
        $this->id_signalement = $id_signalement;
    }

    public function setTitre($titre) {
        $this->titre = $titre;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setEmplacement($emplacement) {
        $this->emplacement = $emplacement;
    }

    public function setDateSignalement($date_signalement) {
        $this->date_signalement = $date_signalement;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

    public function getIdSignalement() {
        return $this->id_signalement;
    }

    public function getTitre() {
        return $this->titre;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getEmplacement() {
        return $this->emplacement;
    }

    public function getDateSignalement() {
        return $this->date_signalement;
    }

    public function getStatut() {
        return $this->statut;
    }
}
?>
