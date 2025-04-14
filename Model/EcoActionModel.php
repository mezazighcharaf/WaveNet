<?php
require_once '../config/database.php'; // Ensure your database connection is available

class EcoActionModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();  // Use your Config class for DB connection
    }

    // Get all eco actions from the database
    public function getAllEcoActions() {
        $sql = "SELECT * FROM eco_action";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $actions = [];

        // Fetch all actions from the database
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $actions[] = $row;
        }

        return $actions;
    }
}
?>
