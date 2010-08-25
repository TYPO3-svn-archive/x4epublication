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
 * Plugin 'Publications edit' for the 'x4epublications' extension.
 *
 * @author	Markus Stauffiger <markus@4eyes.ch>
 */


require_once(t3lib_extMgm::extPath('x4epibase').'class.x4epibase.php');
class tx_x4epublication_pi2 extends x4epibase {
	var $prefixId = 'tx_x4epublication_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_x4epublication_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey = 'x4epublication';	// The extension key.
	var $pi_checkCHash = TRUE;

	/**
	 * Array of the publication to be edited
	 *
	 * @var array
	 */
	var $record = array();

	/**
	 * List of authors uids
	 * @var array
	 */
	var $authorUids = array();

	/**
	 * Heading of the content
	 *
	 * @var string
	 */
	var $contentHeading = '';

	/**
	 * Activate redirect
	 *
	 * @var boolean
	 */
	var $doRedirect = true;

	/**
	 * Name of the table containing the persons
	 *
	 * @var string
	 */
	var $personTable = 'tx_x4epersdb_person';

	/**
	 * Name of the person extension
	 *
	 * @var string
	 */
	var $personExtKey = 'x4epersdb';

	/**
	 * Instance of the person extension
	 * @var object
	 */
	var $persDbInstance = null;

	/**
	 * Name of the table containing the relations between authors (persons)
	 * and publications
	 * @var string
	 */
	var $authorMMTable 	  = 'tx_x4epublication_publication_persons_auth_mm';

	/**
	 * Name of the table containing the relations between publishers (persons)
	 * and publications
	 * @var string
	 */
	var $publisherMMTable = 'tx_x4epublication_publication_persons_publ_mm';

	/**
	 * Instance of the pi1 publication
	 * @var object
	 */
	var $pi1;

	/**
	 * Table name of the main categories
	 *
	 * @deprecated main category is never used
	 * @var string
	 */
	var $mainCatTable = 'tx_x4epublication_category_main';

	/**
	 * Table name of the subcategories
	 * @var string
	 */
	var $subCatTable = 'tx_x4epublication_category_sub';

	/**
	 * Table name of the languages
	 * @var string
	 */
	var $langTable = 'static_languages';

	/**
	 * Table name of the publications
	 * @var string
	 */
	var $table = 'tx_x4epublication_publication';

	/**
	 * Initalizes variables and returns the desired view
	 *
	 * @param string $content
	 * @param array $conf typoscript configuration array
	 *
	 * @return string HTML formatted output of the view
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->template = $this->cObj->fileResource($conf['templateFile']);
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
			// load author to display in heading
		if (intval($_GET['tx_'.$this->personExtKey.'_pi1']['showUid'])>0) { // added if statement to prevent template-errors
			$author = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'uid = '.intval($_GET['tx_'.$this->personExtKey.'_pi1']['showUid']).$this->cObj->enableFields($this->personTable));
			$this->makePersDbInstance();
        	$this->contentHeading = $this->persDbInstance->addPersonToPageTitle($author[0],1,$this->cObj->getSubpart($this->template,'###pageTitle###'));
        	unset($author);
		}
       	$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '
			<link href="'.$this->conf['stylesheet'].'" rel="stylesheet" type="text/css" />';
			// add fvalidate javascripts to use for validation
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey].='
				<script type="text/javascript" src="typo3conf/ext/'.$this->extKey.'/templates/code.js"></script>';
		switch ($this->piVars['action']) {
			case 'showNewForm':
				$this->contentHeading .= $this->pi_getLL('headingNew');
				if (is_numeric($this->piVars['category_sub'])) {
					$this->record['category_sub'] = $this->piVars['category_sub'];
					$content = $this->getEditForm();
				} else {
					$content = $this->getSubCatSelection();
				}
			break;
			case 'searchAuthor':
				$GLOBALS['TSFE']->additionalHeaderData[$this->extKey].='
					<script type="text/javascript" src="typo3conf/ext/'.$this->extKey.'/templates/code.js"></script>';
				$content = $this->displaySearchAuthor();
			break;
			case 'searchPublisher':
				$GLOBALS['TSFE']->additionalHeaderData[$this->extKey].='
					<script type="text/javascript" src="typo3conf/ext/'.$this->extKey.'/templates/code.js"></script>';
				$content = $this->displaySearchPublisher();
			break;
			case 'saveForm':
				$this->piVars['uid'] = $this->createRecord();
				$this->redirect($this->getTSFFvar('returnPageUid'));
				return '';
			break;
			case 'update':
				if (isset($this->piVars['uid'])) {
					$this->updateRecord();
				}

					// if user has only changed the type of publication no redirection is needed
				if ($this->doRedirect) {
					$this->redirect($this->getTSFFvar('returnPageUid'));
				} else {
					$this->contentHeading .= $this->pi_getLL('headingEdit');
					$content = $this->editRecord();
				}
			break;
			case 'edit':
				$this->contentHeading .= $this->pi_getLL('headingEdit');
				$content = $this->editRecord();
			break;
			default:
				$this->contentHeading .= $this->pi_getLL('headingNew');
				$content = $this->getSubCatSelection();
			break;
		}
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Redirects after creation of record, exits the script
	 *
	 * @param int $uid Uid of the page to redirect to
	 * @return void
	 */
	function redirect($uid) {
		// clears page cache
		require_once(PATH_t3lib.'class.t3lib_tcemain.php');
		t3lib_TCEmain::clear_cacheCmd($uid);

		$param['tx_'.$this->personExtKey.'_pi1[showUid]'] = $_GET['tx_'.$this->personExtKey.'_pi1']['showUid'];
		header("Location: http://".$_SERVER['HTTP_HOST'].'/'.$this->pi_getPageLink($uid,'',$param).'#publ_'.$this->piVars['uid']);
		exit();
	}


