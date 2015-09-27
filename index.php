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



include "Page.class.php";
$page = new Page();
$page->setTitle("Barcode generator");
$page->printHeader();

?>
<style type="text/css">
th {
    text-align: right;
    white-space: nowrap;
}
#advanced {
    padding: 0.3em;
    cursor: pointer;
    font-style: italic;
    text-decoration: underline;
    color: blue;
}
#advancedBlock {
    border: 1px solid grey;
    background: #efefef;
    display: none;
    padding: 0.5em 1em;
    margin-bottom: 1em;
}
#prefix {
    text-transform: uppercase;
}
#warning {
    color: red;
    font-style: italic;
    display: none;
}
</style>

<script type="text/javascript">
function zeropad(n, width) {
    n = n + '';
    return n.length >= width ? n : new Array(width - n.length + 1).join('0') + n;
}

function toggleDisplay( sName ) {
    var domName = document.getElementById( sName );
    domName.style.display = ( domName.style.display=="block" ? "none" : "block" );
}

function toggleAdvanced() {
    if ( domAdvancedBlock.style.display=="block" ) {
        domAdvancedBlock.style.display="none";
        domAdvanced.innerHTML="Display advanced layout options";
    } else {
        domAdvancedBlock.style.display="block";
        domAdvanced.innerHTML="Hide advanced layout options";
    }
}

function reloadSample() {
    var image = document.createElement('img');
    var fileName = (domTape.value=="dlt" ? "dlt" : "lto") + "-" + domTextOrientation.value + "-" + (domColorized.checked ? "color" : "bw") + ".png";
    domSample.setAttribute("src", fileName);
}

function sanityCheck() {
    // Ensure that startno contains only digits
    var test = domStartno.value.replace(/\D/g, '');
    if (test !== domStartno.value) {
        domStartno.value = test;
    }

    // Sanity check the length of the label
    if (domPrefix.value.length + domStartno.value.length > 6) {
        domWarning.style.display="block";
    } else {
        domWarning.style.display="none";
    }
}

// Set margins for LTO layout
function setMarginsLTO() {
    document.getElementById('textOrientation').value='vertical';
    document.getElementById('cols').value=2;
    document.getElementById('rows').value=16;
    document.getElementById('marginPageLeft').value=18.5;
    document.getElementById('marginPageTop').value=22;
    document.getElementById('spacingCol').value=20.5;
    document.getElementById('spacingRow').value=0;
}
// Set margins for Super DLT layout
function setMarginsDLT() {
    document.getElementById('textOrientation').value='horizontal';
    document.getElementById('cols').value=3;
    document.getElementById('rows').value=13;
    document.getElementById('marginPageLeft').value=15;
    document.getElementById('marginPageTop').value=15;
    document.getElementById('spacingCol').value=0;
    document.getElementById('spacingRow').value=0;
}

// Reset margins if tape type is changed from Super DLT to LTO or vice versa
function tapeChange() {
    if (oldTapeType == 'dlt' && this.value != 'dlt') {
        setMarginsLTO();
    } else if (oldTapeType != 'dlt' && this.value=='dlt') {
        setMarginsDLT();
    }
    // Save the current value for next time
    oldTapeType = this.value;
    if (this.value == "custom") {
        domSuffix.value = "";
        // domSuffix.disabled = false;
        domSuffix.focus();
    } else {
        domSuffix.value = this.value.toUpperCase();
        // domSuffix.disabled = true;
    }
}

function init() {
    domAdvanced = document.getElementById('advanced');
    domAdvancedBlock = document.getElementById('advancedBlock');
    domAdvanced.addEventListener("click", toggleAdvanced, false);

    domTape = document.getElementById("tape");
    domPrefix = document.getElementById("prefix");
    domSuffix = document.getElementById("suffix");
    domStartno = document.getElementById("startno");
    domWarning = document.getElementById("warning");
    domTextOrientation = document.getElementById("textOrientation");
    domColorized = document.getElementById("colorized");

    // Display appropriate sample image depending on current selections
    domTape.addEventListener("change", tapeChange);
    domTape.addEventListener("change", reloadSample);
    domTextOrientation.addEventListener("change", reloadSample);
    domColorized.addEventListener("change", reloadSample);

    // Sanity check length of labels
    domPrefix.addEventListener("keyup", sanityCheck);
    domStartno.addEventListener("keyup", sanityCheck);

    // Reload sample image in case of back-button-press
    domSample = document.getElementById('sample');
    setMarginsLTO();
    reloadSample();
}

