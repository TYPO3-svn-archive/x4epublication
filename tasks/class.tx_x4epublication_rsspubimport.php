<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 4eyes GmbH (info-at-4eyes.ch)
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
require_once(PATH_site.'fileadmin/localconfs/publication_import_localconf.php');
class tx_x4epublication_RssPubImport extends tx_scheduler_Task {

<<<<<<< .mine
	var $url;
	var $personPid ;
	var $pubPid;
	var $category = 136;
	var $currentPerson;
	var $tablePersDb = 'tx_x4epersdb_person';
	var $tablePublDb = 'tx_x4epublication_publication';
	var $tablePublDbMMAuthor = 'tx_x4epublication_publication_persons_auth_mm';
	var $tablePublDbMMPublisher = 'tx_x4epublication_publication_persons_publ_mm';
	var $pubCount = 0;
	var $errCount = 0;
		
	/**
	 * CONSRTUCTOR?
=======
	var $url;
	var $personPid ;
	var $pubPid;
	var $category = 136;
	var $currentPerson;
	var $tablePersDb = 'tx_x4epersdb_person';
	var $tablePublDb = 'tx_x4epublication_publication';
	var $tablePublDbMMAuthor = 'tx_x4epublication_publication_persons_auth_mm';
	var $tablePublDbMMPublisher = 'tx_x4epublication_publication_persons_publ_mm';
	var $pubCount = 0;
	var $errCount = 0;
	
	/**
	 * CONSRTUCTOR?
>>>>>>> .r40720
	 */
	 /*
	public function __construct($personPid, $pubPid){
		if($personPid != ''){
			$this->personPid = $personPid;
		}
		if($pubPid != ''){
			$this->pubPid = $pubPid;
		}
	}
	*/
	
