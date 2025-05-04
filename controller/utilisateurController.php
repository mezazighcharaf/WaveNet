<?php
require_once __DIR__ . '/../model/utilisateur.php';
require_once __DIR__ . '/../config.php';

class UtilisateurController {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    public function getUtilisateurById($id) {
        try {
            $query = "SELECT * FROM utilisateur WHERE id_utilisateur = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$id]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }
            
            $utilisateur = new Utilisateur();
            $utilisateur->setIdUtilisateur($data['id_utilisateur']);
            $utilisateur->setNom($data['nom']);
            $utilisateur->setPrenom($data['prenom']);
            $utilisateur->setEmail($data['email']);
            $utilisateur->setPointsVerts($data['points_verts']);
            
            return $utilisateur;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
        }
    }

    public function updatePointsVerts($id_utilisateur, $nouveaux_points) {
        try {
            $query = "UPDATE utilisateur SET points_verts = ? WHERE id_utilisateur = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$nouveaux_points, $id_utilisateur]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour des points verts: " . $e->getMessage());
        }
    }
}
?>