var domTape;
var domPrefix;
var domSuffix;
var domStartno;
var domWarning;

var domTextOrientation;
var domColorized;

var domAdvanced;
var domAdvancedBlock;

var domSample;

var oldTapeType = "l3";


window.onload = init;

</script>

<?php
$page->printMenu();
?>
        <h1>Barcode label generator</h1>
        <p>Use this form to generate barcodes for your tape library. Each LTO label can contain at most six characters
        plus a two-character media identifier (L3, L4, etc.). A DLT label is limited to six characters.</p>
        <p>All feedback is welcome.</p>
        <p>Latest update: 2015-08-04</p>
        <form name="barcodeGenerator" method="post" action="print.php">
            <table>
                <tr>
                    <th>Tape type: </th>
                    <td><select name="tape" id="tape">
                        <option value="dlt">Super DLT</option>
                        <option value="l3" selected="selected">LTO-3</option>
                        <option value="l4">LTO-4</option>
                        <option value="l5">LTO-5</option>
                        <option value="l6">LTO-6</option>
                        <option value="lw">LTO-6 Worm</option>
                        <option value="cu">Cleaning unit</option>
                        <option value="custom">Custom...</option>
                    </select>
                    <input type="text" name="suffix" id="suffix" value="L3" size="2" maxlength="2" /></td>
                </tr>
                <tr>
                    <th>Prefix: </th>
                    <td><input type="text" maxlength=6 name="prefix" id="prefix" value="ZF" /></td>
                    <td rowspan=2><div id="warning">Warning: a label can at most contain six characters</div></td>
                </tr>
                <tr>
                    <th>Starting number: </th>
                    <td><input type="text" maxlength=6 name="startno" id="startno" value="0001" /></td>
                </tr>
                </tr>
                    <th>Sample: </th>
                    <td colspan=2><img src="lto-vertical-bw.png" id="sample"/></td>
                <tr>
            </table>
            
            <p id="advanced">Display advanced layout options</p>

            <div id="advancedBlock">
                <table>
                    <!--
                    <tr>
                        <th>Checksum: </th>
                        <td><input type="checkbox" name="checksum" value="true" id="checksum" /><label for="checksum"> (not all tape libraries require this)</label></td>
                    </tr>
                    -->
                    <tr>
                        <th>Borders: </th>
                        <td><input type="checkbox" name="borders" value="true" id="borders" checked="checked" /><label for="borders"> labels are printed with borders</label></td>
                    </tr>
                    <tr>
                        <th>Colorized: </th>
                        <td><input type="checkbox" name="colorized" value="true" id="colorized" /><label for="colorized"> labels are colorized (tri-optic vibrant compatible)</label></td>
                    </tr>
                    <tr>
                        <th>Text orientation: </th>
                        <td><select name="textOrientation" id="textOrientation">
                            <option value="vertical">Vertical</option>
                            <option value="horizontal">Horizontal</option>
                        </select></td>
                    </tr>
                    <tr>
                        <th>Page size: </th>
                        <td><select name="pageSize" />
                            <option value="a4">A4</option>
                            <option value="letter">Letter</option>
                        </select></td>
                    </tr>
                    <tr>
                        <th>Columns: </th>
                        <td><input type="text" name="cols" id="cols" /></td>
                    </tr>
                    <tr>
                        <th>Rows: </th>
                        <td><input type="text" name="rows" id="rows" /></td>
                    </tr>
                    <tr>
                        <th>Page left margin: </th>
                        <td><input type="text" name="marginPageLeft" id="marginPageLeft" />mm</td>
                    </tr>
                    <tr>
                        <th>Page top margin: </th>
                        <td><input type="text" name="marginPageTop" id="marginPageTop" />mm</td>
                    </tr>
                    <tr>
                        <th>Spacing between columns: </th>
                        <td><input type="text" name="spacingCol" id="spacingCol" />mm</td>
                    </tr>
                    <tr>
                        <th>Spacing between rows: </th>
                        <td><input type="text" name="spacingRow" id="spacingRow" />mm</td>
                    </tr>
                </table>
            </div>
            <input type="submit" value="Generate PDF" />
        </form>
        <p>This software is open source, and licensed under the Apache License v2. You can download the source from github:<br />
        <code>https://github.com/markusberg/barcode</code></p>
<?php
$page->printFooter();
?>
