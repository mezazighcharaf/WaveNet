<?php

class Intervention {
    private $id_intervention;
    private $id_signalement;
    private $date_intervention;
    private $statut;
    private $description;
    private $intervenant;
    private $date_fin;

    // Getters
    public function getIdIntervention() {
        return $this->id_intervention;
    }

    public function getIdSignalement() {
        return $this->id_signalement;
    }

    public function getDateIntervention() {
        return $this->date_intervention;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getIntervenant() {
        return $this->intervenant;
    }

    public function getDateFin() {
        return $this->date_fin;
    }

    // Setters
    public function setIdIntervention($id_intervention) {
        $this->id_intervention = $id_intervention;
    }

    public function setIdSignalement($id_signalement) {
        $this->id_signalement = $id_signalement;
    }

    public function setDateIntervention($date_intervention) {
        $this->date_intervention = $date_intervention;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setIntervenant($intervenant) {
        $this->intervenant = $intervenant;
    }

    public function setDateFin($date_fin) {
        $this->date_fin = $date_fin;
    }
}
?>