	/**
	 * Handles the editing of a record,
	 *
	 * @return string HTML formatted editing form for the publication
	 */
	function editRecord() {
		$this->checkPermission();
		$this->loadRecord();
		return $this->getEditForm();
	}

	/**
	 * Handles updload of file
	 *
	 * @todo use t3lib-filehandling
	 *
	 * @param string $tmpName
	 * @param string $fName
	 * @param int $uid
	 * @return string target path
	 */
	function handleUpload($tmpName,$fName,$uid){
		if(!is_dir('uploads/'.$this->extKey.'/'.$uid.'/')){
			mkdir('uploads/'.$this->extKey.'/'.$uid.'/');
		}
		$targetPath='uploads/'.$this->extKey.'/'.$uid.'/'.utf8_encode(preg_replace("/[^a-zäöüÄÖÜ\.0-9-]/i","_",basename($fName)));
		if(move_uploaded_file($tmpName, $targetPath)){
			return($targetPath);
		}
		return(NULL);
	}

	/**
	 * Updates the record
	 *
	 * @return 	string	HTML-String from a template (or empty)
	 */
	function updateRecord() {
		if (intval($this->piVars['uid']) > 0) {
				// maybe only updating doctype
			if (($this->piVars['title'] == '') && ($this->piVars['changePublType'] == 1)) {
				if (intval($this->piVars['category_sub']) > 0) {;
					$upd['category_sub'] = intval($this->piVars['category_sub']);
					$upd['tstamp'] = time();
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,'uid='.intval($this->piVars['uid']),$upd);
					$this->doRedirect = false;
				}
				return '';
			} else {
				global $TCA;
				t3lib_div::loadTCA($this->table);
				$upd = array();
				// loop over all recieved values
				foreach($this->piVars as $key => $value) {
					// if value is a valid colum, add to insert array
					if (isset($TCA[$this->table]['columns'][$key])) {
						$upd[$key] = $value;
					}	
					if (($key == 'authors_ext') || ($key == 'publishers_ext')){
						$upd[$key] = implode("\n",t3lib_div::trimExplode("\n",$value,1));
					}
				}
				unset($key,$value);

				// only one file allowed, if there is already one, delete that file first
				$rows=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('file_ref','tx_x4epublication_publication','uid ='.intval($this->piVars['uid']));
				if($rows[0]['file_ref']!=''){
					unlink($rows[0]['file_ref']);
				}
				unset($rows);

				// handle file uploads
				if($_FILES[$this->prefixId]['name']['file_ref']!=''){
					$uniqueFilename = $this->handleUpload($_FILES[$this->prefixId]['tmp_name']['file_ref'], $_FILES[$this->prefixId]['name']['file_ref'],$this->piVars['uid']);
					if($uniqueFilename!=NULL){
						$upd['file_ref']=$uniqueFilename;
					}
				}

				$uid['tstamp'] = time();

				// add external authors
				$upd['author_sorting'] = $this->piVars['authors'];
				$upd['publisher_sorting'] = $this->piVars['publishers'];

					// update record
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,'uid='.intval($this->piVars['uid']),$upd);
				unset($key,$value,$upd);

				$this->setPersonPublicationRelations('author');
				$this->setPersonPublicationRelations('publisher');
				
				$this->record = $this->pi_getRecord($this->table,$this->piVars['uid']);
				if($this->piVars['deletefile']!='' && $_FILES[$this->prefixId]['name']['file_ref']==''){
					// delete existing file
					unlink($this->record['file_ref']);
					$upd['file_ref']='';
				}

				$this->record = $this->pi_getRecord('tx_x4epublication_publication',$this->piVars['uid']);
				
				$this->initPublicPi1();
				$upd['author_sorting_text'] = $this->getSortingColumn();
				$upd['publisher_sorting_text'] = $this->getSortingColumn(true);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,'uid='.intval($this->piVars['uid']),$upd);
				
