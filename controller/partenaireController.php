<?php

require_once(__DIR__ . '/../model/partenaire.php');

require_once(__DIR__ . '/../config.php');


class PartenaireController {
    private $model;
    private $pdo;

    public function __construct() {
        $this->model = new Partenaire();
        $this->pdo = Config::getConnexion();
    }

    private function validatePartenaireData($nom, $tel, $mail, $adresse) {
        $errors = [];

        // Validation du nom
        if (empty($nom)) {
            $errors[] = "Le nom du partenaire est obligatoire";
        } elseif (strlen($nom) > 255) {
            $errors[] = "Le nom ne doit pas dépasser 255 caractères";
        }

        // Validation du téléphone
        if (empty($tel)) {
            $errors[] = "Le téléphone est obligatoire";
        } elseif (!preg_match('/^[0-9]{8,15}$/', $tel)) {
            $errors[] = "Le téléphone doit contenir uniquement des chiffres (8-15 chiffres)";
        }

        // Validation de l'email
        if (empty($mail)) {
            $errors[] = "L'email est obligatoire";
        } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format d'email invalide";
        } elseif (strlen($mail) > 255) {
            $errors[] = "L'email ne doit pas dépasser 255 caractères";
        }

        // Validation de l'adresse
        if (empty($adresse)) {
            $errors[] = "L'adresse est obligatoire";
        } elseif (strlen($adresse) > 255) {
            $errors[] = "L'adresse ne doit pas dépasser 255 caractères";
        }

        return $errors;
    }

    public function create($nom, $tel, $mail, $adresse) {
        $validationErrors = $this->validatePartenaireData($nom, $tel, $mail, $adresse);
        
        if (!empty($validationErrors)) {
            throw new Exception(implode("\n", $validationErrors));
        }

        try {
            // Vérification de l'unicité de l'email
            $checkQuery = "SELECT id_part FROM partenaire WHERE mail = ?";
            $checkStmt = $this->pdo->prepare($checkQuery);
            $checkStmt->execute([$mail]);
            
            if ($checkStmt->fetch()) {
                throw new Exception("Un partenaire avec cet email existe déjà");
            }

            $query = "INSERT INTO partenaire (nom_part, tel, mail, adresse) VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$nom, $tel, $mail, $adresse]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création du partenaire: " . $e->getMessage());
        }
    }

    public function read($id) {
        try {
            $query = "SELECT * FROM partenaire WHERE id_part = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                throw new Exception("Partenaire non trouvé");
            }
            
            $this->model->setIdPart($data['id_part']);
            $this->model->setNomPart($data['nom_part']);
            $this->model->setTel($data['tel']);
            $this->model->setMail($data['mail']);
            $this->model->setAdresse($data['adresse']);
            
            return $this->model;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la lecture du partenaire: " . $e->getMessage());
        }
    }

    public function update($id, $nom, $tel, $mail, $adresse) {
        $validationErrors = $this->validatePartenaireData($nom, $tel, $mail, $adresse);
        
        if (!empty($validationErrors)) {
            throw new Exception(implode("\n", $validationErrors));
        }

        try {
            // Vérification de l'unicité de l'email (sauf pour l'actuel)
            $checkQuery = "SELECT id_part FROM partenaire WHERE mail = ? AND id_part != ?";
            $checkStmt = $this->pdo->prepare($checkQuery);
            $checkStmt->execute([$mail, $id]);
            
            if ($checkStmt->fetch()) {
                throw new Exception("Un autre partenaire avec cet email existe déjà");
            }

            $query = "UPDATE partenaire SET nom_part = ?, tel = ?, mail = ?, adresse = ? WHERE id_part = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$nom, $tel, $mail, $adresse, $id]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour du partenaire: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $checkQuery = "SELECT COUNT(*) FROM recompense WHERE id_part = ?";
            $checkStmt = $this->pdo->prepare($checkQuery);
            $checkStmt->execute([$id]);
            
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception("Impossible de supprimer: ce partenaire a des récompenses associées");
            }

            $query = "DELETE FROM partenaire WHERE id_part = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression du partenaire: " . $e->getMessage());
        }
    }

    public function listAll() {
        try {
            $query = "SELECT * FROM partenaire";
            $stmt = $this->pdo->query($query);
            
            $partenaires = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $partenaire = new Partenaire();
                $partenaire->setIdPart($data['id_part']);
                $partenaire->setNomPart($data['nom_part']);
                $partenaire->setTel($data['tel']);
                $partenaire->setMail($data['mail']);
                $partenaire->setAdresse($data['adresse']);
                $partenaires[] = $partenaire;
            }
            
            return $partenaires;
        } catch (PDOException $e) {
            throw new Exception("Erreur  lors de la récupération des partenaires: " . $e->getMessage());
        }
    }
}
?>
