<?php
require('fpdf.php');

$pdf = new FPDF();
$pdf->AddFont('Helvetica','','Helvetica.php');
$pdf->AddPage();
$pdf->SetFont('Helvetica','',35);
$pdf->Write(10,'Enjoy new fonts with FPDF!');
$pdf->Ln(10); 
$pdf->SetFont('Arial','',35);
$pdf->Write(10,'Enjoy new fonts with FPDF!');
$pdf->Output();
?>