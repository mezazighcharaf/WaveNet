<?php
class quartier {
    private $idq;
    private $nomq;
    private $ville;
    private $scoreeco;
    private $classement;

    public function __construct($idq,$nomq,$ville,$scoreeco,$classement){
        $this->idq = $idq;
        $this->nomq = $nomq;
        $this->ville = $ville;
        $this->scoreeco = $scoreeco;
        $this->classement = $classement;
    }



    public function getID_quartier(){
        return $this->idq;
    }
    public function getNom(){
        return $this->nomq;
    }
    public function getVille(){
        return $this->ville;
    }
    public function getscore_ecologique(){
        return $this->scoreeco;
    }
    public function getClassement(){
        return $this->classement;
    }



    public function setID_quartier(){
        $this->idq=$idq;
    }
    public function setNom(){
        $this->nomq=$nomq;
    }
    public function setVille(){
        $this->ville=$ville;
    }
    public function setscore_ecologique(){
        $this->scoreeco=$scoreeco;
    }
    public function setClassement(){
        $this->classement=$classement;
    }

}