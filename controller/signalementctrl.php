<?php
include_once __DIR__ . '/../views/includes/config.php';
include_once __DIR__ . '/../models/signalement.php';

// Vérifier si la classe Config n'existe pas déjà
if (!class_exists('Config')) {
    class Config {
        public static function getConnection() {
            return connectDB();
        }
    }
}

// Check if this is a direct API request
if (isset($_GET['action']) && $_GET['action'] === 'get_all_json') {
    $controller = new SignalementC();
    header('Content-Type: application/json');
    echo json_encode($controller->afficherSignalements());
    exit;
}

class SignalementC {
    private function showMessage($type, $message, $index = 0) {
        $top = 20 + ($index * 60);
        echo <<<HTML
        <div class="alert alert-{$type} position-fixed" 
             style="top:{$top}px; right:20px; z-index:1050; width:300px; transition: all 0.3s ease;">
            {$message}
        </div>
        HTML;
    }

    public function afficherSignalement() {
        $sql = "SELECT * FROM signalement";
        $db = Config::getConnection();
        try {
            $liste = $db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) {
            $this->showMessage('danger', '❌ Error: '.$e->getMessage());
            return [];
        }
    }

    public function addSignalement($signalement) {
        $db = Config::getConnection();

        
        if (empty($signalement->getIdSignalement())) {
            $signalement->setIdSignalement(mt_rand(1000, 9999));
        }

        try {
            $query = $db->prepare("
                INSERT INTO signalement 
                (id_signalement, titre, description, emplacement, date_signalement, statut)
                VALUES 
                (:id_signalement, :titre, :description, :emplacement, NOW(), :statut)
            ");

            $success = $query->execute([
                ':id_signalement' => $signalement->getIdSignalement(),
                ':titre' => $signalement->getTitre(),
                ':description' => $signalement->getDescription(),
                ':emplacement' => $signalement->getEmplacement(),
                ':statut' => $signalement->getStatut()
            ]);

            if ($success) {
                $this->showMessage('success', '✅ Signalement ajouté avec succès !');
                return true;
            }
            
            $this->showMessage('danger', '❌ Erreur lors de l\'ajout du signalement');
            return false;

        } catch (PDOException $e) {
            $this->showMessage('danger', '❌ Erreur: '.htmlspecialchars($e->getMessage()));
            return false;
        }
    }

    public function deleteSignalement($id_signalement) {
        $db = Config::getConnection();
        try {
            $query = $db->prepare("DELETE FROM signalement WHERE id_signalement = :id_signalement");
            $query->execute([':id_signalement' => $id_signalement]);
            $this->showMessage('success', '✅ Signalement supprimé !');
            return true;
        } catch (PDOException $e) {
            $this->showMessage('danger', '❌ Erreur: '.htmlspecialchars($e->getMessage()));
            return false;
        }
    }

    public function updateSignalement($signalement) {
        $db = Config::getConnection();
        $errors = [];

        if (empty($signalement->getIdSignalement())) {
            $errors[] = "L'ID est obligatoire";
        } elseif (!ctype_digit($signalement->getIdSignalement())) {
            $errors[] = "L'ID doit être un nombre";
        }

        if (empty($signalement->getTitre())) {
            $errors[] = "Le titre est obligatoire";
        } elseif (!preg_match('/[a-zA-ZÀ-ÿ]/u', $signalement->getTitre())) {
            $errors[] = "Le titre doit contenir des lettres";
        }

        if (empty($signalement->getDescription())) {
            $errors[] = "La description est obligatoire";
        } elseif (!preg_match('/[a-zA-ZÀ-ÿ]/u', $signalement->getDescription())) {
            $errors[] = "La description doit contenir des lettres";
        }

        if (empty($signalement->getEmplacement())) {
            $errors[] = "L'emplacement est obligatoire";
        }
        if (empty($signalement->getDateSignalement())) {
            $errors[] = "La date est obligatoire";
        }
        if (empty($signalement->getStatut())) {
            $errors[] = "Le statut est obligatoire";
        }

        if (!empty($errors)) {
            foreach ($errors as $i => $error) {
                $this->showMessage('danger', '❌ '.htmlspecialchars($error), $i);
            }
            return false;
        }

        try {
            $query = $db->prepare("
                UPDATE signalement SET
                    titre = :titre,
                    description = :description,
                    emplacement = :emplacement,
                    date_signalement = :date_signalement,
                    statut = :statut
                WHERE id_signalement = :id_signalement
            ");

            $success = $query->execute([
                ':id_signalement' => $signalement->getIdSignalement(),
                ':titre' => $signalement->getTitre(),
                ':description' => $signalement->getDescription(),
                ':emplacement' => $signalement->getEmplacement(),
                ':date_signalement' => $signalement->getDateSignalement(),
                ':statut' => $signalement->getStatut()
            ]);

            if ($success) {
                $this->showMessage('success', '✅ Signalement modifié !');
                return true;
            }
            
            $this->showMessage('danger', '❌ Erreur lors de la modification');
            return false;

        } catch (PDOException $e) {
            $this->showMessage('danger', '❌ Erreur: '.htmlspecialchars($e->getMessage()));
            return false;
        }
    }

    public function rechercher($id) {
        $db = Config::getConnection();
        try {
            $query = $db->prepare("SELECT * FROM signalement WHERE id_signalement = :id");
            $query->execute(['id' => $id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            $this->showMessage('danger', '❌ Erreur: '.htmlspecialchars($e->getMessage()));
            return [];
        }
    }

    public function afficherSignalements() {
        $db = Config::getConnection();
        try {
            $query = $db->query("SELECT * FROM signalement");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->showMessage('danger', '❌ Erreur: '.htmlspecialchars($e->getMessage()));
            return [];
        }
    }
}
?>