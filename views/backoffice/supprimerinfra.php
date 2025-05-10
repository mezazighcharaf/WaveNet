<?php
include_once "../../Controller/infraC.php";

if (isset($_GET['id'])) {
    $infraC = new infraC();
    $infraC->supprimerInfrastructure($_GET['id']);
    header('Location: backinfra.php');
    exit();
}
?>