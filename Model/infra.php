<?php
class infra{
    private $id_infra;
    private $type;
    private $statut;

    public function __construct($id_infra,$type,$statut){
        $this->id_infra = $id_infra;
        $this->type = $type;
        $this->statut = $statut;

    }



    public function getIdInfra() {  
        return $this->id_infra;
    }
    public function getType(){
        return $this->type;
    }
    public function getStatut(){
        return $this->statut;
    }
    


    public function setIdInfra($id_infra) {  
        $this->id_infra = $id_infra;
    }
    public function setType($type) {  
        $this->type = $type;
    }
    public function setStatut($statut) { 
        $this->statut = $statut;
    }
    

}