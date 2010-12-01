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
 * Plugin 'Publications view' for the 'x4epublication' extension.
 *
 * @author	Markus Stauffiger <markus@4eyes.ch>
 */


require_once(t3lib_extMgm::extPath('x4epibase').'class.x4epibase.php');
class tx_x4epublication_pi1 extends x4epibase {
	var $prefixId = 'tx_x4epublication_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_x4epublication_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'x4epublication';	// The extension key.
	var $pi_checkCHash = TRUE;

	/**
	 * changes template for author_sorting
	 * @var boolean
	 */
	var $author_sorting_creation = false;

	/**
	 * if mode = edit, the edit icons will be shown
	 * @var boolean
	 */
	var $editMode = false;

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
	 * ExtPrefix of the person database plugin
	 * @var string
	 */
	var $personExtPrefix = 'tx_x4epersdb_pi1';

	/**
	 * Name of the table containing the persons
	 * @var string
	 */
	var $personTable = 'tx_x4epersdb_person';
	
	/**
	 * Name of the table containing the mm relations between persons and departments
	 * @var string
	 */
	var $personDepMMTable = 'tx_x4epersdb_person_department_mm';

	/**
	 * Instance of the person plugin
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
	 * Array of types (uid is key, value is comma-separated list of fields to
	 * display)
	 * This is used to avoid loading the types multiple times from the database.
	 * contains the template subpart as well
	 *
	 * @var array
	 */
	var $types = array();

