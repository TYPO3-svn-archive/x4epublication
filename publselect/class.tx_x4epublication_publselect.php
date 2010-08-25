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
 * Plugin 'Publications select' for the 'x4epublication' extension.
 *
 * @author	Markus Stauffiger <markus@4eyes.ch>
 */

require_once(t3lib_extMgm::extPath('x4epibase').'class.x4epibase.php');
class tx_x4epublication_publselect extends x4epibase {
	var $prefixId = 'tx_x4epublication_publselect';		// Same as class name
	var $scriptRelPath = 'publselect/class.tx_x4epublication_publselect.php';	// Path to this script relative to the extension dir.
	var $extKey = 'x4epublication';	// The extension key.
	var $pi_checkCHash = TRUE;

	/**
	 * HTML-Template
	 *
	 * @var string
	 */
	var $template ='';

	/**
	 * Instance of the pi1 publication
	 * @var object
	 */
	var $pi1 = null;

	/**
	 * Person record
	 * @var array
	 */
	var $person;

	/**
	 * Name of the table containing the persons
	 *
	 * @var string
	 */
	var $personTable = 'tx_x4epersdb_person';

	/**
	 * Initalizes variables and adds stylesheet and code
	 * @param string $content
	 * @param string $conf typoscript configuration array
	 */
	function init(&$content,&$conf) {
		$this->conf=$conf;
		$this->pi_loadLL();
		$this->pi_setPiVarDefaults();
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '
			<link href="'.$this->conf['stylesheet'].'" rel="stylesheet" type="text/css" />';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey].='
					<script type="text/javascript" src="typo3conf/ext/x4epublication/templates/code.js"></script>';
	}

