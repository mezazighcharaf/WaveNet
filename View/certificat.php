<?php
require_once '../libs/fpdf/fpdf.php';

// Vérifier si le nom a été envoyé via le formulaire
if (isset($_POST['nom'])) {
    $nom_participant = $_POST['nom'];

    // Création du PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(200, 10, 'Certificate of Participation', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(200, 10, 'This certifies that', 0, 1, 'C');
    $pdf->SetFont('Arial', 'I', 14);
    $pdf->Cell(200, 10, $nom_participant, 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(200, 10, 'has successfully participated in the event.', 0, 1, 'C');
    $pdf->Ln(20);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, 'Date: ' . date('Y-m-d'), 0, 1, 'L');

    // Générer le PDF et l'envoyer à l'utilisateur
    $pdf->Output();
    exit;  // Toujours utiliser exit() après Output() pour arrêter le script
}
?>
