<?php
/**
 * Barcode
 * Online barcode generator
 * Version 1.0
 *
 * Written by Markus Berg
 *   email: markus@kelvin.nu
 *   http://kelvin.nu/software/barcode/
 *
 * Copyright 2013 Markus Berg
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

$tape = isset($_POST['suffix']) ? strtolower($_POST['suffix']) : 'l3';

$prefix = isset($_POST['prefix']) ? strtoupper($_POST['prefix']) : '';
$startno = isset($_POST['startno']) ? $_POST['startno'] : '0000';
$checksum = ( isset($_POST['checksum']) && $_POST['checksum'] == 'true' ) ? true : false;
$borders = ( isset($_POST['borders']) && $_POST['borders'] == 'true' ) ? true : false;
$colorized = isset($_POST['colorized']) && $_POST['colorized'] == 'true';
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
$wLabel = $tape == 'dlt' ? 55 : 76.2;    // max 57,2 mm & 78,2 mm
$hLabel = $tape == 'dlt' ? 21 : 15.875;   // max 20,8 mm & 15,5 mm

$wBarcode = $tape == 'dlt' ? 47 : 65;  // max 47,0 mm & 69,9 mm
$hBarcode = $tape == 'dlt' ? 15 : 10.5; // min 10,2 mm & 9,9 mm

// Padding on top of the label (above the barcode)
$paddingTop = $tape == 'dlt' ? 0 : 0;
$paddingSide = $tape == 'dlt' ? 3 : 5;

// the radius of the curvature of the label corners
$radius = $tape == 'dlt' ? 0 : 2.5;

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
    'border'    => false,
    'text'      => false,
    'stretch'   => true,
    'hpadding'  => $paddingSide
);

$iStartNo = (int)$startno;

// Define the "boxes" that the letters and numbers will be printed in
$iBoxes = $tape=='dlt' ? 6 : 7;
$wBox = ($wLabel-( 2*$radius ))/$iBoxes;
$hBox = $hLabel-$hBarcode-$paddingTop;

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
            $pdf->SetFontSize($fontSize*1.2);
            $pdf->SetXY($posX+$radius, $posY+$hBarcode+$paddingTop);
        }

        $pdf->SetFillColor(192, 128, 255);
        foreach (str_split($txt) as $letter) {
            $pdf->SetFillColorArray($colorized && is_numeric($letter) ? $aColors[$letter] : array(255, 255,255));
            if ($textOrientation=='vertical') {
                $pdf->Cell($hBox, $wBox, $letter, 1, 2, 'C', true, '', 0, true);
            } else {
                $pdf->Cell($wBox, $hBox, $letter, 1, 0, 'C', true, '', 0, true);
            }
        }

        if ($textOrientation=='vertical') {
            $pdf->StopTransform();
        }

        // Print media name in a box of its own
        if ($tape != 'dlt') {
            $pdf->SetFontSize($fontSize);
            if ($textOrientation == 'vertical') {
                // begin at bottom left, and start a rotation
                $pdf->SetXY($posX+$radius+$wBox*6, $posY+$hLabel);
                $pdf->StartTransform();
                $pdf->Rotate(90);
                $pdf->Cell($hBox, $wBox, strtoupper($tape), 1, 0, 'C', false, '', 0, true);
                $pdf->StopTransform();
            } else {
                $pdf->SetX($posX+$radius+$wBox*6);
                $pdf->Cell($wBox, $hBox, strtoupper($tape), 1, 0, 'C', false, '', 0, true);
            }
            $txt .= strtoupper($tape);
        }
        // Finish with the actual barcode
        $pdf->write1DBarcode( $txt, 'C39', $posX, $posY+$paddingTop, $wLabel, $hBarcode, '', $style);
    }
}

$pdf->Output('barcodes.pdf', 'D');

