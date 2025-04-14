<?php
class config
{
    private static $pdo = null;
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername="localhost";
            $username="root";
            $password="";
            $dbname="urbanisme";
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
}
 $query = config::getConnexion(); //  à utiliser seulement pour test manuel
?>