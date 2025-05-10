<?php
include_once "../../Controller/quartierC.php";

if (isset($_GET['id'])) {
    $quartierC = new quartierC();
    $quartierC->supprimerQuartier($_GET['id']);
    header('Location: Gquartier.php');
    exit();
}
?>