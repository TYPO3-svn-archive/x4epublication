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
 * Text output of publications.
 *
 * @author	Markus Stauffiger <markus@4eyes.ch>
 */
header('Content-type: application/rtf');
header('Content-Disposition: attachment; filename="publExport.rtf"');
header("Content-type: text/rtf; charset=utf-8");

require('typo3conf/ext/x4epublication/pi1/class.tx_x4epublication_pi1.php');
require('typo3conf/ext/x4epersdb/pi1/class.tx_x4epersdb_pi1.php');
require_once(PATH_tslib.'class.tslib_content.php');

class txtPublics extends tx_x4epublication_pi1 {
	var $rtf;
	var $author = array();

	/**
	 * Constructor, settings variables
	 *
	 * @param object $rtf Instance of rtf object
	 * @return void
	 */
	function txtPublics($rtf) {
		$this->rtf = $rtf;
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
	}

	/**
	 * Overriding the parent's main function
	 * @param string $content
	 * @param array $conf
	 * return void
	 */
	function main($content,$conf) {
		$this->init($content,$conf);
		foreach($_GET[$this->prefixId] as $key => $value) {
			$this->piVars[$key] = $value;
		}
		if ($_GET[$this->personExtPrefix]['showUid']) {
			$this->author = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('title,lastname,firstname,title_after',$this->personTable,'uid = '.intval($_GET[$this->personExtPrefix]['showUid']).$this->cObj->enableFields($this->personTable));
			$this->author = $this->author[0];

		}
		parent::init($content,$conf);
	}

	/**
	 * Overriding row function to add a pdf-row
	 *
	 * @param integer $c Row number
	 * @return void
	 */
	function pi_list_row($c) {
		$row['publication'] = utf8_decode(trim(strip_tags(html_entity_decode($this->renderPublication($this->internal['currentRow'])))));
		if ($row['publication'] != '') {
			//file_put_contents($this->file,($row['publication']."\r\n\r\n"),FILE_APPEND);
			$this->rtf->addText('<p>'.$row['publication'].'</p><BR><BR>');
		}
	}

	/**
	 * Overriding the parent list by subcategory
	 *
	 * @todo Could surely be done without overriding such a hugh function
	 *
	 * @global array $TCA
	 * @param integr $authorUid
	 * @return void
	 */
	function listBySubCategory($authorUid=0) {
		global $TCA;
		$out = '';
		$where = '';

		$this->internal['results_at_a_time'] = 1000;
			// add subquery to get only publication which the author is involved in
		if (intval($authorUid) > 0) {
			$where = ' AND '.$this->table.'.uid IN ('.$GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->authorMMTable,'uid_foreign = '.intval($authorUid)).')';
		}

		$addOrderBy = $this->table.'.year DESC, '.$this->table.'.tstamp DESC';

		if (!intval($authorUid)) {
			$addOrderBy = $this->personTable.'.lastname, '.$this->personTable.'.firstname, '.$addOrderBy;
		}

			// only display when either search or author view
		if (intval($authorUid) > 0 || $this->piVars['submit'] || $this->piVars['showAll'] || $this->piVars['yearfrom']) {
				// get all main categories
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->subCatTable,'1'.$this->cObj->enableFields($this->subCatTable),'',$TCA[$this->subCatTable]['ctrl']['sortby']);
			
			//$res = $this->pi_exec_query('tx_publics_category_sub',0);
				// loop over main categories to get sub categories and after all, the publications
			$where .= $this->generateSearchQuery();
			if ($this->internal['orderBy'] == '') {
				$this->internal['orderBy'] = 'year';
				$this->internal['descFlag'] = 1;
			}

				// order by author in search
			if ($this->piVars['submit']) {
				$this->internal['orderBy'] = 'author';
			}
			if (intval($authorUid)==0) {
				// anpassungen mai 08
				$addOrderBy = $this->table.'.author_sorting_text,'.$this->table.'.year DESC';
			} else {
				$addOrderBy = $this->table.'.year DESC,'.$this->table.'.author_sorting_text';
			}
			while ($subCat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					// get publications
				$mm_cat['table'] = $this->personTable;
				$mm_cat['mmtable'] = $this->authorMMTable;
				unset($mm_cat);
				$publ = $this->pi_exec_query($this->table,0,$where.' AND category_sub ='.$subCat['uid'],$mm_cat,'',$addOrderBy);
					// set markers

				if ($GLOBALS['TYPO3_DB']->sql_num_rows($publ) > 0) {
					$this->addCategoryTitle($subCat['title_plural']);

					$this->pi_list_makelist($publ);
					$mArr['###subCategory###'] = $subCat['title_p'];
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($publ);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
	}

	/**
	 * Overriding to add category title to text
	 * @param string $title
	 * @return void
	 */
	function addCategoryTitle($title) {
		if (isset($this->author['name'])) {
			$this->makePersDbInstance();
			$title = $this->persDbInstance->addPersonToPageTitle($this->author,2,$this->cObj->getSubpart($this->template,'###pageTitle###')).' - '.$title;
		}
		//file_put_contents($this->file,("<p><strong>Publikationen - ".$title.'</strong>'),FILE_APPEND);
		$this->rtf->addText('<p><strong>Publikationen - '.utf8_decode($title).'</strong></p><BR><BR>');
	}

	/**
	 * Prepends fields with table name to avoid join problems
	 * @param string $table
	 * @param <type> $fieldList
	 * @return string
	 */
	function pi_prependFieldsWithTable($table,$fieldList) {
		$ret = parent::pi_prependFieldsWithTable($table,$fieldList);
		return 'DISTINCT '.$ret;
	}
}

require_once('typo3conf/ext/x4epublication/rtf/class_rtf.php');
	$rtf = new rtf("typo3conf/ext/x4epublication/rtf/rtf_config.php");

 //$file = tempnam('','publExport');
 $publ =& new txtPublics($rtf);
 $rtf->setPaperSize(5);
 $rtf->setPaperOrientation(1);
 $rtf->setDefaultFontFace(0);
 $rtf->setDefaultFontSize(24);
 $rtf->setTitle("Publikationen");
 $rtf->addColour("#000000");

 $publ->main('',$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_x4epublication_pi1.']);
 $publ->listBySubCategory($_GET['tx_x4epersdb_pi1']['showUid']);

 // We'll be outputting a rtf
	$rtf->getDocument();
	$rtf->addText(readfile($file));

?>