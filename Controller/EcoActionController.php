<?php
require_once(__DIR__ . '/../Model/EcoActionModel.php');  // Include your model

class EcoActionController {
    private $model;

    public function __construct() {
        $this->model = new EcoActionModel();  // Create an instance of the model
    }

    // Get all eco actions by calling the model method
    public function getAllActions() {
        return $this->model->getAllEcoActions();  // Return data from the model
    }
}
?>
