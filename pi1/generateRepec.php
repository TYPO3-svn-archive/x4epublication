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
 * Repec output of publications.
 *
 * @author	Moritz Bützer <moritz@4eyes.ch>
 */
require('typo3conf/ext/x4epublication/pi1/class.tx_x4epublication_pi1.php');
require_once(PATH_tslib.'class.tslib_content.php');
class generateRepec extends tx_x4epublication_pi1 {
	function generateRepec() {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
	}

	function main($content,$conf) {
		parent::init($content,$conf);
	}

	function pi_list_makelist($publ){
		$out = parent::pi_list_makelist($publ);
		
		$GLOBALS['TYPO3_DB']->sql_data_seek($publ,0);
		$this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($publ);
		return $out;
	}
	
	function renderPublication($publ){
		$data = parent::renderPublication($publ);
		switch($this->internal['currentRow']['category_sub']){
			case 67:
				t3lib_div::mkdir('uploads/RePEc/bsl/wpaper/');
				$dir = 'wpaper';
			break;
			default: 
				t3lib_div::mkdir('uploads/RePEc/bsl/default/');
				$dir = 'default';
			break;
		}
		$filename = 'uploads/RePEc/'.$dir.'/publ_'.$publ['uid'].'.rdf';
		file_put_contents($filename, $data);
	}
	
	
	
	/*
	 * displays the authors
	 */
	function getAuthors($publ,$showPublisher=false) {
		
		if ($showPublisher) {
			$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('DISTINCT uid_foreign',$this->publisherMMTable,'uid_local='.$publ['uid']);
			$authorsT = $this->cObj->getSubpart($this->types[$publ['category_sub']]['template'],'###publishers_box###');
			$authorT = $this->cObj->getSubpart($authorsT,'###publisher###');
			$external = $publ['publishers_ext'];
			$subPLabel= '###publisher###';
		} else {
			
			$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('DISTINCT uid_foreign',$this->authorMMTable,'uid_local='.$publ['uid']);
			$authorsT = $this->cObj->getSubpart($this->types[$publ['category_sub']]['template'],'###authors_box###');
			$authorT = $this->cObj->getSubpart($authorsT,'###author###');
			$external = $publ['authors_ext'];
			$subPLabel= '###author###';
		}

		$auths = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'uid IN ('.$subQ.')'.$this->cObj->enableFields($this->personTable),'','lastname,firstname');
		
		$newAuths = array();
		foreach($auths as $v){
			$newAuths[$v['lastname']] = $v;
		}
		$t = explode("\n",trim($external));

		// fill array with separated name, firstname and "publisher"-flag

		foreach($t as $a) {
			$temp = explode(',',$a);
			if (count($temp)>1) {
				$newAuths[$temp[0]] = array('lastname'=>$temp[0],'firstname'=>$temp[1]);
			}

		}
		if (count($newAuths)>1) {
			ksort($newAuths);
			usort($newAuths, array(&$this, "compareAuthors"));
		}
		$auths = $newAuths;
		/*
		// get templates
		$separatorT = str_replace("\n",'',$this->cObj->getSubpart($authorT,'###separator###'));
		$altSeparatorT = str_replace("\n",'',$this->cObj->getSubpart($authorT,'###alternative_separator###'));
		$numOfAuthors = 0;
		$authorStr = '';
			// now add all authors
		foreach($auths as $a) {
				// fill markers
			$mArr['###lastname###'] = $a['lastname'];
			if ($a['firstname'] != '') {
				$subP['###firstname_box###'] = $this->cObj->substituteMarker($this->cObj->getSubpart($authorT,'###firstname_box###'),'###firstname###',$a['firstname']);
				$subP['###firstnameShort_box###'] = $this->cObj->substituteMarker($this->cObj->getSubpart($authorT,'###firstnameShort_box###'),'###firstname###',substr($a['firstname'],0,1));
			} else {
				$subP['###firstname_box###'] = '';
			}

			if ($a['uid']) {
					// add link if page-id is set
				$p[$this->personExtPrefix.'[showUid]'] = $a['uid'];
				if ($this->getTSFFvar('authorDetailPageUid')) {
					$mArr['###linkStart###'] = str_replace('&nbsp;</a>','',$this->pi_linkTP('&nbsp;',$p,1,$this->getTSFFvar('authorDetailPageUid')));
					$mArr['###linkEnd###'] = '</a>';
				} else {
					$mArr['###linkStart###'] = str_replace('&nbsp;</a>','',$this->pi_linkTP('&nbsp;',$p,1));
					$mArr['###linkEnd###'] = '</a>';
				}
			} else {
				$mArr['###linkStart###'] = $mArr['###linkEnd###'] = '';
			}


				// Add separator if necessary
			if ($numOfAuthors > 0) {
				if (($numOfAuthors == (count($auths)-1)) && ($altSeparatorT != '')) {
					$authorStr .= $altSeparatorT;
				} else {
					$authorStr .= $separatorT;
				}
			}
				// clear separator subpart, because we don't need it
			$subP['###separator###'] = '';
			$subP['###alternative_separator###'] = '';

			$authorStr .= $this->cObj->substituteMarkerArrayCached($authorT,$mArr,$subP);
			$numOfAuthors++;
		}
		$subP = array();
		$subP[$subPLabel] = $authorStr;
		unset($authorStr);

			// add external authors
		$subP['###additional###'] = '';

			// return empty string if no author/publisher is specified
		if ($subP[$subPLabel] == '') {
			return '';
		}

		if ($publ['auth_publ']) {
			$subP['###auth_publ_box###'] = $this->cObj->getSubpart($authorsT,'###auth_publ_box###');
		} else {
			$subP['###auth_publ_box###'] = '';
		}
		return $this->cObj->substituteMarkerArrayCached($authorsT,array(),$subP);
		*/
		$count = count($newAuths);
		$i=0;
		foreach($newAuths as $author){
			if($i == $count-1){
				$authors .= 'Author-Name: '.$author['firstname']." ".$author['lastname']."\nAuthor-Email: ".$author['email'];
			}else{
				$authors .= 'Author-Name: '.$author['firstname']." ".$author['lastname']."\nAuthor-Email: ".$author['email']."\n";
			}
			$i++;
		}
		
		$mArr['###authors###'] = $authors;
		return $this->cObj->substituteMarkerArray($authorsT,$mArr);
	}	
	
	
	
}

 $publ =& new generateRepec();
 $publ->main('',$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_x4epublication_pi1.']);
 $publ->listBySubCategory($_GET['tx_x4epersdb_pi1']['showUid']);
 //echo(file_get_contents('uploads/RePEc/bsl/wpaper/publ_1006.rdf'));
?>