	/**
	 * Initalizes variables and returns the desired view
	 *
	 * @param string $content
	 * @param array $conf typoscript configuration array
	 *
	 * @return string HTML formatted output of the view
	 */
	function main($content,$conf)	{

		$this->init($content,$conf);

		$this->getPersonInfo();
		
		if(!$this->checkAccessRight()){
			return '';
		}
		
		if ($this->piVars['submit'] == $this->pi_getLL('save')) {
			$this->saveSettings();
		}

		if ($this->piVars['publicationSearch']) {
			$content = $this->view('search');
		} else {
			$content = $this->view();
		}
		$tmp2 = $GLOBALS['TSFE']->additionalHeaderData[$this->extKey];

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Returns the view according to the $view variable
	 *
	 * @param string $view
	 * @return string HTML formatted string
	 */
	function view($view='') {
		$mArr = array();
		if ($view == 'search') {
			$this->template = $this->cObj->fileResource($this->conf['publicationSearchTemplateFile']);
			$mArr['###searchForm###'] = $this->getSearchForm();
			$mArr['###searchResult###'] = $this->getSearchRes();
			$mArr['###label###'] = $this->pi_getLL('searchHeading');
			$mArr['###close###'] = $this->pi_getLL('close');
			$mArr['###prefixId###'] = $this->prefixId;
			$mArr['###formAction###'] = $this->getFormAction();
			$out = $this->cObj->getSubpart($this->template,'###searchBox###');
		} else {
			$tmp = $GLOBALS['TSFE']->additionalHeaderData[$this->extKey];
			$this->template = $this->cObj->fileResource($this->conf['templateFile']);
			$mArr = $this->getCheckboxState();
			$mArr['###formAction###'] = $this->getFormAction();
			$mArr['###prefixId###'] = $this->prefixId;
			$mArr['###saveLabel###'] = $this->pi_getLL('save');
			$mArr['###selectedPublications###'] = $this->person['tx_x4epublication_selectedpubls'];

			if ($this->checkIfAdmin()) {
				$mArr['###publAdminArea###'] = $this->renderPublAdminArea();
			} else {
				$mArr['###publAdminArea###'] = '';
			}
			$sub['###list###'] = $this->getSelectedList();

			$p[$this->prefixId.'[publicationSearch]'] = '1';
			$p['tx_x4epersdb_pi1[showUid]'] = $this->person['uid'];
			$mArr['###searchPublicationFrameSrc###'] = $this->pi_getPageLink($this->conf['publicationSearchPageUid'],'',$p);
			
			$tmpl = $this->cObj->getSubpart($this->template,'###view###');
			$out = $this->cObj->substituteMarkerArrayCached($tmpl,$mArr,$sub);

		}
		return $this->cObj->substituteMarkerArray($out,$mArr);
	}

	/**
	 * Returns a search form for the publications (including results)
	 *
	 * @return string html
	 */
	function getSearchForm(){
		$sub['###authorHeading###'] = '';
		$mArr['###searchLabel###'] = $this->pi_getLL('search');
		$mArr['###searchWord###'] = $this->piVars['sword'];
		$tmpl = $this->cObj->getSubpart($this->template,'###searchFormBox###');
		return $this->cObj->substituteMarkerArrayCached($tmpl,$mArr,$sub);
	}

	/**
	 * Returns a list of publications
	 *
	 * @return string html
	 */
	function getSearchRes(){
		$tmpl = $this->cObj->getSubpart($this->template,'###searchResBox###');
		$this->getPi1($tmpl);
		if ($this->piVars['sword']) {
			$this->pi1->piVars['sword'] = $this->piVars['sword'];
		}
		return $this->pi1->listBySubCategory($this->person['uid']);
	}

	/**
	 * Gets an instance of pi1
	 *
	 * @param $tmpl	String	Template to use
	 * @return void
	 */
	function getPi1($tmpl=''){
		if ($this->pi1 == null) {
			require_once('typo3conf/ext/x4epublication/pi1/class.tx_x4epublication_pi1.php');
			$this->pi1 = t3lib_div::makeInstance('tx_x4epublication_pi1');
			$this->pi1->cObj = $this->cObj;
			$this->conf['detailView.']['publication.']['pidList'] = $this->conf['publications']['pidList'];
			$this->pi1->init('',$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_x4epublication_pi1.']);
			$tmp = $GLOBALS['TSFE']->additionalHeaderData[$this->extKey];
			if ($tmpl != '') {
				$this->pi1->template = $tmpl;
			}
		}
	}

	/**
	 * Returns a list of selected publications
	 *
	 * @return string	HTML of rendered publications
	 */
	function getSelectedList(){
		$this->getPi1();
		$this->pi1->publicationT = $this->cObj->fileResource($this->pi1->conf['publicationTemplate']);
		$publArr = t3lib_div::trimExplode(',',$this->person['tx_x4epublication_selectedpubls'],1);
		$publT = $this->cObj->getSubpart($this->template,'###rows###');
		foreach($publArr as $p) {
			$mArr['###content###'] = $this->pi1->renderPublication($this->pi_getRecord('tx_x4epublication_publication',$p));
			$mArr['###uid###'] = $p;
			$out .= trim($this->cObj->substituteMarkerArray($publT,$mArr));
		}
		$sub['###rows###'] = trim($out);
		$tmpl = $this->cObj->getSubpart($this->template,'###list###');
		return $this->cObj->substituteMarkerArrayCached($tmpl,array(),$sub);
	}

	/**
	 * Saves the user setting
	 *
	 * @return void
	 */
	function saveSettings(){
		$upd['tx_x4epublication_displayselected'] = intval($this->piVars['displayselected']);
		$upd['tx_x4epublication_selectedpubls'] = $GLOBALS['TYPO3_DB']->quoteStr($this->piVars['selectedPublications'],$this->personTable);
		unset($this->piVars['displayselected']);
		unset($this->piVars['selectedPublications']);
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->personTable,'uid='.intval($this->person['uid']),$upd);
		$this->person['tx_x4epublication_displayselected'] = $upd['tx_x4epublication_displayselected'];
		$this->person['tx_x4epublication_selectedpubls'] = $upd['tx_x4epublication_selectedpubls'];
		$this->getPi1();
		$this->pi1->clearCache($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_x4epersdb_pi1.']['listView.']['detailPageUid']);
	}

	/**
	 * Returns the form's action (url)
	 *
	 * @return string Link for the form's action attribute
	 */
	function getFormAction(){
		$p = array();
		foreach($this->piVars as $k=>$v) {
			$p[$this->prefixId.'['.$k.']']=$v;
		}
		$p['tx_x4epersdb_pi1[showUid]'] = $this->person['uid'];
		return $this->pi_getPageLink($GLOBALS['TSFE']->id,'',$p);
	}
	
	
	
	/**
	 * Returns marker array with checkbox-state.
	 *
	 * @return array
	 */
	function getCheckboxState(){
		if ($this->person['tx_x4epublication_displayselected']) {
			$mArr['###displaySelected0###'] = '';
			$mArr['###displaySelected1###'] = 'checked="checked"';
		} else {
			$mArr['###displaySelected0###'] = 'checked="checked"';
			$mArr['###displaySelected1###'] = '';
		}
		return $mArr;
	}

	/**
	 * Sets the member variable "person"
	 *
	 * @return void
	 */
	function getPersonInfo(){
		if (!isset($_GET['tx_x4epersdb_pi1'])) {
			if(!isset($this->piVars['person_id'])){
				$id = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'feuser_id='.intval($GLOBALS['TSFE']->fe_user->user['uid']));
			}else{
				$id = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'uid='.intval($this->piVars['person_id']));
			}
			$this->person = $id[0];
		} else {
			$id = t3lib_div::_GET('tx_x4epersdb_pi1');
			$id = $id['showUid'];
			if (intval($id)>0) {
				$this->person = $this->pi_getRecord($this->personTable,$id);
			}
		}
	}

