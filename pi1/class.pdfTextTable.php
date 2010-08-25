<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Markus Stauffiger (markus@4eyes.ch)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'Publications view / PDF Output' for the 'x4epublication' extension.
 *
 * @author	Markus Stauffiger <markus@4eyes.ch>
 */

define('FPDF_FONTPATH', t3lib_extMgm::extPath('x4epublication').'ufpdf/font/');
require_once(t3lib_extMgm::extPath('x4epublication').'ufpdf/class.pdfTable.php');
require_once(PATH_tslib.'/class.tslib_content.php');
class pdfTextTable extends pdfTable {
	/**
	 * Path to logo, currently not in use
	 * if used again, use configurable logo
	 * @var string
	 */
	var $PDFLogo = 'fileadmin/histsem/_templates/images/pdf_logo.png';

	/**
	 * Number of rows
	 * @var integer
	 */
	var $numRow;

	/**
	 * Width of content (in pixels)
	 * @var integer
	 */
	var $contentWidth;

	/**
	 * Header text
	 * @var string
	 */
	var $headerText;
	

	/**
	 * Flag if template was cropped
     *
     * @var boolean
     */
    var $_firstPageCutted = false;

    /**
     * Id of the current template
     *
     * @var int
     */
    var $_tplIdx = null;

	/**
	 * Adds the header text and logo to the page, currently not in use
	 *
	 * @return void
	 */
	function Header() {
	    //Logo
	    //$logoW = 188;
	    //$this->Image($this->PDFLogo,$this->contentWidth-$logoW,0,$logoW);
	    /*$this->Ln(30);
	    //Arial bold 15
	    $this->SetFont('Arial','B',12);
	    //Title
	    $this->Cell(600,20,$this->headerText,0,0,'L');
	    //Line break
	    $this->Ln(20);*/
	}

	/**
	 * Adds the logo to the page, currently not in use
	 *
	 * @return void
	 */
	function addLogo() {
		if (is_file($this->PDFLogo)) {
			$logoW = 188;
	    	$this->Image($this->PDFLogo,$this->contentWidth-$logoW,0,$logoW);
		}
	}

	/**
	 * Adds the title of the subcategory to the pdf file
	 *
	 * @param string $title Title of the publication category
	 */
	function categoryTitle($title) {
		$this->Ln(30);
	    //Arial bold 15
	    $this->SetFont('FreeSansBold','',12);
	    //Title
	    $this->Cell(600,20,$title,0,0,'L');
	    //Line break
	    $this->Ln(20);
	}

    /**
	 * Adds a table row using the text-method and a rectangle to enable
	 * alternating columns
     *
     * @param array $row
     * @return boolean
     */
    function addRow($row) {
        if (!$this->_inTable)
            return false;

        if ($this->useFill != 0)  {
            $this->_setRowColor();
            if (is_null($this->_totalRowWidth))
                $this->_totalRowWidth = array_sum($this->cols);
            if ($this->y+$this->rowHeight>$this->PageBreakTrigger)
                $this->AddPage();
            $this->Rect($this->x, $this->y, $this->_totalRowWidth, $this->rowHeight, 'F');
        }

        $_x = $this->x = $this->lMargin;
        $_x += $this->cMargin;

        if ($this->y+$this->rowHeight*3>$this->PageBreakTrigger)
            $this->AddPage();

        $desc = ($this->FontSize*0.3);
        foreach ($this->cols AS $colName => $width) {
        	//Output justified text
		    $this->MultiCell($width,$this->rowHeight,"\n".$row[$colName]."\n",'','L',1);
		    //Line break
		    $this->Ln($this->rowHeight/2);
        }
        //$this->Ln($this->rowHeight);
        return true;
    }

	/**
	 * Cuts the template on the first page and adds a horizontal line as a footer
     *
     * @return boolean
     */
    function Footer() {
        $this->SetY(-20);
        $this->Line($this->lMargin, $this->y, $this->lMargin+array_sum($this->cols), $this->y);
        $this->SetFont('FreeSans','',8);
        $this->Cell(0,10,'Stand: '.date('d.m.Y',time()),0,0,'L');
        $this->Cell(0,10,'Seite '.$this->PageNo().'',0,0,'R');

        return true;
    }


	/**
	 * Sets the pdf Bookmarks, so the viewer can quickly jump to the different categories
	 *
	 * @param string $txt
	 * @param integer $level
	 * @param integer $y
	 *
	 * @return void
	 */
	function Bookmark($txt,$level=0,$y=0) {
	    if($y==-1)
	        $y=$this->GetY();
	    $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
	}

	/**
	 *	Additional function necessary to place the bookmarks
	 *
	 * @return void
	 */
	function _putbookmarks() {
	    $nb=count($this->outlines);
	    if($nb==0)
	        return;
	    $lru=array();
	    $level=0;
	    foreach($this->outlines as $i=>$o)
	    {
	        if($o['l']>0)
	        {
	            $parent=$lru[$o['l']-1];
	            //Set parent and last pointers
	            $this->outlines[$i]['parent']=$parent;
	            $this->outlines[$parent]['last']=$i;
	            if($o['l']>$level)
	            {
	                //Level increasing: set first pointer
	                $this->outlines[$parent]['first']=$i;
	            }
	        }
	        else
	            $this->outlines[$i]['parent']=$nb;
	        if($o['l']<=$level and $i>0)
	        {
	            //Set prev and next pointers
	            $prev=$lru[$o['l']];
	            $this->outlines[$prev]['next']=$i;
	            $this->outlines[$i]['prev']=$prev;
	        }
	        $lru[$o['l']]=$i;
	        $level=$o['l'];
	    }
	    //Outline items
	    $n=$this->n+1;
	    foreach($this->outlines as $i=>$o)
	    {
	        $this->_newobj();
	        $this->_out('<</Title '.$this->_textstring($o['t']));
	        $this->_out('/Parent '.($n+$o['parent']).' 0 R');
	        if(isset($o['prev']))
	            $this->_out('/Prev '.($n+$o['prev']).' 0 R');
	        if(isset($o['next']))
	            $this->_out('/Next '.($n+$o['next']).' 0 R');
	        if(isset($o['first']))
	            $this->_out('/First '.($n+$o['first']).' 0 R');
	        if(isset($o['last']))
	            $this->_out('/Last '.($n+$o['last']).' 0 R');
	        $this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
	        $this->_out('/Count 0>>');
	        $this->_out('endobj');
	    }
	    //Outline root
	    $this->_newobj();
	    $this->OutlineRoot=$this->n;
	    $this->_out('<</Type /Outlines /First '.$n.' 0 R');
	    $this->_out('/Last '.($n+$lru[0]).' 0 R>>');
	    $this->_out('endobj');
	}

	/**
	 * Ads the resources and the bookmarks
	 *
	 * @return void
	 */
	function _putresources() {
	    parent::_putresources();
	    $this->_putbookmarks();
	}

	/**
	 * Adds the catalog and outlines
	 *
	 * @return void
	 */
	function _putcatalog()	{
	    parent::_putcatalog();
	    if(count($this->outlines)>0)
	    {
	        $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
	        $this->_out('/PageMode /UseOutlines');
	    }
	}
}

?>