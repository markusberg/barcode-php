<?php

$tape = isset($_POST['tape']) ? $_POST['tape'] : 'l3';

$prefix = isset($_POST['prefix']) ? strtoupper($_POST['prefix']) : '';
$startno = isset($_POST['startno']) ? $_POST['startno'] : '0000';
$checksum = ( isset($_POST['checksum']) && $_POST['checksum'] == 'true' ) ? true : false;
$borders = ( isset($_POST['borders']) && $_POST['borders'] == 'true' ) ? true : false;
$colors = ( isset($_POST['colors']) && $_POST['colors'] == 'true' ) ? true : false;
$textOrientation = ( isset($_POST['textOrientation']) && $_POST['textOrientation'] == 'horizontal' ) ? 'horizontal' : 'vertical';
$pageSize = ( isset($_POST['pageSize']) && $_POST['pageSize'] == 'letter' ) ? 'Letter' : 'A4';
$rows = ( isset($_POST['rows']) && is_numeric($_POST['rows']) ) ? $_POST['rows'] : 1;
$cols = ( isset($_POST['cols']) && is_numeric($_POST['cols']) ) ? $_POST['cols'] : 1;

$marginPageLeft = ( isset($_POST['marginPageLeft']) && is_numeric($_POST['marginPageLeft']) ) ? $_POST['marginPageLeft'] : 0;
$marginPageTop = ( isset($_POST['marginPageTop']) && is_numeric($_POST['marginPageTop']) ) ? $_POST['marginPageTop'] : 0;

$spacingCol = ( isset($_POST['spacingCol']) && is_numeric($_POST['spacingCol']) ) ? $_POST['spacingCol'] : 0;
$spacingRow = ( isset($_POST['spacingRow']) && is_numeric($_POST['spacingRow']) ) ? $_POST['spacingRow'] : 0;

// Label dimensions taken from: 
// http://www.vinastar.com/docs/tls/TLSRLS_Barcode_Labels_Specs.pdf
$wLabel = $tape == 'dlt' ? 57 : 76.2;    // max 57,2 mm & 78,2 mm
$hLabel = $tape == 'dlt' ? 21 : 15.875;   // max 20,8 mm & 15,5 mm

$wBarcode = $tape == 'dlt' ? 47 : 65;  // max 47,0 mm & 69,9 mm
$hBarcode = $tape == 'dlt' ? 14 : 10.5; // min 10,2 mm & 9,9 mm

// the radius of the curvature of the label corners
$radius = ($tape == 'dlt' ? 0 : 2.5);


$aColors = array( 0 => array(182, 40,  42),
                  1 => array(252, 227, 75),
                  2 => array(149, 196, 83),
                  3 => array(1,   165, 226),
                  4 => array(160, 167, 181),
                  5 => array(216, 125, 54),
                  6 => array(226, 121, 152),
                  7 => array(102, 166, 69),
                  8 => array(245, 182, 84),
                  9 => array(113, 88,  128) );

require_once('tcpdf/tcpdf.php');

// create new PDF document
$pdf = new TCPDF('P', 'mm', $pageSize);
$pdf->SetMargins($marginPageLeft, $marginPageTop);
$pdf->SetAutoPageBreak(false);

// set document information
$pdf->SetCreator('http://kelvin.nu/software/barcode/');
$pdf->SetAuthor('Markus Berg');
$pdf->SetTitle('Barcodes');
$pdf->SetSubject('Barcodes');
$pdf->SetKeywords('barcode, code39, dlt, lto');


$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);


// add a page
$pdf->AddPage();

// define barcode style
$style = array(
    'border' => false,
    'text' => false,
    'stretch' => false
);

$iStartNo = (int)$startno;

// Define the "boxes" that the letters and numbers will be printed in
$iBoxes = ($tape=='dlt' ? 8 : 7);
$wBox = ($wLabel-( 2*$radius ))/$iBoxes;
$hBox = $hLabel-$hBarcode;

// The text can be larger when the text orientation is vertical
$fontSize = ($hBox-2)/25.4*72;
$pdf->SetFont('helvetica', 'B');


for ($c=0; $c<$cols; $c++) {
    $pdf->setPage(1);
    for ($r=0; $r<$rows; $r++) {
        // Calculate the correct position on the page, and move to it
        $posX = $marginPageLeft + $c*( $spacingCol + $wLabel );
        $posY = $marginPageTop + $r*( $spacingRow + $hLabel );
        $pdf->SetXY($posX, $posY);

        // Truncate text to maximum allowed
        $txt = $prefix . sprintf("%0" . strlen($startno) . "d", $iStartNo++);
        $txt = substr($txt, 0, ($tape=='dlt' ? 8 : 6));

        if ($borders) {
            $pdf->RoundedRect($posX, $posY, $wLabel, $hLabel, $radius);
        }


        // Begin by drawing the [colored] boxes
        for ($i=0; $i<$iBoxes; $i++) {
            $letter = $txt[$i];
            $color = $colors && is_numeric($letter) ? $aColors[$letter] : array(255, 255,255);
            $pdf->Rect($posX+$radius+$i*$wBox, $posY+$hBarcode, $wBox, $hBox, 'DF', array(), $color);
        }
        // Print contents of boxes
        if ($textOrientation == 'vertical') {
            // Set a proper font size
            $pdf->SetFontSize($fontSize*1.6);
            // begin at bottom left, and start a rotation
            $pdf->SetXY($posX+$radius, $posY+$hLabel);
            $pdf->StartTransform();
            $pdf->Rotate(90);
        } else {
            // Set a proper font size
            $pdf->SetFontSize($fontSize*1.3);
            $pdf->SetXY($posX+$radius, $posY+$hBarcode);
        }

        foreach (str_split($txt) as $letter) {
            if ($textOrientation=='vertical') {
                $pdf->Cell($hBox, $wBox, $letter, 0, 2, 'C');
            } else {
                $pdf->Cell($wBox, $hBox, $letter, 0, 0, 'C');
            }
        }

        if ($textOrientation=='vertical') {
            $pdf->StopTransform();
        }
        if ($tape != 'dlt') {
            // Print media name in a box of its own
            $pdf->SetFontSize($fontSize);
            if ($textOrientation == 'vertical') {
                // begin at bottom left, and start a rotation
                $pdf->SetXY($posX+$radius+$wBox*6, $posY+$hLabel);
                $pdf->StartTransform();
                $pdf->Rotate(90);
                $pdf->Cell($hBox, $wBox, strtoupper($tape), 0, 0, 'C');
                $pdf->StopTransform();
            } else {
                $pdf->SetX($posX+$radius+$wBox*6);
                $pdf->Cell($wBox, $hBox, strtoupper($tape), 0, 0, 'C');
            }
            $txt .= strtoupper($tape);
        }
        $pdf->write1DBarcode( $txt, 'C39', $posX, $posY, $wLabel, $hBarcode, '', $style);
    }
}

$pdf->Output('barcodes.pdf', 'I');