	public function execute() {
<<<<<<< .mine
		// get id's of old publications
		$oldPublications = $this->getOldPublications();
		$persons = $this->getPersonWithRssUrl();
		if ($persons != ''){
			foreach($persons as $this->currentPerson){
				$rssXmlStr = $this->file_post_contents($this->currentPerson['rssUrl']);
				
				$rssXml = new SimpleXMLElement($rssXmlStr);
			
				if($rssXml != ''){
					$this->doImport($rssXml);
				}
			}
		}
		
		// remove old publications
		if($this->pubCount > 0){
			$this->removeOldPublications($oldPublications);
		}
		
		echo $this->pubCount . " Publications imported <br>";
=======
		// get id's of old publications
		$oldPublications = $this->getOldPublications();
		$persons = $this->getPersonWithRssUrl();
		t3lib_div::debug($persons);
		if ($persons != ''){
			foreach($persons as $this->currentPerson){
				$rssXmlStr = $this->file_post_contents($this->currentPerson['rssUrl']);
				$rssXml = new SimpleXMLElement($rssXmlStr);
			
				if($rssXml != ''){
					$this->doImport($rssXml);
				}
			}
		}
		
		// remove old publications
		if($this->pubCount > 0){
			$this->removeOldPublications($oldPublications);
		}
		
		echo $this->pubCount . " Publications imported <br>";
>>>>>>> .r40720
		echo $this->errCount . " Errors";
	}
	
	
	/**
	 * Find all persons with current pid where rssUrl not null
	 * 
	 * @return array of persons
	 */
	private function getPersonWithRssUrl(){
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid, rssUrl',$this->tablePersDb,'rssUrl !=  \'\' AND pid = '.$this->personPid.' AND deleted = 0 AND hidden = 0');
	}
	
	/**
	 * 
	 */
	private function getOldPublications(){
		$pubs = array();
		foreach ($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid',$this->tablePublDb,'pid = '.$this->pubPid) as $pub){
			$pubs[] = $pub['uid'];
		}
		return $pubs;
	}
	
	/**
	 * Removes all publications of the given pid
	 * Removes also all mm entries
	 */
	function removeOldPublications($pubIds){
		// dont delete old ones
		return;
		
		if($pubIds != ''){
		$pubs = implode(",",$pubIds);
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->tablePublDbMMAuthor,'uid_local IN ('.$pubs.')');
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->tablePublDb,'pid = '.$this->pubPid.' AND uid IN ('.$pubs.')');
		}
	}
	
	/**
	 * Processes the import
	 * 
	 * @param $rssXml the xml file with the rss content
	 */
	public function doImport($rssXml){
		foreach ($rssXml->channel->item as $pub){
			$pubArray = array (
				'pid' => $this->pubPid,
				'crdate' => time(),
				'year' => date("Y"),
				'title' => (string) $pub->title,
				'category_sub' => $this->category,
				'description' => (string) $pub->description,
				'file_ref' => (string) $pub->link
			);
			$this->savePublication($pubArray);
		}
	}
	
	/**
	 * saves all given data to the DB
	 * 
	 * @param $pubDataArray  an array of field => value combinations
	 */
	private function savePublication($pubDataArray){
		// check if publication already exists
		$pubCount = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(*) as c',$this->tablePublDb,'file_ref = "'.$pubDataArray['file_ref'].'" AND deleted = 0');
		if ($pubCount[0]['c']==0) {
			if($GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tablePublDb,$pubDataArray)){
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->tablePublDbMMAuthor,
					array(
						'uid_local' => $GLOBALS['TYPO3_DB']->sql_insert_id(), 
						'uid_foreign' => $this->currentPerson['uid'])
				);
				$this->pubCount++;
			} else {
				$this->errCount++;
			}
		}
	}
	
	/**
	 * We need this function to write the content of an xml/url to a string
	 * because the function file_get_contents is not allowed to get external files
	 * 
	 * @param $url the url of the file to read
	 * @param $headers 
	 * @param $user optional user for the webserver
	 * @param $pass optional password for the webserver
	 * 
	 * @return string with the contents of the file
	 */
	public function file_post_contents($url,$headers=FALSE,$user='',$pass='') {
	    $url = parse_url($url);
	    if (!isset($url['port'])) {
	    	if ($url['scheme'] == 'http') { 
	    		$url['port']=80; 
	    	} elseif ($url['scheme'] == 'https') { 
	      		$url['port']=443; 
	      	} elseif ($url['scheme'] == 'feed') { 
	      		$url['scheme']='http';
	      		$url['port']=80; 
	      	}
	    }
	    $url['query']=isset($url['query'])?$url['query']:'';
	
	    $url['protocol']=$url['scheme'].'://';
	    $eol="\r\n";
	
	    $auth =  ($user != '') ? "Authorization: Basic ".base64_encode("$user:$pass").$eol : '';
	    $headers =  "GET ".$url['protocol'].$url['host'].$url['path']."?".$url['query']." HTTP/1.0".$eol.
	                "Host: ".$url['host'].$eol.
	                "Referer: ".$url['protocol'].$url['host'].$url['path'].$eol.
	                "Content-Type: application/x-www-form-urlencoded".$eol.
	                "Content-Length: ".strlen($url['query']).$eol.
	               	$auth.
	                $eol.$url['query'];
	    $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
	    
		if($fp) {
	      	fputs($fp, $headers);
	      	$result = '';
	      	while(!feof($fp)) { 
	      		$result .= fgets($fp, 128); 
	      	}
	      	fclose($fp);
		  
	  		// Remove headers
	    	$pattern="/^(.+?)\r\n\r\n(.+)/s"; //as per RFC
	        $result=preg_match($pattern,$result,$matches);
	        if (!empty($matches[1])) {
	        	$headers=$matches[1];
	        }
	        if (!empty($matches[2])) {
	       		return $matches[2];
			}
	    }	
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/tasks/class.tx_x4epublication_rsspubimport.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/tasks/class.tx_x4epublication_rsspubimport.php']);
}
?>