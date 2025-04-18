<?php
require_once __DIR__ . '/../model/recompense.php';
require_once __DIR__ . '/../model/partenaire.php';
require_once __DIR__ . '/../config.php';


class RecompenseController {
    private $model;
    private $pdo;

    public function __construct() {
        $this->model = new Recompense();
        $this->pdo = Config::getConnexion();
    }

    private function validateRecompenseData($nom, $description, $cout, $date_fin, $id_partenaire) {
        $errors = [];

        // Validation du nom
        if (empty($nom)) {
            $errors[] = "Le nom de la récompense est obligatoire";
        } elseif (strlen($nom) > 255) {
            $errors[] = "Le nom ne doit pas dépasser 255 caractères";
        }

        // Validation de la description
        if (empty($description)) {
            $errors[] = "La description est obligatoire";
        }

        // Validation du coût
        if (!is_numeric($cout) || $cout <= 0) {
            $errors[] = "Le coût doit être un nombre positif";
        }

        // Validation de la date
        if (empty($date_fin)) {
            $errors[] = "La date de fin est obligatoire";
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $date_fin);
            if (!$date || $date->format('Y-m-d') !== $date_fin) {
                $errors[] = "Format de date invalide (YYYY-MM-DD attendu)";
            } elseif ($date < new DateTime('today')) {
                $errors[] = "La date de fin ne peut pas être dans le passé";
            }
        }

        // Validation du partenaire
        if (!is_numeric($id_partenaire) || $id_partenaire <= 0) {
            $errors[] = "ID partenaire invalide";
        }

        return $errors;
    }

    public function create($nom, $description, $cout, $date_fin, $id_partenaire) {
        $validationErrors = $this->validateRecompenseData($nom, $description, $cout, $date_fin, $id_partenaire);
        
        if (!empty($validationErrors)) {
            throw new Exception(implode("\n", $validationErrors));
        }

        try {
            // Vérifier que le partenaire existe
            $partenaireQuery = "SELECT id_part FROM partenaire WHERE id_part = ?";
            $partenaireStmt = $this->pdo->prepare($partenaireQuery);
            $partenaireStmt->execute([$id_partenaire]);
            
            if (!$partenaireStmt->fetch()) {
                throw new Exception("Le partenaire spécifié n'existe pas");
            }

            $query = "INSERT INTO recompense (nom_rec, description, cout, date_fin, id_part) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$nom, $description, $cout, $date_fin, $id_partenaire]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de la récompense: " . $e->getMessage());
        }
    }

    public function read($id) {
        try {
            $query = "SELECT r.*, p.nom_part FROM recompense r 
                     JOIN partenaire p ON r.id_part = p.id_part 
                     WHERE r.id_rec = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                throw new Exception("Récompense non trouvée");
            }
            
            $this->model->setIdRec($data['id_rec']);
            $this->model->setNomRec($data['nom_rec']);
            $this->model->setDescription($data['description']);
            $this->model->setCout($data['cout']);
            $this->model->setDateFin($data['date_fin']);
            $this->model->setIdPart($data['id_part']);
            
            return $this->model;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la lecture de la récompense: " . $e->getMessage());
        }
    }

    public function update($id, $nom, $description, $cout, $date_fin, $id_partenaire) {
        $validationErrors = $this->validateRecompenseData($nom, $description, $cout, $date_fin, $id_partenaire);
        
        if (!empty($validationErrors)) {
            throw new Exception(implode("\n", $validationErrors));
        }

        try {
            // Vérifier que le partenaire existe
            $partenaireQuery = "SELECT id_part FROM partenaire WHERE id_part = ?";
            $partenaireStmt = $this->pdo->prepare($partenaireQuery);
            $partenaireStmt->execute([$id_partenaire]);
            
            if (!$partenaireStmt->fetch()) {
                throw new Exception("Le partenaire spécifié n'existe pas");
            }

            $query = "UPDATE recompense SET nom_rec = ?, description = ?, cout = ?, date_fin = ?, id_part = ? WHERE id_rec = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$nom, $description, $cout, $date_fin, $id_partenaire, $id]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour de la récompense: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM recompense WHERE id_rec = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de la récompense: " . $e->getMessage());
        }
    }

    public function listAll() {
        try {
            $query = "SELECT r.*, p.nom_part FROM recompense r 
                     JOIN partenaire p ON r.id_part = p.id_part";
            $stmt = $this->pdo->query($query);
            
            $recompenses = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $recompense = new Recompense();
                $recompense->setIdRec($data['id_rec']);
                $recompense->setNomRec($data['nom_rec']);
                $recompense->setDescription($data['description']);
                $recompense->setCout($data['cout']);
                $recompense->setDateFin($data['date_fin']);
                $recompense->setIdPart($data['id_part']);
                $recompenses[] = $recompense;
            }
            
            return $recompenses;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des récompenses: " . $e->getMessage());
        }
    }
}
?>
