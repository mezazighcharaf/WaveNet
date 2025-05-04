<?php
require_once __DIR__ . '/fpdf/fpdf.php';
include_once __DIR__ . '/../../controller/signalementctrl.php';

$signalementC = new SignalementC();
$liste = $signalementC->afficherSignalement();

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,utf8_decode('Liste des Signalements'),0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(20,10,'ID',1);
$pdf->Cell(50,10,'Titre',1);
$pdf->Cell(80,10,'Description',1);
$pdf->Cell(60,10,'Emplacement',1);
$pdf->Cell(30,10,'Date',1);
$pdf->Cell(30,10,'Statut',1);
$pdf->Ln();

$pdf->SetFont('Arial','',10);
foreach($liste as $signalement){
    $pdf->Cell(20,10,$signalement['id_signalement'],1);
    $pdf->Cell(50,10,utf8_decode($signalement['titre']),1);
    $pdf->Cell(80,10,utf8_decode(substr($signalement['description'],0,50)),1);
    $pdf->Cell(60,10,utf8_decode(substr($signalement['emplacement'],0,35)),1);
    $pdf->Cell(30,10,utf8_decode($signalement['date_signalement']),1);
    $pdf->Cell(30,10,utf8_decode($signalement['statut']),1);
    $pdf->Ln();
}

$pdf->Output('D', 'signalements.pdf');
exit; 