	/**
	 * Returns true if given uid (= person) has publications
	 *
	 * @param 	int		$personUid		Uid of person
	 * @return 	boolean
	 */
	function hasPublication($personUid) {
		$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->authorMMTable,'uid_foreign='.intval($personUid));
		$q = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(*)',$this->table,'uid IN ('.$subQ.')'.$this->cObj->enableFields($this->table));
		if ($q[0]['count(*)'] > 0) {
			return true;
		}
		return false;
	}

	/**
	 * Sets intial variables
	 *
	 * @param string $content
	 * @param array $conf typoscript configuration array
	 * @return void
	 */
	function init($content,$conf) {
		$this->conf=$conf;

		/**
		 * Init parameters
		 */
		if ($this->conf['extKey']) {
			$this->extKey = $this->conf['extKey'];
			$this->prefixId = 'tx_'.$this->extKey.'_pi1';
			$this->scriptRelPath = 'pi1/class.tx_'.$this->extKey.'_pi1.php';

			$this->table = 'tx_'.$this->extKey.'_publication';
			$this->subCatTable = 'tx_'.$this->extKey.'_category_sub';
			$this->mainCatTable = 'tx_'.$this->extKey.'_category_main';

			$this->personExtPrefix = 'tx_'.$this->conf['persDB.']['extKey'].'_pi1';
			$this->personTable = 'tx_'.$this->conf['persDB.']['extKey'].'_person';
			$this->authorMMTable    = 'tx_'.$this->extKey.'_publication_persons_auth_mm';
			$this->publisherMMTable = 'tx_'.$this->extKey.'_publication_persons_publ_mm';
		}

		$this->template = $this->cObj->fileResource($this->conf['templateFile']);
		
		$this->publicationT = $this->cObj->fileResource($this->conf['publicationTemplate']);
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();
		$this->getPiVars();
		$this->loadTypes();
		$this->internal['results_at_a_time']=t3lib_div::intInRange($this->conf['listView.']['results_at_a_time'],0,1000,3);		// Number of results to show in a listing.
		if ($this->piVars['showAll']) {
			$this->internal['results_at_a_time'] = 1000;
			$this->piVars['pointer'] = 0;
		}
		$this->internal['maxPages']=t3lib_div::intInRange($this->conf['listView.']['maxPages'],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
		$this->internal['searchFieldList']='authors_ext,title,location,run,year,volume,magazine_title,magazine_year,magazine_issue,anthology_title,anthology_publisher,pages,event_date,other_redaction';
		$this->internal['orderByList']='author_sorting_text,year,title,location,run,magazine_title,magazine_year,magazine_issue,anthology_title,anthology_publisher,pages,event_date,other_redaction,tstamp';

		$this->conf['pidList'] = $this->getTSFFvar('pidList');

		if (!isset($this->piVars['pointer']))	$this->piVars['pointer']=0;

			// Initializing the query parameters:
		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['currentTable'] = $this->table;
		$this->internal['showFirstLast'] = $this->conf['search.']['pagebrowser.']['showFirstLast'];

		if (intval($this->piVars['removeUid']) > 0) {
			$this->checkRemovePermission();
			$this->hideRecord();
		}
	}

	/**
	 * Checks permissions and return appropriate view
	 *
	 * @param string $conent
	 * @param array $conf typoscript configuration array
	 *
	 * @return string Html formated output
	 */
	function main($content,$conf)	{
	
		/**
		 * Add additional JavaScript and CSS for abstractView in popup
		 */
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .='<script type="text/javascript" src="typo3conf/ext/x4epublication/res/windows.js"></script>';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .='<script type="text/javascript" src="typo3conf/ext/x4epublication/res/window_effects.js"></script>';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .='<script type="text/javascript" src="typo3conf/ext/x4epublication/res/popup.js"></script>';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= '<link rel="stylesheet" type="text/css" href="typo3conf/ext/x4epublication/res/style.css" media="all" />';
	
		$this->init($content,$conf);
		switch ($this->getTSFFvar('modeSelection')) {
			case '1':
				$content = $this->listBySubCategory($_GET[$this->personExtPrefix]['showUid']);
			break;
			case '2':
				$this->checkAccessPermission();
				$this->editMode = true;
				$GLOBALS['TSFE']->additionalHeaderData[$this->extKey].='
					<script type="text/javascript" src="typo3conf/ext/x4epublication/templates/code.js"></script>';
				if (!isset($_GET[$this->personExtPrefix]['showUid'])) {
					$personUid = $this->person['uid'];
				} else {
					$personUid = intval($_GET[$this->personExtPrefix]['showUid']);
				}
				$content = $this->listBySubCategory($personUid);
			break;
			case '3':
		 		$content = $this->singleView($this->piVars['singleUid']);
			break;
			case '4':
				$content = $this->listByMainCategory();
			default:
				$content = $this->listView($content,$conf);
			break;
		}
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Checks if the current user has enough rights to perform the action,
	 * dies if otherwise
	 *
	 * @return void
	 */
	function checkAccessPermission() {
		if ($this->conf['noPersDB']==1) {
			if (!isset($GLOBALS['TSFE']->fe_user->user['uid'])) {
				die('Access violation (nopersdb)');
			}
		} else {

			if ($_GET[$this->personExtPrefix]['showUid'] > 0) {
				$this->person = $this->pi_getRecord($this->personTable,intval($_GET[$this->personExtPrefix]['showUid']));
			} else {
				$p = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'feuser_id='.intval($GLOBALS['TSFE']->fe_user->user['uid']).$this->cObj->enableFields($this->personTable));
				$this->person = $p[0];
			}
			$activePerson = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('publadmin',$this->personTable,'feuser_id = '.intval($GLOBALS['TSFE']->fe_user->user['uid']).$this->cObj->enableFields($this->personTable));
			$activePerson = $activePerson[0];
			if (!((isset($this->person['uid'])) && isset($GLOBALS['TSFE']->fe_user->user['uid']) && (($this->person['feuser_id'] == $GLOBALS['TSFE']->fe_user->user['uid']) || ($activePerson['publadmin'] == 1)))) {
				die('Access violation');
			}
		} 
	}

	/**
	 * Hides record (If user selects delete in frontend editing mode)
	 *
	 * @return void
	 */
	function hideRecord() {
		$publ = $this->pi_getRecord('tx_x4epublication_publication',$this->piVars['removeUid']);
		if (is_file($publ['file_ref'])) {
			unlink($publ['file_ref']);
			rmdir(dirname($publ['file_ref']));
		}
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->table,'uid='.intval($this->piVars['removeUid']),array('deleted'=>1));
		require_once('typo3conf/ext/x4epublication/pi2/class.tx_x4epublication_pi2.php');
		tx_x4epublication_pi2::clearCache($this->conf['pidList']);
	}

	/**
	 * Displays the publication in detail view
	 * @param integer $uid	Uid of the publication to display
	 * @return string HTML formatted string
	 */
	function singleView($uid){
   		$this->template = $this->cObj->fileResource($this->conf['singleViewTemplate']);
		$this->template = $this->cObj->getSubpart($this->template,'###singleView###');
		$publ = $this->pi_getRecord($this->table,$uid);
		return $this->renderPublication($publ);
	}
   
	/**
	 * Returns the latest publications of a given author. If author has
	 * selected his publications to display in the latest view, these will
	 * be returned.
	 *
	 * @param integer $authorUid Uid of the author (person database)
	 * @param integer $numRows Number of publications to show
	 * @return string HTML formatted output of the publications
	 */
	function getLatestByAuthor($authorUid,$numRows) {
		$this->template = $this->cObj->getSubpart($this->template,'###latestByAuthor###');
			// create where condition
		$author = $this->pi_getRecord($this->personTable,$authorUid);
		if ($author['tx_x4epublication_displayselected']) {
			$res = $this->getPersonallySelectedRes($author['tx_x4epublication_selectedpubls']);
		} else {
			$res = $this->getDefaultLatestRes($authorUid,$numRows);
		}
				// Put the whole list together:
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0) {
				// Adds the whole list table
			$subArr['###list###'] = $this->pi_list_makelist($res);
			$tmpl = $this->cObj->getSubpart($this->template,'###listView###');

			return $this->cObj->substituteMarkerArrayCached($this->template,$mArr,$subArr);
		} else {
			return '';
		}
	}

	/**
	 * Get personally selected result. User defines it's own publications
	 *
	 * @param 	String	$uids	Comma-seperated list of uids
	 * @return	Object			SQL Result pointer
	 */
	function getPersonallySelectedRes($uids) {
		$uidArr = t3lib_div::trimExplode(',',$uids,1);
		$queries = array();
		foreach($uidArr as $u) {
			array_push($queries,$GLOBALS['TYPO3_DB']->SELECTquery('*',$this->table,'uid ='.intval($u).$this->cObj->enableFields($this->table)));
		}
		if (count($queries)) {
			$query = '('.implode(') UNION (',$queries).')';
			return $GLOBALS['TYPO3_DB']->sql_query($query);
		} else {
			// create a empty result pointer
			return $GLOBALS['TYPO3_DB']->sql_query('SELECT * FROM '.$this->table.' WHERE 1=2');
		}
	}
	
	/**
	 * Returns a sql-result pointer. Pointing to the latest publication by given
	 * algorithm
	 *
	 * @param 	integer		$authorUid	Uid of author
	 * @param 	integer		$numRows	Number of rows to display
	 *
	 * @return 	Object					SQL-Result pointer
	 */
	function getDefaultLatestRes($authorUid,$numRows) {
		$addWhere = $specialAddWhere = ' AND '.$this->table.'.uid IN ('.$GLOBALS['TYPO3_DB']->SELECTquery('DISTINCT uid_local',$this->authorMMTable,'uid_foreign = '.intval($authorUid)).')';

		if ($this->conf['excludeSubCategoryInLatestUid'] != '') {
			$addWhere .= ' AND '.$this->table.'.category_sub NOT IN ('.$this->conf['excludeSubCategoryInLatestUid'].')';
		}

			// Make listing query, pass query to SQL database:
		$this->internal['results_at_a_time'] = $numRows;
		$this->internal['orderBy'] = 'year, title';
		$this->internal['descFlag'] = true;

			// set the category from which at least one publication will be shown
		if (isset($this->conf['includeSubCatInLatestIfAvailable']) && $this->conf['includeSubCatInLatestIfAvailable'] > 0) {
			$includeSubCatInLatestIfAvailable = $this->conf['includeSubCatInLatestIfAvailable'];
			$specialAddWhere .= ' AND '.$this->table.'.category_sub = '.intval($includeSubCatInLatestIfAvailable);
		}

		$sql1 = $GLOBALS['TYPO3_DB']->SELECTquery('*',$this->table,'1 '.$specialAddWhere.$this->cObj->enableFields($this->table),'','year DESC, title',1);
		$addWhere .= ' AND '.$this->table.'.category_sub != 1';
		$sql2 = $GLOBALS['TYPO3_DB']->SELECTquery('*',$this->table,'1 '.$addWhere.$this->cObj->enableFields($this->table),'','year DESC, tstamp DESC',$this->internal['results_at_a_time']-1);
		return $GLOBALS['TYPO3_DB']->sql_query('('.$sql1.') UNION ('.$sql2.')');
	}

	/**
	 * List records by main category. Is not used at present, though probably not working
	 *
	 * @deprecated Has never been used
	 * @param 	int		If defined, only publications with this author will be shown
	 * @return  string	HTML-String containig the list
	 */
	function listByMainCategory($authorUid=0) {
		$out = '';
		$where = '';
		$persExt = explode('_',$this->personExtPrefix);
			// add subquery to get only publication which the author is involved in
		if (intval($authorUid) > 0) {
			$where = 'uid IN ('.$GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->authorMMTable,'uid_foreign = '.intval($authorUid)).')';
		}
		
		$this->template = $this->cObj->getSubpart($this->template,'###latestByMainCategory###');
		
		

			// get all main categories
		$res = $this->pi_exec_query('tx_x4epublication_category_main');

		if ($this->internal['orderBy'] == '') {
			$this->internal['orderBy'] = 'year,title';
		}

			// loop over main categories to get sub categories and after all, the publications
		while ($mainCat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				// generate subquery which gets all subcategories from one category
			$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('uid','tx_x4epublication_category_sub','cat_main = '.intval($mainCat['uid']));
				// get authors from defined department IF department is selected and persDB is loaded
			if(intval($this->conf['showOnlyPublFromDep']) > 0 && t3lib_extMgm::isLoaded($persExt[1])){
				$depSub = $GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->personDepMMTable,'uid_foreign = '.$this->conf['showOnlyPublFromDep']);
				$dep = ' AND FIND_IN_SET (\''.$depSub.'\',authors)';
			} else {
				$dep = '';
			}
				// get publications
			$publ = $this->pi_exec_query('tx_x4epublication_publication',0,$where.' AND category_sub IN ('.$subQ.')'.$dep);
				// set markers
			$mArr['###mainCategory###'] = $mainCat['title'];
			$subPArr['###publications###'] = $this->pi_list_makelist($publ);
			$out .= $this->cObj->substituteMarkerArrayCached($this->template,$mArr,$subPArr);
			$GLOBALS['TYPO3_DB']->sql_free_result($publ);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $out;
	}

	/**
	 * List records by sub category.
	 *
	 * Performace-tuning-possibilities:
	 *		- Only add the really required columns to author (so far all fields are put into an marker)
	 *
	 * @param 	int		$authorUid	If defined, only publications with this author will be shown
	 * @param	string	$addWhere	Additional where query
	 * @return  string	HTML-String containig the list
	 */
	function listBySubCategory($authorUid=0,$addWhere='') {
		global $TCA;
		$out = '';
		$where = '';
		$searchT = $this->cObj->getSubpart($this->template,'###searchBox###');
		$noResultT = $this->cObj->getSubpart($this->template,'###noResultFoundBox###');

		if ($this->editMode) {
			$newPubliT = $this->cObj->getSubpart($this->template,'###newPublicationLink###');
				// add create new publication link
			$p[$this->personExtPrefix.'[showUid]'] = $this->person['uid'];
			$link = $this->pi_linkToPage(htmlentities($this->pi_getLL('createPublication')),$this->getTSFFvar('newPublicationPageUid'),'',$p);
			$subP['###newPublicationLink###'] = $this->cObj->substituteMarker($newPubliT,'###link###',$link);
			unset($newPubliT,$p,$link);
		} else {
			$subP['###newPublicationLink###'] = '';
		}
		$this->internal['results_at_a_time'] = 1000;

			// additional ORDER BY statement
		$addOrderBy = $this->table.'.year DESC, .'.$this->table.'.tstamp DESC';


			// add subquery to get only publication which the author is involved in
		if (intval($authorUid) > 0) {
			$authorT = $this->cObj->getSubpart($this->template,'###authorHeading###');
			$author = $this->pi_getRecord($this->personTable,intval($authorUid));

			$where = ' AND '.$this->table.'.uid IN ('.$GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->authorMMTable,'uid_foreign = '.intval($authorUid)).')';

			if ($author['publadmin'] && $this->editMode) {
				$where = '';
			}
			$author = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'uid = '.intval($authorUid).$this->cObj->enableFields($this->personTable));
			$author = $author[0];
			foreach($author as $key => $val) {
				if (($key != 'username') && ($key != 'password')) {
					$boxT = $this->cObj->getSubpart($authorT,'###'.$key.'_box###');
					if ($val != '') {
						$tmp = $this->cObj->substituteMarker($boxT,'###'.$key.'###',$val);
					} else {
						$tmp = '';
					}
					$authorT = $this->cObj->substituteSubpart($authorT,'###'.$key.'_box###',$tmp);
				}
			}
			$this->makePersDbInstance();
			$this->persDbInstance->addPersonToPageTitle($author,2,$this->cObj->getSubpart($this->template,'###pageTitle###'));
			$mArr['###search###'] = '';
			$subP['###selectYear###'] = '';
			$subP['###authorBox###'] = $this->cObj->substituteMarker($this->cObj->getSubpart($this->template,'###authorBox###'),'###author###',$GLOBALS['TSFE']->page['title']);
		} else {
				// show searchbox,pdflink and year-selection if no author is selected
			$mArr['###search###'] = $this->pi_list_searchBox('',$searchT);
			$subP['###selectYear###'] = $this->getYearSearchLinks();
			$subP['###authorBox###'] = '';
			// Order by Author names
			$addOrderBy = $this->personTable.'.lastname, '.$this->personTable.'.firstname, '.$addOrderBy;
		}
		$mArr['###pdfLink###'] = $this->getPdfLink();
		$mArr['###txtLink###'] = $this->getTxtLink();
		$mArr['###formAction###'] = $this->pi_getPageLink($GLOBALS['TSFE']->id);
		unset($author,$key,$val,$boxT,$tmp,$searchT);
		$tmpl = $this->cObj->getSubpart($this->template,'###latestBySubCategory###');
		$categoryJumpBoxT = $this->cObj->getSubpart($this->template,'###jumpToCategoryBox###');
		$this->template = $this->cObj->getSubpart($tmpl,'###subCategories###');
		$rowsT = $this->cObj->getSubpart($this->template,'###rows###');
		$subP['###jumpToCategoryBox###'] = '';

			// only display when either search or author view
		if (intval($authorUid) > 0 || $this->piVars['submit'] || $this->piVars['showAll'] || $this->piVars['yearfrom']) {
			$subWhere = '';
			
			// disalbe Multilang to display publications even if another language is default than publications have been created in
			if(intval($this->getTSFFvar('disableMultiLang')) < 1){
			if(intval($GLOBALS['TSFE']->sys_language_uid) > 0){
				$subWhere = ' AND sys_language_uid = '.intval($GLOBALS['TSFE']->sys_language_uid);
			}
			}
			
			if ($this->editMode) {
				$editableCats = ($this->conf['editableCats'] != '') ? $this->conf['editableCats'] : '-1';
					$subWhere .= ' AND uid IN ('.$editableCats.')';
			}	
			
				// get all main categories
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->subCatTable,'pid IN ('.$this->conf['pidList'].')'.$subWhere.$addWhere.$this->cObj->enableFields($this->subCatTable),'',$TCA[$this->subCatTable]['ctrl']['sortby']);
			
				// loop over main categories to get sub categories and after all, the publications
			$where .= $this->generateSearchQuery();
			
			if ($this->internal['orderBy'] == '') {
				$this->internal['orderBy'] = 'year';
				$this->internal['descFlag'] = 1;
			}

				// get category jump template
			if (intval($authorUid)==0) {
				$addOrderBy = $this->table.'.author_sorting_text,'.$this->table.'.year DESC';
			} else {
				$addOrderBy = $this->table.'.year DESC,'.$this->table.'.author_sorting_text';
			}
			
			// user sorting by manuel - 26.10.2010
			if(!empty($this->conf['listView.']['manualOrderBy'])){
				if($this->conf['listView.']['orderOverwrite'] == 1){
					$addOrderBy = $this->conf['listView.']['manualOrderBy'];
				} else {
					$addOrderBy .= $this->conf['listView.']['manualOrderBy'];
				}
			}
			// user sorting - end

			$categoryJumpT = $this->cObj->getSubpart($categoryJumpBoxT,'###jumpToCategory###');
			$count = 0;
			$orgAddWhere = $addWhere;
			while ($subCat = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if(!intval($this->getTSFFvar('disableMultiLang'))){
				if(intval($GLOBALS['TSFE']->sys_language_uid) > 0){
					$subCat['uid'] = $subCat['l18n_parent'];	
				}}
					// get publications
				$mm_cat['table'] = $this->personTable;
				$mm_cat['mmtable'] = $this->authorMMTable;

				$addWhere = $orgAddWhere;
				
				$aWhere = '';
				if ($this->editMode) {
					$aWhere = ' AND fdb_id = 0 ';
				}
				
				// filter for persDB department
				if ($this->conf['showOnlyPublWithAuthorFromDepartment']) {
					$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('uid_local','tx_x4epersdb_person_department_mm','uid_foreign = '.intval($this->conf['showOnlyPublWithAuthorFromDepartment']));
					$addWhere .= ' AND '.$this->table.'.uid IN ('.$GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->authorMMTable,'uid_foreign IN ('.$subQ.')').')';
				}

				// filter for researchDB researchgroup
				if ($this->conf['showOnlyPublWithAuthorFromResearchgroup']) {
					$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('uid_foreign','tx_x4eresearch_researchgroup_head_mm','uid_local = '.intval($this->conf['showOnlyPublWithAuthorFromResearchgroup']));
					if($this->conf['showPublOfTeamMembers']){
						$subQ2 = $GLOBALS['TYPO3_DB']->SELECTquery('uid_foreign','tx_x4eresearch_researchgroup_members_mm','uid_local = '.intval($this->conf['showOnlyPublWithAuthorFromResearchgroup']));
						$addWhere .= ' AND '.$this->table.'.uid IN ('.$GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->authorMMTable,'uid_foreign IN ('.$subQ.') OR uid_foreign IN ('.$subQ2.')').')';	
					}else{
						$addWhere .= ' AND '.$this->table.'.uid IN ('.$GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->authorMMTable,'uid_foreign IN ('.$subQ.')').')';
					}
					
					
				}
				unset($mm_cat);
					// first get query, add DISTINCT and run it
				$publQ = $this->pi_list_query($this->table,0,$where.$aWhere.' AND category_sub ='.$subCat['uid'].$addWhere,$mm_cat,'',$addOrderBy);

				$publQ  = str_replace('SELECT ', 'SELECT DISTINCT ',$publQ);

				//$publ = $this->pi_exec_query('tx_x4epublication_publication',0,$where.' AND category_sub ='.$subCat['uid'],$mm_cat,'',$addOrderBy);
				$publ = $GLOBALS['TYPO3_DB']->sql_query($publQ);
					// set markers

				$uids = array();
				
				$count += $GLOBALS['TYPO3_DB']->sql_num_rows($publ);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($publ) > 0) {
					$mArr['###subCategory###'] = $subCat['title_plural'];
					$mArr['###categoryAnchor###'] = 'subcat_'.$subCat['uid'];
					$subPArr['###rows###'] = $this->pi_list_makelist($publ);
					$out .= $this->cObj->substituteMarkerArrayCached($this->template,$mArr,$subPArr);
						// add category selection
					$m['###categoryTitle###'] = $subCat['title_plural'];

					$m['###categoryAnchor###'] = $_SERVER['REQUEST_URI'].'#subcat_'.$subCat['uid'];
					$t['###subCategories###'] .= $this->cObj->substituteMarkerArray($categoryJumpT,$m);
				}

				$GLOBALS['TYPO3_DB']->sql_free_result($publ);
			}
			
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			$subP['###subCategories###'] = $out;
			if (intval($authorUid) > 0) {
				$subP['###jumpToCategoryBox###'] = $this->cObj->substituteSubpart($categoryJumpBoxT,'###jumpToCategory###',$t['###subCategories###']);
			}
			unset($m,$t,$categoryJumpBoxT,$categoryJumpT);
			// Adds the result browser:
			$mArr['###pageBrowser###'] = $this->pi_list_browseresults(1,'',$this->conf['search.']['pagebrowser.']['wraps.']);
			if ($count == 0) {
				$mArr['###noResultFound###'] = $this->noResultFound($noResultT);
			} else {
				$mArr['###noResultFound###'] = '';
			}
		} else {
			$subP['###subCategories###'] = '';
			$mArr['###noResultFound###'] = '';
		}
		
		
		$mArr['###prefixId###'] = $this->prefixId;
		return $this->cObj->substituteMarkerArrayCached($tmpl,$mArr,$subP);
	}

	/**
	 * Generates an instance of the person-db. Puts it into member variable
	 * to prevent regenerating instances
	 *
	 * return void
	 */
	function makePersDbInstance() {
		if ($this->persDbInstance == null) {
			require_once(PATH_typo3conf.'ext/x4epersdb/pi1/class.'.$this->personExtPrefix.'.php');
			$this->persDbInstance = t3lib_div::makeInstance($this->personExtPrefix);
		}
	}

	/**
	 * Returns a selection of years
	 *
	 * @return string
	 */
	function getYearSearchLinks() {
		$t = $this->cObj->getSubpart($this->template,'###selectYear###');
		$actualYear = date('Y');
		$o = '';
		// save pi_vars
		for($i=$this->conf['startYear'];$i<=$actualYear;$i++) {
			$p[$this->prefixId.'[yearfrom]'] = $i;
			$p[$this->prefixId.'[yearto]'] = $i;
			if(isset($this->conf['tx_x4eresearch_pi1.']['researchUid'])){
				$p['tx_x4eresearch_pi1[showUid]'] = $this->conf['tx_x4eresearch_pi1.']['researchUid']; 
			}
			$o.= $this->cObj->substituteMarker($t,'###year###',$this->pi_linkTP($i,$p));
		}
		if(!empty($this->conf['yearBoxWrap'])){
			$wrap = explode('|',$this->conf['yearBoxWrap']);
			return $wrap[0].$o.$wrap[1];
		} else {
			return $o;
		}
	}

	/**
	 * Returns a list row. Get data from $this->internal['currentRow'];
	 *
	 * Possible performace increase might be realised by putting the row templates
	 * into a member variable
	 *
	 * @param int $c	Row number
	 * @return string	HTML formatted string conaining one column
	 */
	function pi_list_row($c) {
		$cellT = $this->cObj->getSubpart($this->rowT[c%2],'###cell###');
		$editT = $this->cObj->getSubpart($this->rowT[c%2],'###editCol###');

			// add all fields to marker array
		//$mArr['###content###'] = $this->renderPublication($this->internal['currentRow']);
		$mArr['###content###'] = $this->renderPublication($this->internal['currentRow']);
		$mArr['###uid###'] = $this->internal['currentRow']['uid'];
		$subPart['###cell###'] = $this->cObj->substituteMarkerArray($cellT,$mArr);
		unset($mArr);
		if ($this->editMode) {
				// add edit and remove cols
			$mArr['###editPublication###'] = $this->addEditCol();
			$mArr['###removePublication###'] = $this->addRemoveCol();
			$subPart['###editCol###'] = $this->cObj->substituteMarkerArray($editT,$mArr);
			unset($editT,$mArr);
		} else {
			$subPart['###editCol###'] = '';
		}
			// load template
		return $this->cObj->substituteMarkerArrayCached($this->rowT[$c%2],array(),$subPart);
	}

	/*
	 * Add remove column, so the user can delete his publications
	 *
	 * @return string HTML link
	 */
	function addRemoveCol() {
		return '<a href="javascript:removePublication('.$this->internal['currentRow']['uid'].')">'.$this->pi_getLL('removeRecord').'</a>';
	}

	/*
	 * Add edit column, so the user can edit his publications
	 *
	 * @return string	HTML link
	 */
	function addEditCol() {
		$param[$this->personExtPrefix.'[showUid]'] = $this->person['uid'];
		$param['tx_'.$this->extKey.'_pi2[uid]'] = $this->internal['currentRow']['uid'];
		$param['tx_'.$this->extKey.'_pi2[action]'] = 'edit';
		return $this->pi_linkToPage($this->pi_getLL('editRecord'),$this->getTSFFvar('editPageUid'),'',$param);
	}

	/**
	 * Loads all sub-categories and puts them into the member variable
	 *
	 * @return void
	 */
	function loadTypes() {
		$t = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->subCatTable,'hidden = 0 AND deleted = 0');
		foreach($t as $key => $val) {
			// add record
			$this->types[$val['uid']] = $val;
			// add columns as array
			$this->types[$val['uid']]['columnArray'] = t3lib_div::trimExplode("\n",$val['show_columns']);
		}
	}

	/**
	 * Returns a rendered publication
	 *
	 * @param array	$publ	Publication record
	 * @return string HTML formatted output of one single publication
	 */
	function renderPublication($publ) {
	
		// load template if not loaded yet
		if (!isset($this->types[$publ['category_sub']]['template'])) {
			$this->types[$publ['category_sub']]['template'] = $this->cObj->getSubpart($this->publicationT,'###publ_'.$publ['category_sub'].'###');
		}
		if ($this->types[$publ['category_sub']]['template'] == '') {
			return 'Template für folgende Kategorie nicht gefunden:'.$publ['category_sub'];
		}
		
		$subParts = array();
		if (is_array($this->types[$publ['category_sub']]['columnArray'])) {
			foreach ($this->types[$publ['category_sub']]['columnArray'] as $col) {
				$publ[$col] = $publ[$col];
				switch($col) {
					case 'authors':
						if (trim($publ['author_sorting'])!='') {
							$subParts['###authors_box###'] = $this->getOrderedAuthors($publ);
						} else {
							$subParts['###authors_box###'] = $this->getAuthors($publ);
						}
					break;
					case 'publishers':
						if (trim($publ['publisher_sorting'])!='') {
							$subParts['###publishers_box###'] = $this->getOrderedAuthors($publ,true);
						} else {
							$subParts['###publishers_box###'] = $this->getAuthors($publ,true);
						}
					break;
					case 'url':
						$subT = $this->cObj->getSubpart($this->types[$publ['category_sub']]['template'],'###'.$col.'_box###');
						if ($publ[$col] != '') {
							$subParts['###'.$col.'_box###'] = $this->cObj->substituteMarker($subT,'###'.$col.'###',$this->cObj->gettypolink($this->pi_getLL('url'),$publ[$col]));
						} else {
							$subParts['###'.$col.'_box###'] = '';
						}
					break;
					case 'department_id':
						$subT = $this->cObj->getSubpart($this->types[$publ['category_sub']]['template'],'###'.$col.'_box###');
						$depTitle = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_x4edivision_divisiongroup','uid='.intval($publ['department_id']),'');
						if ($publ[$col] != '') {
							$subParts['###'.$col.'_box###'] = $this->cObj->substituteMarker($subT,'###'.$col.'###',$depTitle[0]['name']);
						} else {
							$subParts['###'.$col.'_box###'] = '';
						}
					break;
					case 'file_ref':
						$subParts['###'.$col.'_box###'] = $this->getFileRef($publ);
					break;
					default:
						$subT = $this->cObj->getSubpart($this->types[$publ['category_sub']]['template'],'###'.$col.'_box###');
						
						if ($publ[$col] != '') {
							$subParts['###'.$col.'_box###'] = $this->cObj->substituteMarker($subT,'###'.$col.'###',$publ[$col]);
						} else {
							$subParts['###'.$col.'_box###'] = '';
						}
					break;
				}
			}
			
			$detailPage = $this->conf['detailPageUid'];
			
			$sep = explode(';',$this->conf['listView.']['dlSeparators']);
			
			// Check for available abstract
			if($publ['abstract'] != ''){
				
				$mArr['###popup_show###'] = $this->pi_getLL('popup_show'); // popup-triggering text
				//$mArr['###popup_hide###'] = $this->pi_getLL('popup_hide');
				$piVars = $this->piVars;
				$piVars['singleUid'] = $publ['uid'];
				if(isset($this->piVars['singleUid'])){
					$this->piVars['singleUid'] = '';
					$this->piVars['pointer'] = '';
					$piVars['singleUid'] = '';
					$piVars['pointer'] = '';
				}
				
				// set separator for multiple linkbox entries
				$mArr['###linkBefore###'] = '';
				if($publ['file_ref'] != '' | $publ['url'] != ''){
					$mArr['###linkAfter###'] = " $sep[1] ";
				}else if($publ['file_ref'] != '' && $publ['url'] == ''){
					$mArr['###linkAfter###'] = " $sep[1] ";;
				}else{
					$mArr['###linkAfter###'] = '';
				}
				
				$mArr['###detailLinkStart###'] = str_replace('</a>','',$this->pi_linkTP_keepPIvars('Abstract',$piVars,1,1,$detailPage));
				$mArr['###detailLinkEnd###'] = '</a>';
				$mArr['###linkText###'] = '';
				$mArr['###backLink###'] = $this->pi_LinkTP_keepPIvars($this->pi_getLL('back'),array(),0,0,$listPage);	
			}else{
				$mArr['###popup_show###'] = '';
				$mArr['###popup_hide###'] = '';
				$mArr['###linkBefore###'] = '';
				$mArr['###linkAfter###'] = '';				
				$mArr['###detailLinkStart###'] = '';
				$mArr['###detailLinkEnd###'] = '';
				$mArr['###linkText###'] = '';
				$mArr['###backLink###'] = '';		
			}
			
			$mArr['###uid###'] = $publ['uid'];
			
			// set start and end marker for linkbox
			$subT = $this->cObj->getSubpart($this->types[$publ['category_sub']]['template'],'###seperator###');
			if($publ['file_ref'] != '' | $publ['url'] != '' | $publ['abstract'] != ''){
				$mArr['###seperatorRight###'] = $sep[2];
				$mArr['###seperatorLeft###'] = $sep[0];
			}else{
				$mArr['###seperatorRight###'] = '';
				$mArr['###seperatorLeft###'] = '';
			}
			
			$mArr['###detailLinkStart###'] = str_replace('&nbsp;</a>','',$this->pi_linkTP_keepPIvars('&nbsp;',array('singleUid'=> $publ['uid']),1,1,$detailPage));
			$mArr['###detailLinkEnd###'] = '</a>';
			$mArr['###uid###'] = $publ['uid'];
			
			return $this->cObj->substituteMarkerArrayCached($this->types[$publ[category_sub]]['template'],$mArr,$subParts);
		} else {
			return '';
		}
	}
	
	/**
	 * gets the fileref and adds a "/" if the url is filled
	 * 
	 * @author Leo Rotzler <leo@4eyes.ch>
	 * @param array $publ
	 * @return string
	 */
	function getFileRef($publ){
		$subT = $this->cObj->getSubpart($this->types[$publ['category_sub']]['template'],'###file_ref_box###');
		if ($publ['file_ref'] == '') {
			return '';
		}
		
		if($publ['url'] != ''){
			$mArr['file_refEnd'] = ' / ';
		}else{
			$mArr['file_refEnd'] = '';
		}
		
		$mArr['file_ref'] = $publ['file_ref'];	
		return $this->cObj->substituteMarkerArray($subT,$mArr,'###|###');
	}

	/**
	 * Displays the authors (or publishers) according to their sorting
	 *
	 * @param array $publ Publication record
	 * @param boolean $showPublisher (if false authors are returned)
	 *
	 * @return string HTML string containing authors and links to their profiles
	 */
	function getAuthors($publ,$showPublisher=false) {
		if ($showPublisher) {
			$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('DISTINCT uid_foreign',$this->publisherMMTable,'uid_local='.intval($publ['uid']));
			$authorsT = $this->cObj->getSubpart($this->types[$publ['category_sub']]['template'],'###publishers_box###');
			$authorT = $this->cObj->getSubpart($authorsT,'###publisher###');
			$external = $publ['publishers_ext'];
			$subPLabel= '###publisher###';
		} else {
			$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('DISTINCT uid_foreign',$this->authorMMTable,'uid_local='.intval($publ['uid']));
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
	}

	/**
	 * Displays a list of publications, probably deprecated
	 *
	 * @param string $addWhere	Additional where statement
	 * @return string HTML formatted list of publications
	 */
	function listView($addWhere='')	{
			// add selected paged to pidList
		$tmpUid = explode(':',$GLOBALS['TSFE']->currentRecord);
		if ($tmpUid[1] != '') {
			$tmpPages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pages','tt_content','uid = '.intval($tmpUid[1]));
			$this->conf['pidList'] = $tmpPages[0]['pages'];
			unset($tmpPages,$tmpUid);
		}

		$addWhere = $this->generateSearchQuery();
		if ($this->internal['orderBy'] == '') {
			$this->internal['orderBy'] = 'year,title';
		}
		
		if($this->internal[''])
			// Get number of records:
		$res = $this->pi_exec_query($this->table,1,$addWhere);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

			// Make listing query, pass query to SQL database:
		//$res = $this->pi_exec_query('tx_x4epublication_publication',0,$addWhere);
		$mArr['###noResultFound###'] = '';
		if ($this->internal['res_count'] > 0) {
			$res = $this->pi_exec_query($this->table,0,$addWhere);

				// Adds the whole list table
			$subArr['###list###'] = $this->pi_list_makelist($res);
			$subArr['###jumpToCategoryBox###'] ='';

		} else {
			$subArr['###list###'] = '';
			$mArr['###noResultFound###'] = $this->noResultFound();
		}

			// Adds the search box:
		$mArr['###search###'] = $this->pi_list_searchBox();

			// Adds the result browser:
		$mArr['###pageBrowser###'] = $this->pi_list_browseresults(1,'',$this->conf['search.']['pagebrowser.']['wraps.']);

		$mArr['###pdfLink###'] = $this->getPdfLink();
		$mArr['###txtLink###'] = $this->getTxtLink();
		$tmpl = $this->cObj->getSubpart($this->template,'###listView###');
		return $this->cObj->substituteMarkerArrayCached($tmpl,$mArr,$subArr);
	}

	/**
	 * Generate the search query according to the user input
	 *
	 * @return string SQL-Where query
	 */
	function generateSearchQuery() {
		$addWhere = '';
			// Generate additional where statement
		if (($this->piVars['yearfrom'] != '') && (is_numeric($this->piVars['yearfrom']))) {
			$addWhere .= ' AND SUBSTRING( '.$this->table.'.year, 1, 4 ) >= '.intval($this->piVars['yearfrom']);
		} else {
			$addWhere .= ' AND SUBSTRING( '.$this->table.'.year, 1, 4 ) >= 1';
		}
		if (($this->piVars['yearto'] != '') && (is_numeric($this->piVars['yearto']))) {
			$addWhere .= ' AND SUBSTRING( '.$this->table.'.year, 1, 4 ) <= '.intval($this->piVars['yearto']);
		}

		if ($this->piVars['category_sub'] != '') {
			if (is_numeric($this->piVars['category_sub'])) {
				$addWhere .= ' AND '.$this->table.'.category_sub = '.intval($this->piVars['category_sub']);
			}
		}
		if ($this->piVars['pub_language'] != '') {
			if (is_numeric($this->piVars['pub_language'])) {
				$addWhere .= ' AND '.$this->table.'.pub_language = '.intval($this->piVars['pub_language']);
			}
		}

		if ($this->piVars['authorSearchWord'] != '') {
				// make two subqueries => 1. get user with matching name/firstname, 2. get publications of this user
			$likeQuery = t3lib_div::trimExplode(' ',$this->piVars['authorSearchWord']);
			foreach($likeQuery as $key => $val) {
				$val = $GLOBALS['TYPO3_DB']->escapeStrForLike($val,$this->personTable);
				$likeQuery[$key] = '(lastname LIKE "%'.$val.'%" OR firstname LIKE "%'.$val.'%")';
			}
			unset($key,$val);
			$subQ1 = $GLOBALS['TYPO3_DB']->SELECTquery('uid',$this->personTable,implode(' AND ',$likeQuery));
			unset($likeQuery);
			$subQ2 = $GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->authorMMTable,'uid_foreign IN ('.$subQ1.')');
			unset($subQ1);
			$addWhere .= ' AND '.$this->table.'.uid IN ('.$subQ2.')';
			unset($subQ2);
		}
		return $addWhere;
	}

	/**
	 * Returns the link to the pdf-version
	 *
	 * @return string	HTML link to the publication export
	 */
	function getPdfLink() {
			// don't show get pdf-link if in pdf mode
		if ($_GET['pdf']) {

			return 'Stand: '.strftime('%d.%m.%Y',time());
		} elseif($this->editMode) {
			return '';
		} else {
			$p = array();
			foreach($this->piVars as $k => $v) {
				$p[$this->prefixId.'['.$k.']'] = $v;
			}
			if (isset($_GET[$this->personExtPrefix]['showUid'])) {
				$p[$this->personExtPrefix.'[showUid]'] = $_GET[$this->personExtPrefix]['showUid'];
			} else {
				$p[$this->prefixId.'[showAll]'] = 1;
			}
			$p[$this->prefixId.'[pdf]'] = 1;
			// add show all
			//$p['tx_x4epublication_pi1[showAll]'] = 1;
			$p['type'] = 4444;
			return $this->pi_linkTP($this->pi_getLL('pdfLinkText'),$p,0);
		}
	}

	/**
	 * Returns the link to the txt-version
	 *
	 * @return string	HTML link to the text export
	 */
	function getTxtLink(){
				// don't show get pdf-link if in pdf mode
		if ($_GET['txt']) {

			return 'Stand: '.strftime('%d.%m.%Y',time());
		} elseif($this->editMode) {
			return '';
		} else {
			$p = array();
			foreach($this->piVars as $k => $v) {
				$p[$this->prefixId.'['.$k.']'] = $v;
			}
			if (isset($_GET[$this->personExtPrefix]['showUid'])) {
				$p[$this->personExtPrefix.'[showUid]'] = $_GET[$this->personExtPrefix]['showUid'];
			} else {
				$p[$this->prefixId.'[showAll]'] = 1;
			}
			$p[$this->prefixId.'[txt]'] = 1;
			// add show all
			//$p['tx_x4epublication_pi1[showAll]'] = 1;
			$p['type'] = 4446;
			return $this->pi_linkTP($this->pi_getLL('txtLinkText'),$p,0);
		}
	}

	/**
	 * Checks wether active user is author of selected publication, dies if 
	 * permission check fails
	 *
	 * @return void
	 */
	function checkRemovePermission() {

		$author = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'feuser_id = '.intval($GLOBALS['TSFE']->fe_user->user['uid']).$this->cObj->enableFields($this->personTable));
		$author = $author[0];

		$count = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(*)',$this->authorMMTable,'uid_local = '.intval($this->piVars['removeUid']).' AND uid_foreign = '.intval($author['uid']));
			// handle invalid request
		if (($count[0]['count(*)'] == 0) && ($author['publadmin'] != 1)) {
			die("You're not allowed to remove this record!");
		}
	}

	/**
	 * Returns a Search box, sending search words to piVars "sword" and setting the "no_cache" parameter as well in the form.
	 * Submits the search request to the current REQUEST_URI
	 *
	 * @param	string		Attributes for the table tag which is wrapped around the table cells containing the search box
	 * @param 	string		Optional template to use
	 * @return	string		Output HTML, wrapped in <div>-tags with a class attribute
	 */
	function pi_list_searchBox($tableParams='',$tmpl='')	{
			// Search box design:
		if (!$tmpl) {
			$tmpl = $this->cObj->getSubpart($template,'###searchBox###');
		}
		unset($template);
		 
		$selectT = $this->cObj->getSubpart($tmpl,'###subcatOptions###');
		$tmpl = $this->cObj->substituteSubpart($tmpl,'###subcatOptions###',$this->generateOptionsFromTable($selectT,$this->subCatTable,$this->piVars['category_sub'], FALSE, '', '', ' AND pid IN ('.$this->conf['pidList'].') AND sys_language_uid = '. $GLOBALS["TSFE"]->sys_language_uid));
		$selectT = $this->cObj->getSubpart($tmpl,'###pubLangOptions###');
		if ($this->conf['langList'] != '') {
			$tmpl = $this->cObj->substituteSubpart($tmpl,'###pubLangOptions###',$this->generateOptionsFromTable($selectT,$this->langTable,$this->piVars['pub_language'], $false, 'lg_name_local', '', ' AND uid IN ('.$this->conf['langList'].')','lg_name_local'));
		}
		unset($selectT);
		
		$lConf = array('parameter'=>$GLOBALS['TSFE']->id);
		if(isset($this->conf['tx_x4eresearch_pi1.']['researchUid'])){
			$lConf['additionalParams'] = '&tx_x4eresearch_pi1[showUid]='.$this->conf['tx_x4eresearch_pi1.']['researchUid'];
		}
		$mArr['###formAction###'] = $this->cObj->typolink_URL($lConf);
		
		$mArr['###searchWord###'] = htmlspecialchars($this->piVars['sword']);
		$mArr['###pubLanguage###'] = htmlspecialchars($this->piVars['pub_language']);
		$mArr['###yearFrom###'] = htmlspecialchars($this->piVars['yearfrom']);
		$mArr['###yearTo###'] = htmlspecialchars($this->piVars['yearto']);
		$mArr['###prefixId###'] = $this->prefixId;
		$mArr['###submit###'] = $this->pi_getLL('pi_list_searchBox_search','Search',TRUE);
		$mArr['###authorSearchWord###'] = $this->piVars['authorSearchWord'];
		$mArr['###searchLabel###'] = $this->pi_getLL('searchLabel');
		$mArr['###publicationLabel###'] = $this->pi_getLL('publicationLabel');
		$mArr['###titleLabel###'] = $this->pi_getLL('titleLabel');
		$mArr['###allLabel###'] = $this->pi_getLL('allLabel');
		$mArr['###authorLabel###'] = $this->pi_getLL('authorLabel');
		$mArr['###publYearLabel###'] = $this->pi_getLL('publYearLabel');
		$mArr['###fromLabel###'] = $this->pi_getLL('fromLabel');
		$mArr['###toLabel###'] = $this->pi_getLL('toLabel');
		$mArr['###pubLanguageLabel###'] = $this->pi_getLL('pubLanguageLabel');
		return $this->cObj->substituteMarkerArrayCached($tmpl,$mArr);
	}

	/**
	 * List header row, showing column names:
	 *
	 * @return	string		HTML content; a Table row, <tr>...</tr>
	 */
	function pi_list_header()	{
		return '';
	}

	/**
	 * Compares to authors for the alpabetical ordering
	 *
	 * @param array $a Person record of Author 1
	 * @param array $b Person record of Author 2
	 * @return integer
	 */
	function compareAuthors($a,$b){
		if (strcasecmp($a['lastname'],$b['lastname']) > 0) {
			return 1;
		} elseif (strcasecmp($a['lastname'],$b['lastname']) < 0) {
			return -1;
		} else {
			return strcasecmp($a['firstname'],$b['firstname']);
		}
	}

	/**
	 * Returns a no-result found message according to your template and
	 * locallang
	 *
	 * @param string $tmpl Template to use
	 * @return string HTML formatted string
	 */
	function noResultFound($tmpl='') {
		if ($tmpl == '') {
			return $this->cObj->substituteMarker($this->cObj->getSubpart($tmpl,'###noResultFoundBox###'),'###noResultFoundText###',$this->pi_getLL('noResultFound'));
		} else {
			return $this->cObj->substituteMarker($tmpl,'###noResultFoundText###',$this->pi_getLL('noResultFound'));
		}
	}

	/**
	 * Returns the authors in the order given by their sorting
	 *
	 * @param array $publ Publication record (by reference)
	 * @param boolean $showPublisher (if false authors will be returned
	 *
	 * @return string	HTML output of the ordered authors
	 */
	function getOrderedAuthors(&$publ,$showPublisher=false) {
		if ($showPublisher) {
			$name = 'publisher';
		} else {
			$name = 'author';
		}

		if ($showPublisher) {
			$mmTable = $this->publisherMMTable;
		} else {
			$mmTable = $this->authorMMTable;
		}

		$subQ = $GLOBALS['TYPO3_DB']->SELECTquery('DISTINCT uid_foreign',$mmTable,'uid_local='.intval($publ['uid']));
		$authorsT = $this->cObj->getSubpart($this->types[$publ['category_sub']]['template'],'###'.$name.'s_box###');
		if ($this->author_sorting_creation) {
			$authorsT = '<!-- ###'.$name.'### begin -->###lastname###<!-- ###firstname_box### begin -->, ###firstname###<!-- ###firstname_box### end --><!-- ###separator### -->; <!-- ###separator### --><!-- ###'.$name.'### end --><!-- ###auth_publ_box### --> <!-- ###auth_publ_box### -->';
		}
		if ($authorsT=='') {
			return ' no template for authors found';
		}
		$authorT = $this->cObj->getSubpart($authorsT,'###'.$name.'###');
		$external = $publ[$name.'s_ext'];
		$subPLabel= '###'.$name.'###';

		require_once(PATH_typo3conf.'ext/'.$this->extKey.'/pi2/class.tx_x4epublication_pi2.php');
		$externalAuthors = tx_x4epublication_pi2::prepareExternalPersons($publ[$name.'s_ext'],$name.'s_ext');
		$auths = t3lib_div::trimExplode(',',$publ[$name.'_sorting'],1);

		// get templates
		$separatorT = str_replace("\n",'',$this->cObj->getSubpart($authorT,'###separator###'));
		$altSeparatorT = str_replace("\n",'',$this->cObj->getSubpart($authorT,'###alternative_separator###'));
		$numOfAuthors = 0;
		$authorStr = '';
			// now add all authors
		foreach($auths as $a) {
			if (intval($a)>0) {
					// hidden authors will be shown as well, but not linked to
					// their profile
				$a = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->personTable,'uid = '.intval($a).' AND deleted=0');
				$a = $a[0];
			} else {
				$a = $externalAuthors[$a];
			}

				// fill markers
			$mArr['###lastname###'] = $a['lastname'];
			if ($a['firstname'] != '') {
				$subP['###firstname_box###'] = $this->cObj->substituteMarker($this->cObj->getSubpart($authorT,'###firstname_box###'),'###firstname###',$a['firstname']);
				$subP['###firstnameShort_box###'] = $this->cObj->substituteMarker($this->cObj->getSubpart($authorT,'###firstnameShort_box###'),'###firstname###',substr($a['firstname'],0,1));

			} else {
				$subP['###firstname_box###'] = '';
			}
				// add link if page-id is set
			$p[$this->personExtPrefix.'[showUid]'] = $a['uid'];
			
			if (!$a['hidden'] && !$a['deleted'] && !$a['alumni'] && (intval($a['uid']>0))) {
				if ($this->getDetailPage($a['uid'])) {
					$mArr['###linkStart###'] = str_replace('&nbsp;</a>','',$this->pi_linkTP('&nbsp;',$p,1,$this->getDetailPage($a['uid'])));
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
				// clear separator subpart, because we don't need it
			$authorStr .= trim($this->cObj->substituteMarkerArrayCached($authorT,$mArr,$subP));
			$numOfAuthors++;
		}
		$subP = array();
		$subP[$subPLabel] = $authorStr;
		unset($authorStr);

		if ($showPublisher && ($numOfAuthors > 0)) {
			$subP['###auth_publ_box###'] = $this->cObj->getSubpart($authorsT,'###auth_publ_box###');
		} else {
			$subP['###auth_publ_box###'] = '';
		}

			// add external authors
		if ($showPublisher) {
			if ($publ['furtherPublishers']) {
				$subP['###additional###'] = $this->cObj->getSubpart($authorsT,'###additional###');;
			} else {
				$subP['###additional###'] = '';
			}
		} else {
			if ($publ['furtherAuthors']) {
				$subP['###additional###'] = $this->cObj->getSubpart($authorsT,'###additional###');;
			} else {
				$subP['###additional###'] = '';
			}
		}

		return $this->cObj->substituteMarkerArrayCached($authorsT,array(),$subP);
	}

	/**
	 * Returns the uid of the authors detail page
	 *
	 * @return integer
	 */
	function getDetailPage() {
		return $this->getTSFFvar('authorDetailPageUid');
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/pi1/class.tx_x4epublication_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/pi1/class.tx_x4epublication_pi1.php']);
}

?>