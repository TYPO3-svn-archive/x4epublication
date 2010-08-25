<?php
/*
 * Created on 13.03.2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once('ufpdf.php');

class pdfTable extends UFPDF {
    /**
     * Hintergrundfarbe der Spalten�berschriften
     * Ein Array, indem der Index 0 den Rot-Wert,
     * Index 1 den Gr�n-Wert, Index 2 den Blau-Wert angibt.
     *
     * @var array
     */
    var $headColor = array(103,180,69);
    
    /**
     * Hintergrundfarbe der gerade Zeilen
     * Ein Array, indem der Index 0 den Rot-Wert,
     * Index 1 den Gr�n-Wert, Index 2 den Blau-Wert angibt.
     *
     * @var array
     */
//    var $evenColor = array(223,236,218);
    var $evenColor = array(255,255,255);
    /**
     * Hintergrundfarbe der ungeraden Zeilen
     * Ein Array, indem der Index 0 den Rot-Wert,
     * Index 1 den Gr�n-Wert, Index 2 den Blau-Wert angibt.
     *
     * @var array
     */
    var $oddColor  = array(255,255,255);
    
    /**
     * Der Pfad zum Logo im Header
     *
     * @var string
     */
    var $PDFLogo = "images/PHP_logo_gruen01.pdf";
    
    /**
     * Spaltendefinitionen
     * Der Index bestimmt den Namen und der Wert die
     * Breite der Spalte
     *
     * @var array
     */
    var $cols = array();
    
    /**
     * Die Zeilenh�he
     *
     * @var float
     */
    var $rowHeight = 14;
    
    var $_totalRowWidth = null;
    var $_inTable = false;
    var $_startTable;
    var $_endTable;
    var $_isEven = false;
    var $useFill = true;
    
    function pdfTable($oriantation='P', $unit='mm', $format='A4') {
        parent::UFPDF($oriantation, $unit, $format);
    }
    
    function setEvenColor($evenColor) {
        $this->evenColor = $evenColor;
    }
    
    function setOddColor($oddColor) {
        $this->oddColor = $oddColor;
    }
    
    /**
     * Setzt die Spalten und deren Breiten
     *
     * @param array $cols
     */
    function setCols($cols) {
        $this->cols = $cols;
    }
    
    /**
     * Definiert ob die Hintergundfarbe der einzelnen Reihen
     * dargestellt werden soll
     *
     * @param boolean $useFill
     */
    function setUseFill($useFill=true) {
        $this->useFill = $useFill;
    }
    
    /**
     * �berschreibt die Header-Methode von FPDF und ruft die
     * noch zu implementierende Methode TableHeader() auf.
     */
    function Header() {
        $this->_TableHeader();
    }
    
    /**
     * �berschreibt die Footer-Methode von FPDF und ruft die
     * noch zu implementierende Methode TableFooter() auf.
     */
    function Footer() {
        $this->_TableFooter();
    }
    
    /**
     * Wird von den abgeleiteten Klassen �berschrieben.
     */
    function _TableHeader() { }
    
    function _TableFooter() { }
    
    /**
     * Leitet den Beginn einer Tabelle ein, setzt diverse
     * Statusflags und ruft die TableHeader-Methode auf
     *
     * @return boolean
     */
    function BeginTable() {
        if (count($this->cols) == 0)
            return false;
        if ($this->page == 0)
            $this->AddPage();
        $this->_startTable = microtime();
        $this->_inTable = true;
        $this->_isEven = false;
        $this->_totalRowWidth = null;
        $this->_TableHeader();
        return true;
    }
    
    /**
     * Wird von den abgeleiteten Klassen �berschrieben.
     *
     * @param array $row
     */
    function addRow($row) { }
    
    /**
     * Wechselt die Hintergrundfarbe.
     *
     * @param boolean $i
     */
    function _setRowColor() {
        $bgColor = ($this->_isEven = !$this->_isEven) ? $this->oddColor : $this->evenColor;   
        $this->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]); 
    }
    
    /**
     * Beendet die Tabelle und setzt diverse Statusflags zur�ck.
     *
     * @return boolean
     */
    function EndTable() {
        if ($this->_inTable) {
            $this->_inTable = false;
            $this->_endTable = microtime();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Eine kleine Hilfsmethode, die eine die Laufzeit der Tabellenerstellung
     * in das PDF schreibt.
     *
     * @param string $txt
     * @param float $time
     * @return string
     */
    function WriteTime($txt, $time=null) {
        if (is_null($time)) {
            list($usec, $sec) = explode(" ", $this->_startTable);
            $start = ((float)$usec + (float)$sec);
            list($usec, $sec) = explode(" ", $this->_endTable);
            $end = ((float)$usec + (float)$sec);
            $time = $end - $start;
        }
        
        $this->Cell(0,20,sprintf($txt,$time),0,1);
        return $time;
    }
}

?>