<?php
require_once(__DIR__ . '/../Model/EcoActionModel.php');  // Include your model

class EcoActionController {
    private $model;

    public function __construct() {
        $this->model = new EcoActionModel();  // Create an instance of the model
    }

    

public function getActionsByEtat($etat) {
    $ecoActionModel = new EcoActionModel();
    
    // Appel de la méthode pour récupérer les actions filtrées par état
    return $ecoActionModel->getActionsByEtat($etat);

}

}
?>