				$this->clearCache();
				return $this->cObj->getSubpart($this->template,'###creationSuccessful###');
			}
		} else {
			$this->clearCache();
			return '';
		}
		
	}
	
	/**
	 * Sets the correct relations between persons and publications
	 *
	 * @param string $kindOfPerson		Either "author" or "publisher"
	 * @return void
	 */
	function setPersonPublicationRelations($kindOfPerson) {
		
		if ($kindOfPerson=='publisher') {
			$mmTable = $this->publisherMMTable;
		} else {
			$mmTable = $this->authorMMTable;
		}
		
			// first delete existing relations
		$GLOBALS['TYPO3_DB']->exec_DELETEquery($mmTable,'uid_local='.intval($this->piVars['uid']));
				
		$temp = t3lib_div::trimExplode(',',$this->piVars[$kindOfPerson.'s'],1);
		$ins['uid_local'] = intval($this->piVars['uid']);
		foreach($temp as $t) {
			$ins['uid_foreign'] = $t;
			// add author <-> publication relations
			$GLOBALS['TYPO3_DB']->exec_INSERTquery($mmTable,$ins);
		}

	}

	/**
	 * Returns the list of authors, ordered by their sorting to provide
	 * a quick sorting according to authors
	 * 
	 * @author Markus
	 * @param boolean $publisher
	 * @return string	unformatted, ordered string of authors
	 */
	function getSortingColumn($publisher = false) {
		$this->pi1->internal['currentRow'] = $this->record;
		if (!isset($this->pi1->types[$this->record['category_sub']]['template'])) {
			$this->pi1->types[$this->record['category_sub']]['template'] = $this->cObj->getSubpart($this->pi1->publicationT,'###publ_'.$this->record['category_sub'].'###');
		}
		return trim(html_entity_decode(strip_tags($this->pi1->getOrderedAuthors($this->record,$publisher))));
	}
	
	/**
	 * Clears cache of sites defined in page-ts-config
	 *
	 * @todo check if there is not a better way (e.g. tcemain to delete the
	 * whole cache)
	 *
	 * @return	void
	 */
	function clearCache() {
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pagesection','');
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_hash','');
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pages','');
	}

	/**
	 * Loads the record and puts it into the member variable
	 *
	 * @return void
	 */
	function loadRecord() {
		$this->record = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->table,'uid = '.intval($this->piVars['uid']).$this->cObj->enableFields($this->table));
		$this->record = $this->record[0];
	}

	/**
	 * Checks wether active user is author of selected publication, dies
	 * if invalid permissions
	 *
	 * @return void
	 */
	function checkPermission() {
		$activePerson = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'feuser_id = '.intval($GLOBALS['TSFE']->fe_user->user['uid']).$this->cObj->enableFields($this->personTable));
		$activePerson = $activePerson[0];
		
		$count = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(*)',$this->authorMMTable,'uid_local = '.intval($this->piVars['uid']).' AND uid_foreign = '.intval($activePerson['uid']));
			// handle invalid request
		
		$person = $this->pi_getRecord($this->table,$authorUid);
		if (($count[0]['count(*)'] == 0) && ($activePerson['publadmin'] != 1)) {
			die("You're not allowed to edit this record!");
		}
	}
	
	/**
	 * Displays the searchform with results for an author-search
	 *
	 * @return string HTML form and result of the search for authors
	 */
	function displaySearchAuthor() {
		// get template
		$this->template = $this->cObj->fileResource($this->conf['searchAuthorTemplateFile']);
		$content = $this->cObj->getSubpart($this->template,'###searchForm###');
		// replace some markers
		$this->piVars['action'] = 'searchAuthor';
		$mArr['###formAction###'] = $this->pi_linkTP_keepPIvars_url().'&type=7645';
		$mArr['###label###'] = utf8_decode($this->pi_getLL('searchAuthor.searchLabel'));
		$mArr['###submit###'] = utf8_decode($this->pi_getLL('searchAuthor.submit'));
		$mArr['###close###'] = utf8_decode($this->pi_getLL('searchAuthor.close'));
		$mArr['###authorUids###'] = $this->piVars['###authorUids###'];
		$mArr['###prefixId###'] = $this->prefixId;
		$mArr['###searchWord###'] = $this->piVars['authorSearchWord'];
		$content = $this->cObj->substituteMarkerArray($content,$mArr);
		return $content.$this->searchAuthors();
	}

	/**
	 * Runs the search and returns search-result
	 *
	 * @return string HTML formatted output of persons to choose from
	 */
	function searchAuthors() {
		// Create where statement
		if (isset($this->piVars['authorUids'])) {
			$authorSearchWord = $GLOBALS['TYPO3_DB']->escapeStrForLike($this->piVars['authorSearchWord'], $this->personTable);
			$where = 'pid IN ('.$this->getTSFFvar('authorsSysFolderUid').') AND (lastname LIKE "%'.$authorSearchWord.'%" OR firstname LIKE "%'.$authorSearchWord.'%")'.$this->cObj->enableFields($this->personTable);
			if ($this->piVars['authorUids']) {
				$ids = explode(",", $this->piVars['authorUids']);
				foreach ($ids as $k => $v){
					$ids[$k] = intval($v);
				}
				$ids = implode(",",$ids);				
				$where .= ' AND uid NOT IN ('.$ids.')';
			}
			$authors = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,lastname,firstname',$this->personTable,$where,'','lastname');
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($authors) == 0) {
				$content = $this->cObj->getSubpart($this->template,'###noResults###');
				return $this->cObj->substituteMarker($content,'###content###',utf8_decode($this->pi_getLL('searchAuthor.noResultFound')));
			} else {
				$content = $this->cObj->getSubpart($this->template,'###searchRes###');
				$rowT = $this->cObj->getSubpart($content,'###row###');
				$row = '';
				$mArr['###addLabel###'] = utf8_decode($this->pi_getLL('searchAuthor.addLabel'));
				while ($a = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($authors)) {
					$mArr['###lastname###'] = $a['lastname'];
					$mArr['###uid###'] = $a['uid'];
					$mArr['###firstname###'] = $a['firstname'];

					$row .= $this->cObj->substituteMarkerArray($rowT,$mArr);
				}
				return $this->cObj->substituteSubpart($content,'###row###',$row);
			}
		}
	}

	/**
	 * Displays the searchform with results for an publisher-search
	 *
	 * @return string HTML form and result of the search for publishers
	 */
	function displaySearchPublisher() {
		// get template
		$this->template = $this->cObj->fileResource($this->conf['searchPublisherTemplateFile']);
		$content = $this->cObj->getSubpart($this->template,'###searchForm###');
		// replace some markers
		$this->piVars['action'] = 'searchPublisher';
		$mArr['###formAction###'] = $this->pi_linkTP_keepPIvars_url().'&type=7645';
		$mArr['###label###'] = utf8_decode($this->pi_getLL('searchPublisher.searchLabel'));
		$mArr['###submit###'] = utf8_decode($this->pi_getLL('searchPublisher.submit'));
		$mArr['###close###'] = utf8_decode($this->pi_getLL('searchPublisher.close'));
		$mArr['###authorUids###'] = $this->piVars['publisherUids'];
		$mArr['###searchWord###'] = $this->piVars['publihserSearchWord'];
		$mArr['###prefixId###'] = $this->prefixId;
		$content = $this->cObj->substituteMarkerArray($content,$mArr);
		return $content.$this->searchPublishers();
	}

	/**
	 *  Runs the search and returns search-result
	 *
	 * @return string HTML formatted output of persons to choose from
	 */
	function searchPublishers() {
		// Create where statement
		if (isset($this->piVars['publisherUids'])) {
			$publisherSearchWord = $GLOBALS['TYPO3_DB']->escapeStrForLike($this->piVars['publisherSearchWord'], $this->personTable);
			$where = 'pid IN ('.$this->getTSFFvar('authorsSysFolderUid').') AND (lastname LIKE "%'.$publisherSearchWord.'%" OR firstname LIKE "%'.$publisherSearchWord.'%")'.$this->cObj->enableFields($this->personTable);
			if ($this->piVars['publisherUids']) {
				$ids = explode(",", $this->piVars['publisherUids']);
				foreach ($ids as $k => $v){
					$ids[$k] = intval($v);
				}
				$ids = implode(",",$ids);
				$where .= ' AND uid NOT IN ('.$ids.')';
			}
			$publs = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,lastname,firstname',$this->personTable,$where,'','lastname');
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($publs) == 0) {
				$content = $this->cObj->getSubpart($this->template,'###noResults###');
				return $this->cObj->substituteMarker($content,'###content###',utf8_decode($this->pi_getLL('searchPublisher.noResultFound')));
			} else {
				$content = $this->cObj->getSubpart($this->template,'###searchRes###');
				$rowT = $this->cObj->getSubpart($content,'###row###');
				$row = '';
				$mArr['###addLabel###'] = utf8_decode($this->pi_getLL('searchPublisher.addLabel'));
				while ($a = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($publs)) {
					$mArr['###lastname###'] = $a['lastname'];
					$mArr['###uid###'] = $a['uid'];
					$mArr['###firstname###'] = $a['firstname'];
					$row .= $this->cObj->substituteMarkerArray($rowT,$mArr);
				}
				return $this->cObj->substituteSubpart($content,'###row###',$row);
			}
		}
	}


	/**
	 * Creates the publication record with all corresponding relations
	 *
	 * @return integer Uid of the record created
	 */
	function createRecord() {
		global $TCA;
		t3lib_div::loadTCA($this->table);

			// hardcoded publisher!
		if ($this->piVars['category_sub'] == 3) {
		    $this->piVars['auth_publ'] = 1;
		}

		// loop over all recieved values
		foreach($this->piVars as $key => $value) {
			// if value is a valid colum, add to insert array
			if (isset($TCA[$this->table]['columns'][$key])) {
				$ins[$key] = $value;
			}
		}
		unset($key,$value);

		// handle data
		$ins['pid'] = $this->getTSFFvar('pidList');
		$ins['hidden'] = 0;
		$ins['tstamp'] = time();
		$ins['crdate'] = time();
		
		// save record and use the update query from this point on!
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->table,$ins);
		$this->record = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->table,'crdate = '.$ins['crdate']);
		$this->record = $this->record[0];
		$this->piVars['uid'] = $this->record['uid'];
		$this->updateRecord();
		
		return $this->record['uid'];
	}

	/**
	 * Returns a link with all pivars + actual user id
	 *
	 * @return string LInk
	 */
	function generateLinkWithPiAndUid() {
		foreach($this->piVars as $key => $val) {
			$param[$this->prefixId][$key] = $val;
		}
		$param['tx_'.$this->personExtKey.'_pi1[showUid]'] = $_GET['tx_'.$this->personExtKey.'_pi1']['showUid'];
		return $this->pi_getPageLink($GLOBALS['TSFE']->id,'',$param);
	}

	/**
	 * returns the edit-form according to the selected subcategory
	 *
	 * @return string HTML form for editing the record
	 */
	function getEditForm() {
		
		$this->addfValidate();
		// get columns to show
		$columnsToShow = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('show_columns',$this->subCatTable,'uid='.intval($this->record['category_sub']));
		$columnsToShow = t3lib_div::trimExplode("\n",$columnsToShow[0]['show_columns']);
		// set sorting
		$this->handlePublicationWithoutAuthorSorting();
		$this->handlePublicationWithoutAuthorSorting('publisher');
		
		// add columns to content
		foreach($columnsToShow as $c) {
			$mArr2 = array();
			$subP2 = array();
			switch($c) {
				case 'authors':
					// Add language labels
					$lbls = array(
						'addAuthor',
						'extAuthor',
						'extAuthor.name',
						'extAuthor.firstname',
						'remAuthor',
						'addInternalAuthor',
						'addExternalAuthor'
						);
					$mArr2 = $this->addLanguageLabels($lbls);
					$p['no_cache']=1;
					$p['type'] = 7645;
					$p[$this->prefixId.'[action]'] = 'searchAuthor';
					$mArr2['###searchAuthorFrameSrc###'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->pi_getPageLink($GLOBALS['TSFE']->id,'',$p);

					
					$subP2['###author###'] = $this->cObj->substituteMarkerArray($this->getPersonList('author'),$mArr2);
					$mArr2['###authors_ext###'] = trim($this->record['authors_ext']);
					$mArr2['###author_sorting###'] = $this->record['author_sorting'];
					
					$c = 'authors_sortable';
				break;
				case 'publishers':
					// Add language labels
					$lbls = array(
						'addPublisher',
						'extPublisher',
						'extPublisher.name',
						'extPublisher.firstname',
						'remPublisher');
					$mArr2 = $this->addLanguageLabels($lbls);
					$p['no_cache']=1;
					$p['type'] = 7645;
					$p[$this->prefixId.'[action]'] = 'searchPublisher';
					$mArr2['###searchPublisherFrameSrc###'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->pi_getPageLink($GLOBALS['TSFE']->id,'',$p);
					unset($lbls);
					$subP2['###publisher###'] = $this->cObj->substituteMarkerArray($this->getPersonList('publisher'),$mArr2);
					$mArr2['###publishers_ext###'] = trim($this->record['publishers_ext']);
					$mArr2['###publisher_sorting###'] = $this->record['publisher_sorting'];
					
					$c = 'publishers_sortable';
				break;
				case 'auth_publ':
					$mArr2['###checked'.$this->record['auth_publ'].'###'] = 'checked="checked"';
					$mArr2['###checked'.abs($this->record['auth_publ']-1).'###'] = '';
				break;
				case 'pub_language':
						// Add language labels
					$lbls = array(
						'pubLangSelection.heading',
						'pubLangSelection.firstSelection'
					);
					$mArr2 = $this->addLanguageLabels($lbls);
					unset($lbls);
					
					
				break;
				default:
				break;
			}
			if ($this->record['furtherAuthors'] == 1) {
				$mArr2['###furtherAuthorsChecked###'] = 'checked="checked"';
			} else {
				$mArr2['###furtherAuthorsChecked###'] = '';
			}
			if ($this->record['furtherPublishers'] == 1) {
				$mArr2['###furtherPublishersChecked###'] = 'checked="checked"';
			} else {
				$mArr2['###furtherPublishersChecked###'] = '';
			}
			$mArr2['###label###'] = utf8_decode($this->getLabel($c,$this->record['category_sub']));
			
			$mArr2['###value###'] = $this->record[$c];
			$mArr2['###example###'] = utf8_decode($this->pi_getLL($c.'Example'));
			$tmp = $this->cObj->getSubpart($this->template,'###'.$c.'###');
			
			if ($c == 'pub_language'){
				$selectT = $this->cObj->getSubpart($tmp,'###pubLangOptions###');
				$selected = $this->record['pub_language'];
				if ($selected == '') $selected = $this->conf['defaultPubLang'];
				$tmp = $this->cObj->substituteSubpart($tmp,'###pubLangOptions###',parent::generateOptionsFromTable($selectT,$this->langTable,$selected, $false, 'lg_name_local', '', ' AND uid IN ('.$this->conf['langList'].')','lg_name_local'));
			}
			
			$mArr['###fields###'] .= $this->cObj->substituteMarkerArrayCached($tmp,$mArr2,$subP2);
			
		}
		unset($mArr2);
			// add selected sub category
		if (isset($this->piVars['category_sub'])) {
			$mArr['###category_sub###'] = $this->piVars['category_sub'];
		} else {
			$mArr['###category_sub###'] = $this->record['category_sub'];
		}

			// add current user
		if (trim($this->record['author_sorting']) == '') {
			$mArr['###actualUserUid###'] = implode(',',$this->authorUids);
		}
		if (is_array($this->publisherUids)) {
			$mArr['###actualPublisherUid###'] = implode(',',$this->publisherUids);
		}

		$mArr['###oldfile###']=basename($this->record['file_ref']);
		$mArr['###oldfile_path###']=$this->record['file_ref'];
		if(basename($this->record['file_ref'])!=''){
			$mArr['###delete_marker###']=$this->cObj->getSubpart($this->template,'###file_deletion_marker_part###');
		}else{
			$mArr['###delete_marker###']='';
		}

		$temp = $this->getSubCatSelection(false);
		$mArr['###catSelectorBlock###'] = $this->cObj->getSubpart($temp,'###catSelectorBlock###');
		$mArr['###prefixId###'] = $this->prefixId;
			// add form labels and action
		$mArr['###newForm.submit###'] = utf8_encode($this->pi_getLL('editForm.submit'));
		$mArr['###newForm.back###'] = utf8_decode($this->pi_getLL('editForm.back'));
			// add piVars to parameter array
		$param = array();
		$this->piVars['action'] = 'update';
		foreach($this->piVars as $key => $val) {
			$param[$this->prefixId][$key] = $val;
		}
		unset($key,$val);
		$param['tx_'.$this->personExtKey.'_pi1[showUid]'] = $_GET['tx_'.$this->personExtKey.'_pi1']['showUid'];
		
		if (intval($this->record['uid'])>0) {
			$this->piVars['action'] = 'update';
		} else {
			$this->piVars['action'] = 'saveForm';
		}
		
		foreach($this->piVars as $key => $val) {
			$param['tx_'.$this->extKey.'_pi2'][$key] = $val;
		}
		
		$mArr['###formAction###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id,'',$param);
		unset($param);
		$param['tx_'.$this->personExtKey.'_pi1[showUid]'] = $_GET['tx_'.$this->personExtKey.'_pi1']['showUid'];
		$mArr['###backUrl###'] = $this->pi_getPageLink($this->getTSFFvar('returnPageUid'),'',$param);
		unset($key,$val);
		$mArr['###heading###'] = $this->contentHeading;
		$content = $this->cObj->getSubpart($this->template,'###newForm###');
		$mArr['###extPrefix###'] = $this->prefixId;
		 
		return $this->cObj->substituteMarkerArray($content,$mArr);
	}

	/**
	 * Returns the authors of a publication to be inserted into the publication-edit form
	 *
	 * @param string $tmpl Template to use
	 * @return string Authors
	 */
	function getAuthors($tmpl) {
			// get authors of actual publication
		$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('DISTINCT uid_foreign',$this->authorMMTable,'uid_local='.intval($this->piVars['uid']));
		$authors = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,lastname,firstname',$this->personTable,'uid IN ('.$subQ.')'.$this->cObj->enableFields($this->personTable));
		$out = '';
			// get template
		
		if ($tmpl == '') {
			$tmpl = $this->cObj->getSubpart($this->template,'###author###');
		}
		while($a = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($authors)) {
			$this->authorUids[] = $a['uid'];
			$mArr['###actualUserUid###'] = $a['uid'];
			$mArr['###actualUserName###'] = $a['lastname'];
			$mArr['###actualUserFirstname###'] = $a['firstname'];
			$out .= $this->cObj->substituteMarkerArray($tmpl,$mArr);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($authors);
		return $out;
	}

	/**
	 * Returns the authors of a publication to be inserted into the publication-edit form
	 *
	 * @todo comibine with getAuthors + one parameter
	 *
	 * @param string $tmpl Template to use
	 * @return string Authors
	 */
	function getPublishers($tmpl = '') {
			// get authors of actual publication
		$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('DISTINCT uid_foreign',$this->publisherMMTable,'uid_local='.intval($this->piVars['uid']));
		$publishers = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,lastname,firstname',$this->personTable,'uid IN ('.$subQ.')'.$this->cObj->enableFields($this->personTable));
		$out = '';
		
		// get template
		if($tmpl == ''){
			$tmpl = $this->cObj->getSubpart($this->template,'###publisher###');
		}
		while($a = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($publishers)) {
			$this->publisherUids[] = $a['uid'];
			$mArr['###actualPublisherUid###'] = $a['uid'];
			$mArr['###actualPublisherName###'] = $a['lastname'];
			$mArr['###actualPublisherFirstname###'] = $a['firstname'];
			$out .= $this->cObj->substituteMarkerArray($tmpl,$mArr);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($publishers);
		return $out;
	}

	/**
	 * Returns a select filed the sub-categories
	 *
	 * @todo check if subcat parameter is necessary, as we don't use the main category
	 *
	 * @param boolean $subCatOnly true
	 * @return string HTML select field
	 */
	function getSubCatSelection($subCatOnly = true) {
			// check if there's only one possibility
	   	$opts = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid',$this->subCatTable,'sys_language_uid = 0 AND pid IN ('.intval($this->getTSFFvar('pidList')).')'.$this->cObj->enableFields($this->subCatTable));
	   
	   	if ($subCatOnly && (count($opts) == 1) && isset($opts[0]['uid'])) {
	   		$this->piVars['category_sub'] = $opts[0]['uid'];
	   		$this->record['category_sub'] = $this->piVars['category_sub'];
			return $this->getEditForm();
	   	} else {
		
				// Get template
			$content = $this->cObj->getSubpart($this->template,'###subCatSelection###');
	
			$this->piVars['action'] = 'showNewForm';
	
				// handle differently if record already exists
			if (isset($this->record['uid'])) {
				$this->piVars['category_sub'] = $this->record['category_sub'];
				$this->piVars['action'] = 'update';
			}
	
				// Add language labels
			$lbls = array(
				'subCatSelection.heading',
				'subCatSelection.firstSelection',
				'subCatSelection.submit');
			$mArr = $this->addLanguageLabels($lbls);
			unset($lbls);
				// put all pivars into parameter (necessary to pass the showUid parameter)
			$params = array();
			$params['tx_'.$this->personExtKey.'_pi1[showUid]'] = $_GET['tx_'.$this->personExtKey.'_pi1']['showUid'];
			foreach($this->piVars as $key => $val) {
				if ($val != '') {
					$params[$this->prefixId.'['.$key.']'] = $val;
				}
			}
			$mArr['###heading###'] = $this->contentHeading;
			$mArr['###formAction###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id,'',$params);
			$mArr['###prefixId###'] = $this->prefixId;
			$content = $this->cObj->substituteMarkerArray($content,$mArr);
			unset($mArr);
	
			// get options subpart and add them
			$options = $this->cObj->getSubpart($content,'###subcatOptions###');
			return $this->cObj->substituteSubpart($content,'###subcatOptions###',$this->generateOptionsFromTable($options,$this->subCatTable,$this->piVars['category_sub']));
	   	}
	}

	/**
	 * Adds the language labels to the marker array
	 *
	 * @param array $lbls	Array of labels to translate
	 * @return array Marker array of the translated records
	 */
	function addLanguageLabels($lbls) {
		$mArr = array();
		foreach($lbls as $l) {
			$mArr['###'.$l.'###'] = utf8_decode($this->getLabel($l,$this->record['category_sub']));
		}
		return $mArr;
	}

	/**
     * Generates options for the select menu
     *
     * @param	string	$tmpl		Template to use
     * @param	array	$tableName	Name of the table where to get the info
     * @param	array	$selected	Uid of selected
     * @param	bool	$addEmpty	If true empty element will be inserted
     * @return	string				string with <options>
     */
    function generateOptionsFromTable($tmpl,$tableName,$selected='',$addEmpty=false) {
    	// get correct sql statement
    	global $TCA;
    	$labelKey = $TCA[$tableName]['ctrl']['label'];
    	$valueKey = 'uid';

    	$fields = $valueKey.','.$labelKey;
    	$where = 'hidden=0 AND '.$TCA[$tableName]['ctrl']['delete'].'=0';
    	
    	if (intval($this->getTSFFvar('pidList'))>0) {
    		$where .= ' AND pid IN ('.intval($this->getTSFFvar('pidList')).')';
    	}
    	
		$editableCats = ($this->conf['editableCats'] != '') ? $this->conf['editableCats'] : '-1' ;
		$where .= ' AND uid IN ('.$editableCats.')';
		
    	$where .= ' AND sys_language_uid = 0';
    	
    	$orderBy = $TCA[$tableName]['ctrl']['sortby'];

    	// run statement
    	$opts = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$tableName,$where,'',$orderBy);
    	// free variables
    	unset($fields,$where,$orderBy);
    	// loop the result
       	while ($opt = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($opts)) {
    		$mArr['###label###'] = $opt[$labelKey];
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
	 * Generates an instance of the person-db. Puts it into member variable
	 * to prevent regenerating instances
	 *
	 * return void
	 */
	function makePersDbInstance() {
		if ($this->persDbInstance == null) {
			require_once(PATH_typo3conf.'ext/'.$this->personExtKey.'/pi1/class.tx_'.$this->personExtKey.'_pi1.php');
			$this->persDbInstance = t3lib_div::makeInstance('tx_'.$this->personExtKey.'_pi1');
		}
	}
	
	/**
	 * Returns the correct label or example. Uses either TS-Override or* Locallang
	 *
	 * @param	string	$fieldname	Name of the current field
	 * @return 	string				Correct label
	 */
	function getLabel($fN,$subCatId) {	
		if (isset($this->conf['labelOverride.'][$subCatId.'.'][$fN])) {
			return $this->conf['labelOverride.'][$subCatId.'.'][$fN];
		} else {
			return $this->pi_getLL($fN);
		}
	}
	
	/**
	 * Fills the author_sorting field for persons without sorting
	 * 
	 * return @void
	 */
	function handlePublicationWithoutAuthorSorting($kindOfPerson='author') {
		if (($this->record[$kindOfPerson.'_sorting'] == '') && (intval($this->record['uid'])>0)) {
			$this->noSorting = true;
			$template = '###actualUserUid###,';
			
			if ($kindOfPerson == 'author') {
				$authors = $this->getAuthors($template);
			} else {
				$publishers = $this->getPublishers($template);
			}
			$authors = t3lib_div::trimExplode(',',$authors,1);
			
			$i=0;
			$externalAuthors = t3lib_div::trimExplode("\n",$this->record[$kindOfPerson.'s_ext'],1);
			foreach($externalAuthors as $a) {
					// publishers usually dont have firstnames yet!
				if ($kindOfPerson == 'publisher') {
					$p = t3lib_div::trimExplode(',',$a,1);
					if (count($p)<2) {
						$externalAuthors[$i] .= ',';
					}
				}
				$externalAuthors[$i] .= ','.($i+1);
				$i++;
				$authors[] = 'ext-'.$i;
			}
			$this->record[$kindOfPerson.'_sorting'] = implode(',',$authors);
			$this->record[$kindOfPerson.'s_ext'] = implode("\n",$externalAuthors);
			$update[$kindOfPerson.'_sorting'] = $this->record[$kindOfPerson.'_sorting'];
			$update[$kindOfPerson.'s_ext'] = $this->record[$kindOfPerson.'s_ext'];
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,'uid = '.intval($this->record['uid']),$update);
		}
	}
	
	/**
	 * Returns a sortable list of authors
	 *
	 * @param $kindOfPerson		String		Either "author" or "publisher"
	 * @return string
	 * 
	 */
	function getPersonList($kindOfPerson) {
		$tmpl = $this->cObj->getSubpart($this->template,'###'.$kindOfPerson.'s_sortable###');
		$tmpl = $this->cObj->getSubpart($tmpl,'###'.$kindOfPerson.'###');
		
		$authorArray = t3lib_div::trimExplode(',',$this->record[$kindOfPerson.'_sorting'],1);
		
		if((count($authorArray)==0) && ($kindOfPerson=='author')) {
			$authorArray[] = intval($_GET['tx_'.$this->personExtKey.'_pi1']['showUid']);
			$this->record[$kindOfPerson.'_sorting'] = intval($_GET['tx_'.$this->personExtKey.'_pi1']['showUid']);
		}
		
		$externalAuthors = $this->prepareExternalPersons('',$kindOfPerson.'s_ext');
		foreach($authorArray as $a) {
			if (intval($a)>0) {
				$author = $this->pi_getRecord($this->personTable,$a);
			} else {
				$author = $externalAuthors[$a];
			}
			$mArr['###actualUserUid###'] = $author['uid'];
			$mArr['###actualUserName###'] = $author['lastname'];
			$mArr['###actualUserFirstname###'] = $author['firstname'];
			$out .= $this->cObj->substituteMarkerArray($tmpl,$mArr);
		}
		return $out;
	}
	
	/**
	 * Gets the given field and separated the external person according to the
	 * newslines and commas
	 *
	 * $extAuthors[ext_pseudoId]['lastname'] = Lastname of the person
	 * $extAuthors[ext_pseudoId]['firstname'] = Firstnamename of the person
	 * $extAuthors[ext_pseudoId]['uid'] = Pseudo-Uid of external person
	 *
	 * @param	String	$field	Field of the current record in which the persons are stored
	 * @return	Array			Array of persons with the following structure
	 */
	function prepareExternalPersons($content='',$field='authors_ext') {
		if ($content == '') {
			$content = $this->record[$field];
		}
		$lines = t3lib_div::trimExplode("\n",$content,1);
		$extAuthors = array();
		foreach($lines as $l) {
			$explode = t3lib_div::trimExplode(',',$l);
			if ($explode[0] != '') {
				$id = array_pop($explode);

				$extAuthors['ext-'.$id]['lastname'] = $explode[0];
				$extAuthors['ext-'.$id]['firstname'] = $explode[1];
				$extAuthors['ext-'.$id]['uid'] = 'ext-'.$id;
			}
		}
		return $extAuthors;
	}
	
	/**
	 * Initializes pi1-Object
	 * 
	 * @return void
	 */
	function initPublicPi1() {
		// prepare pi1
		require_once(PATH_typo3conf.'ext/'.$this->extKey.'/pi1/class.tx_'.$this->extKey.'_pi1.php');
		$this->pi1 = t3lib_div::makeInstance('tx_'.$this->extKey.'_pi1');
		$this->pi1->cObj = $this->cObj;
		$this->pi1->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_'.$this->extKey.'_pi1.'];
		$this->pi1->init('',$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_'.$this->extKey.'_pi1.']);
		$this->pi1->loadTypes();
	}
}
?>