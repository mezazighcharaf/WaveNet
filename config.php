<?php
class config
{
    private static $pdo = null;
    public static function getConnection()
    {
        if (!isset(self::$pdo)) {
            $servername="localhost";
            $username="root";
            $password="";
            $dbname="gestion_signalement";
            try {
                self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname",
                    $username,
                    $password,
                );

                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // echo "connected successfully"; //  pour debug uniquement
            } catch (Exception $e) {
                die('Erreur : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
    
    // Alias pour maintenir la compatibilité avec l'ancien code
    public static function getConnexion()
    {
        return self::getConnection();
    }
}
// $query = config::getConnection(); //  à utiliser seulement pour test manuel
?>