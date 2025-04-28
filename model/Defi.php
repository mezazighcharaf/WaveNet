<?php
require_once 'Database.php';

class Defi {
    // Database connection and table name
    private $conn;
    private $table_name = "defi";

    // Object properties matching the fields from your schema
    public $Id_Defi;
    public $Titre_D;
    public $Description_D;
    public $Objectif;
    public $Points_verts;
    public $Statut_D;
    public $Date_Debut;
    public $Date_Fin;
    public $Difficulte;
    public $Id_Quartier;

    // Constructor with DB connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // READ all defis
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY Date_Debut DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // READ all active defis
    public function readAllActive() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE Statut_D = 'Actif' 
                  AND Date_Debut <= CURRENT_DATE 
                  AND Date_Fin >= CURRENT_DATE
                  ORDER BY Date_Debut DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // READ defis by difficulty
    public function readByDifficulty($difficulty) {
        // Convert first letter to uppercase and the rest to lowercase to handle different cases
        $formattedDifficulty = ucfirst(strtolower($difficulty));
        
        // Update the query to remove active filter to show all defis with the selected difficulty
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE Difficulte = ? 
                  ORDER BY Date_Debut DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $formattedDifficulty);
        $stmt->execute();
        return $stmt;
    }
    
    // READ popular defis
    public function readPopular($limit = 4) {
        // In a real app, this would join with a participation table to count participants
        // For simplicity, we'll just order by points
        $query = "SELECT * FROM " . $this->table_name . " 
                  ORDER BY Points_verts DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // READ one defi
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Id_Defi = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->Id_Defi);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->Titre_D = $row['Titre_D'];
            $this->Description_D = $row['Description_D'];
            $this->Objectif = $row['Objectif'];
            $this->Points_verts = $row['Points_verts'];
            $this->Statut_D = $row['Statut_D'];
            $this->Date_Debut = $row['Date_Debut'];
            $this->Date_Fin = $row['Date_Fin'];
            $this->Difficulte = $row['Difficulte'];
            $this->Id_Quartier = $row['Id_Quartier'];
            return true;
        }
        
        return false;
    }

    // CREATE defi
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (Titre_D, Description_D, Objectif, Points_verts, Statut_D, 
                 Date_Debut, Date_Fin, Difficulte, Id_Quartier) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->Titre_D = htmlspecialchars(strip_tags($this->Titre_D));
        $this->Description_D = htmlspecialchars(strip_tags($this->Description_D));
        $this->Objectif = htmlspecialchars(strip_tags($this->Objectif));
        $this->Points_verts = htmlspecialchars(strip_tags($this->Points_verts));
        $this->Statut_D = htmlspecialchars(strip_tags($this->Statut_D));
        $this->Date_Debut = htmlspecialchars(strip_tags($this->Date_Debut));
        $this->Date_Fin = htmlspecialchars(strip_tags($this->Date_Fin));
        $this->Difficulte = htmlspecialchars(strip_tags($this->Difficulte));
        $this->Id_Quartier = htmlspecialchars(strip_tags($this->Id_Quartier));
        
        // Bind parameters
        $stmt->bindParam(1, $this->Titre_D);
        $stmt->bindParam(2, $this->Description_D);
        $stmt->bindParam(3, $this->Objectif);
        $stmt->bindParam(4, $this->Points_verts);
        $stmt->bindParam(5, $this->Statut_D);
        $stmt->bindParam(6, $this->Date_Debut);
        $stmt->bindParam(7, $this->Date_Fin);
        $stmt->bindParam(8, $this->Difficulte);
        $stmt->bindParam(9, $this->Id_Quartier);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // UPDATE defi
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET Titre_D = ?, Description_D = ?, Objectif = ?, 
                 Points_verts = ?, Statut_D = ?, Date_Debut = ?, 
                 Date_Fin = ?, Difficulte = ?, Id_Quartier = ? 
                 WHERE Id_Defi = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->Titre_D = htmlspecialchars(strip_tags($this->Titre_D));
        $this->Description_D = htmlspecialchars(strip_tags($this->Description_D));
        $this->Objectif = htmlspecialchars(strip_tags($this->Objectif));
        $this->Points_verts = htmlspecialchars(strip_tags($this->Points_verts));
        $this->Statut_D = htmlspecialchars(strip_tags($this->Statut_D));
        $this->Date_Debut = htmlspecialchars(strip_tags($this->Date_Debut));
        $this->Date_Fin = htmlspecialchars(strip_tags($this->Date_Fin));
        $this->Difficulte = htmlspecialchars(strip_tags($this->Difficulte));
        $this->Id_Quartier = htmlspecialchars(strip_tags($this->Id_Quartier));
        $this->Id_Defi = htmlspecialchars(strip_tags($this->Id_Defi));
        
        // Bind parameters
        $stmt->bindParam(1, $this->Titre_D);
        $stmt->bindParam(2, $this->Description_D);
        $stmt->bindParam(3, $this->Objectif);
        $stmt->bindParam(4, $this->Points_verts);
        $stmt->bindParam(5, $this->Statut_D);
        $stmt->bindParam(6, $this->Date_Debut);
        $stmt->bindParam(7, $this->Date_Fin);
        $stmt->bindParam(8, $this->Difficulte);
        $stmt->bindParam(9, $this->Id_Quartier);
        $stmt->bindParam(10, $this->Id_Defi);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // DELETE defi
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE Id_Defi = ?";
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->Id_Defi = htmlspecialchars(strip_tags($this->Id_Defi));
        
        // Bind parameter
        $stmt->bindParam(1, $this->Id_Defi);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // READ all defis ordered by title
    public function readAllOrderByTitle($order = 'ASC') {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY Titre_D " . ($order === 'DESC' ? 'DESC' : 'ASC');
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?> 