<?php
require_once 'Database.php';

class Etape {
    private $conn;
    private $table_name = "etape";

    public $Id_Etape;
    public $Titre_E;
    public $Description_E;
    public $Points_Bonus;
    public $Ordre;
    public $Statut_E;
    public $Id_Defi;

    public function __construct($db) {
        $this->conn = $db;
    }

    // READ all étapes
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY Ordre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ étapes for a specific défi
    public function readByDefi($id_defi) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Id_Defi = ? ORDER BY Ordre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_defi, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    // Ajouter cette méthode pour corriger l'erreur
    public function readAllByDefi($id_defi) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Id_Defi = ? ORDER BY Ordre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_defi, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // READ one étape
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Id_Etape = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->Id_Etape, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->Titre_E = $row['Titre_E'];
            $this->Description_E = $row['Description_E'];
            $this->Points_Bonus = $row['Points_Bonus'];
            $this->Ordre = $row['Ordre'];
            $this->Statut_E = $row['Statut_E'];
            $this->Id_Defi = $row['Id_Defi'];
            return true;
        }
        return false;
    }

    // CREATE étape
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (Titre_E, Description_E, Points_Bonus, Ordre, Statut_E, Id_Defi)
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $this->Titre_E = htmlspecialchars(strip_tags($this->Titre_E));
        $this->Description_E = htmlspecialchars(strip_tags($this->Description_E));
        $this->Points_Bonus = htmlspecialchars(strip_tags($this->Points_Bonus));
        $this->Ordre = htmlspecialchars(strip_tags($this->Ordre));
        $this->Statut_E = htmlspecialchars(strip_tags($this->Statut_E));
        $this->Id_Defi = htmlspecialchars(strip_tags($this->Id_Defi));

        $stmt->bindParam(1, $this->Titre_E);
        $stmt->bindParam(2, $this->Description_E);
        $stmt->bindParam(3, $this->Points_Bonus);
        $stmt->bindParam(4, $this->Ordre);
        $stmt->bindParam(5, $this->Statut_E);
        $stmt->bindParam(6, $this->Id_Defi);

        return $stmt->execute();
    }

    // UPDATE étape
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET Titre_E = ?, Description_E = ?, Points_Bonus = ?, Ordre = ?, Statut_E = ?, Id_Defi = ?
                  WHERE Id_Etape = ?";
        $stmt = $this->conn->prepare($query);

        $this->Titre_E = htmlspecialchars(strip_tags($this->Titre_E));
        $this->Description_E = htmlspecialchars(strip_tags($this->Description_E));
        $this->Points_Bonus = htmlspecialchars(strip_tags($this->Points_Bonus));
        $this->Ordre = htmlspecialchars(strip_tags($this->Ordre));
        $this->Statut_E = htmlspecialchars(strip_tags($this->Statut_E));
        $this->Id_Defi = htmlspecialchars(strip_tags($this->Id_Defi));
        $this->Id_Etape = htmlspecialchars(strip_tags($this->Id_Etape));

        $stmt->bindParam(1, $this->Titre_E);
        $stmt->bindParam(2, $this->Description_E);
        $stmt->bindParam(3, $this->Points_Bonus);
        $stmt->bindParam(4, $this->Ordre);
        $stmt->bindParam(5, $this->Statut_E);
        $stmt->bindParam(6, $this->Id_Defi);
        $stmt->bindParam(7, $this->Id_Etape);

        return $stmt->execute();
    }

    // DELETE étape
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE Id_Etape = ?";
        $stmt = $this->conn->prepare($query);

        $this->Id_Etape = htmlspecialchars(strip_tags($this->Id_Etape));
        $stmt->bindParam(1, $this->Id_Etape);

        return $stmt->execute();
    }

    // Retrieve defis sorted by alphabetical order
    public function getDefisByAlphabeticalOrder() {
        return $this->defi->readAllAlphabetical();
    }

    // Retrieve defis by status
    public function getDefisByStatus($status) {
        return $this->defi->readByStatus($status);
    }

    // READ all defis in alphabetical order
    public function readAllAlphabetical() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY Titre_D ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ defis by status
    public function readByStatus($status) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Statut_D = ? ORDER BY Date_Debut DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->execute();
        return $stmt;
    }
}
?>
