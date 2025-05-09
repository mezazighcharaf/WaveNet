<?php
require('../libs/fpdf/fpdf.php');

// Récupérer les données
$nom_participant = isset($_POST['nom']) ? trim($_POST['nom']) : 'Nom Inconnu';
$nom_action = isset($_POST['action']) ? trim($_POST['action']) : 'Action Inconnue';
$date_action = isset($_POST['date']) ? trim($_POST['date']) : date('d/m/Y');

// Créer un nouveau PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Définir les couleurs
$vert_fonce = [34, 139, 34];
$bleu = [0, 102, 204];

// Ajouter un cadre vert
$pdf->SetDrawColor(0, 128, 0);
$pdf->SetLineWidth(2);
$pdf->Rect(10, 10, 277, 190, 'D');

// Titre
$pdf->SetFont('Arial', 'B', 28);
$pdf->SetTextColor($vert_fonce[0], $vert_fonce[1], $vert_fonce[2]);
$pdf->Cell(0, 30, utf8_decode('CERTIFICAT DE PARTICIPATION'), 0, 1, 'C');

$pdf->Ln(10);

// Sous-titre
$pdf->SetFont('Arial', '', 18);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, utf8_decode('Attribué à'), 0, 1, 'C');

$pdf->Ln(5);

// Nom participant
$pdf->SetFont('Arial', 'B', 26);
$pdf->SetTextColor($bleu[0], $bleu[1], $bleu[2]);
$pdf->Cell(0, 20, strtoupper(utf8_decode($nom_participant)), 0, 1, 'C');

$pdf->Ln(10);

// Détail action
$pdf->SetFont('Arial', '', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->MultiCell(0, 10, utf8_decode("Pour sa participation active à l'action : "), 0, 'C');

$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor($vert_fonce[0], $vert_fonce[1], $vert_fonce[2]);
$pdf->MultiCell(0, 10, strtoupper(utf8_decode($nom_action)), 0, 'C');

$pdf->SetFont('Arial', '', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(5);
$pdf->MultiCell(0, 10, utf8_decode("qui a eu lieu le : " . $date), 0, 'C');

$pdf->Ln(20);

// Ligne pour signature
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Line(200, 150, 270, 150);

// Date et lieu
$pdf->SetFont('Arial', 'I', 14);
$pdf->Cell(0, 10, utf8_decode('Fait à Tunis, le ' . date('d/m/Y')), 0, 1, 'R');

$pdf->Ln(5);
$pdf->Cell(0, 10, 'Signature', 0, 1, 'R');

$pdf->Ln(15);

// Footer
$pdf->SetFont('Arial', 'I', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, utf8_decode('Urbaverse - Ensemble pour un avenir urbain durable'), 0, 0, 'C');

// Générer
$pdf->Output('I', 'certificat_participation.pdf');
?>