	/**
	 * Renders publication select view
	 *
	 * @return string HTML
	 */
	function renderPublAdminArea(){
		$selectSub = $this->cObj->getSubpart($this->template,'###select###');
		$optionSub = $this->cObj->getSubpart($selectSub,'###options###');
		if(isset($this->piVars['person_id'])){
			$selected = intval($this->piVars['person_id']);
		}else{
			$selected = intval($this->person['uid']);
		}
		
		$mArr['choosePerson'] = $this->pi_getPageLink($GLOBALS['TSFE']->id);
		$mArr['persons'] = $this->generateOptions($optionSub,$this->personTable,$selected,false, array('lastname','firstname'),'',' AND pid='.$this->conf['authorsSysFolderUid'],'lastname');
		return $this->cObj->substituteMarkerArray($selectSub, $mArr, '###|###');
	}

	/**
	 * Generate html form option tags
	 *
	 * @global array $TCA
	 *
	 * @param string $tmpl
	 * @param string $tableName
	 * @param string $selected
	 * @param boolean $addEmpty
	 * @param array $labelKey
	 * @param string $valueKey
	 * @param string $addWhere
	 * @param string $orderBy
	 * @return string HTML Options
	 */
	function generateOptions($tmpl,$tableName,$selected='',$addEmpty=false, $labelKey=array(), $valueKey='', $addWhere='',$orderBy='') {
    	if($tmpl == '') {
    		$tmpl = '<option value="###value###" ###selected###>###label###</option>';
    	}

    	// get correct sql statement
    	global $TCA;
    	if (!isset($labelKey[0])) {
    		$labelKey[0] = $TCA[$tableName]['ctrl']['label'];
    	}
    	if ($valueKey == '') {
    		$valueKey = 'uid';
    	}
    	
    	$fields = $valueKey;    	
		for($i=0; $i<count($labelKey); $i++){
			$fields .= ','.$labelKey[$i];
		}
    	
    	if ($valueKey != 'uid') {
    		$fields .=', uid';
    	}

    	$where = '1 '.$this->cObj->enableFields($tableName).$addWhere;

    	if ($orderBy == '') {
    		$orderBy = $TCA[$tableName]['ctrl']['sortby'];
    	}
    	// run statement
    	$opts = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$tableName,$where.$this->cObj->enableFields($tableName),'',$orderBy);
    	
    	// free variables
    	unset($fields,$where,$orderBy);

    	// loop the result
       	while ($opt = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($opts)) {
       		$mArr['###label###'] = '';
       		foreach ($labelKey as $index => $label){
       			if($index==0){
       				$mArr['###label###'] .= $opt[$label].', ';
       			}else{
       				$mArr['###label###'] .= $opt[$label];
       			}
       		}
    		$mArr['###value###'] = $opt[$valueKey];
    		if ($opt['uid'] == $selected) {
    			$mArr['###selected###'] = 'selected';
    			$chosen = true;
    		} else {
    			$mArr['###selected###'] = '';
    		}
    		$returnStr .= $this->cObj->substituteMarkerArray($tmpl,$mArr);
    	}

    	// Add empty elment at the beginning, and select if no other element is selected
    	if ($addEmpty) {
    		$mArr['###label###'] = '';
    		$mArr['###value###'] = '';
    		if (!$chosen) {
    			$mArr['###selected###'] = 'selected="selected"';
    		} else {
    			$mArr['###selected###'] = '';
    		}
    		$returnStr = $this->cObj->substituteMarkerArray($tmpl,$mArr).$returnStr;
    	}
    	return $returnStr;
    }
    
    /**
	 * Checks if person proper permissions
	 *
	 * @return boolean
	 */
    function checkAccessRight(){
    	$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'feuser_id='.intval($GLOBALS['TSFE']->fe_user->user['uid']));
    	if($res[0]['publadmin'] || $this->person['feuser_id']==intval($GLOBALS['TSFE']->fe_user->user['uid'])){
    		return true;
    	}else{
    		return false;
    	}
    }
    
    /**
	 * Checks if person is publication administrator
	 *
	 * @return boolean
	 */
    function checkIfAdmin(){
    	$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'feuser_id='.intval($GLOBALS['TSFE']->fe_user->user['uid']));
    	if($res[0]['publadmin']){
    		return true;
    	}else{
    		return false;
    	}
    }

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/publselect/class.tx_x4epublication_publselect.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/publselect/class.tx_x4epublication_publselect.php']);
}

?>