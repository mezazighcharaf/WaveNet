<?php
require_once '../Model/EcoActionBackModel.php';

$model = new EcoActionBackModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // ADD
    if ($action === 'add') {
        $success = $model->addEcoAction(
            $_POST['nom'],
            $_POST['description'],
            $_POST['date'],
            $_POST['statut'],
            $_POST['points_verts'],
            $_POST['categorie']
        );
        header('Location: ../View/eco_actionsB.php?success=add');
        exit();
    }

    // UPDATE
    if ($action === 'update') {
        $success = $model->updateEcoAction(
            $_POST['id_action'],
            $_POST['nom'],
            $_POST['description'],
            $_POST['date'],
            $_POST['statut'],
            $_POST['points_verts'],
            $_POST['categorie']
        );
        header('Location: ../View/eco_actionsB.php?success=update');
        exit();
    }

    // DELETE
    if ($action === 'delete') {
        $success = $model->deleteEcoAction($_POST['id_action']);
        header('Location: ../View/eco_actionsB.php?success=delete');
        exit();
    }
}
?>
