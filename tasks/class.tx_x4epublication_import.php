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
class tx_x4epublication_import extends tx_scheduler_Task {

	var $debug = 0;
	var $verbose = 0;
	var $showxml = 0;
	
	var $_EXTKEY = 'x4epublication';
	var $pubpid;
	var $perspid;
	var $oaiuser;
	var $oaipw;
	var $cat_matching;
	var $mapping;
	var $config;
	
	public function execute() {
		$this->debug = $_GET['debug'];
		$this->verbose = $_GET['verbose'];
		$this->showxml = $_GET['showxml'];
		
		$this->mapping = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->_EXTKEY]['pubMapping'][$this->pubpid];
		$this->config = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->_EXTKEY]['configPubImport'];
		if(empty($this->mapping) || empty($this->config)) {
			$this->addMessage('Mapping or config empty', t3lib_FlashMessage::ERROR);
			return false;
		} 
		
		echo "Running import for '" . $this->mapping['title'] . "':<br/>"; 
		
		// Get all records of specific institute
		$url = $this->oaiurl . '&metadataPrefix=fdb_pub&filterStatus=PBL&filterOrgExt=' . $this->mapping['rdborgid_from'] . '&filterOrgExtTo=' .$this->mapping['rdborgid_to'];
		$resumptionurl = $this->oaiurl . '&resumptionToken=';
		
		// Get xmlstr over oai
		$xmlstr = $this->file_post_contents($url,false,$this->oaiuser,$this->oaipw);
		if(strpos($xmlstr,"401 Authorization Required")!==false) {
			$this->addMessage('Authorization failed. Wrong User/PW or URL.', t3lib_FlashMessage::ERROR);
			return false;
		}
		$xmlstr = str_replace("iso-8859-1", "utf-8", $xmlstr);
		$xmlstr = iconv("windows-1252", "utf-8", $xmlstr);
		
		if ($this->showxml) print_r(($xmlstr));
		$xmlArr = array();
		$import = array();
		$xmlArr[0] = new SimpleXMLElement($xmlstr);
		$count = 0;
		
		// if an resumptionToken is set get further data
		while($xmlArr[$count]->ListRecords->resumptionToken != '' && $count < $this->config['maxresumptionToken']){
			$url = $resumptionurl . $xmlArr[$count]->ListRecords->resumptionToken[0];
			$xmlstr = $this->file_post_contents($url,false,$this->oaiuser,$this->oaip);
			if(strpos($xmlstr,"401 Authorization Required")!==false) {
			$this->addMessage('Authorization failed. Wrong User/PW or URL.', t3lib_FlashMessage::ERROR);
			return false;
		}
			$xmlstr = str_replace("iso-8859-1", "utf-8", $xmlstr);
			$xmlstr = iconv("windows-1252", "utf-8", $xmlstr);
			if ($this->showxml) print_r(($xmlstr));
			$count++;
			$xmlArr[$count] = new SimpleXMLElement($xmlstr);
		}
		
		foreach ($xmlArr as $xml){
			//t3lib_div::debug($xml);
			// Schleife inklusive Namespace :dc und Namespace :fdb
			foreach ($xml->ListRecords->record as $record) {
				$ns_dc = $record->metadata->children('http://purl.org/forschdb_publication/');
				$tmp['dc'] = $ns_dc->children('http://purl.org/dc/elements/1.1/');
				$tmp['fdb'] = $ns_dc->children('http://purl.org/forschdb_publication/');
				
				//Change charset to ISO 8859-1
				//array_walk_recursive($tmp, 'utf8_conv');
				//print_r($tmp);
				//xml childrens in array einfügen, Format abhängig von key
				$tmpArray = array();
				foreach($tmp as $key => $value){
					foreach($value as $skey => $svalue) {
						
						switch($skey){
							case 'creator':
							case 'editor':
								//$svalue = html_entity_decode($svalue, ENT_QUOTES, "utf-8");
								if($this->config['u8toIso']) $svalue = utf8_decode($svalue);
								$svalue = html_entity_decode($svalue);
//								$svalue = htmlspecialchars_decode($svalue, ENT_QUOTES);
								if (array_key_exists($skey,$tmpArray)) $tmpArray[$skey] .= ";". trim($svalue);
								else $tmpArray[$skey] = trim($svalue);
								//t3lib_div::debug($tmpArray[$skey]);
							break;
							case 'unibasauthor':
							case 'unibaseditor':
							case 'unibascreator':
								foreach($svalue as $akey => $avalue){
									if($this->config['u8toIso']) $avalue = utf8_decode($avalue);
									$avalue = html_entity_decode($avalue);
									switch($akey){										
										case 'unibasauthor_dni':
										case 'unibaseditor_dni':
										case 'unibascreator_dni':
										case 'unibasauthor_mcssid':
										case 'unibaseditor_dni':
										case 'unibascreator_mcssid':
											if (array_key_exists($akey,$tmpArray)) $tmpArray[$akey] .= ",".trim($avalue);
											else $tmpArray[$akey] = trim($avalue);
										break;
										default:
											if (array_key_exists($akey,$tmpArray)) $tmpArray[$akey] .= trim((string)($avalue));
											else $tmpArray[$akey] = trim((string)($avalue));
										break;
									}
								}
							break;
							case 'type':
								foreach($svalue->attributes() as $okey => $ovalue){
									if($this->config['u8toIso']) $ovalue = utf8_decode($ovalue);
									$ovalue = html_entity_decode($ovalue);
									if (array_key_exists($okey,$tmpArray)) $tmpArray[$okey] .= trim((string)($ovalue));
									else $tmpArray[$okey] = trim((string)($ovalue));
							}
							break;
							case 'rdborgid':
								if($this->config['u8toIso']) $svalue = utf8_decode($svalue);
								if (array_key_exists($skey,$tmpArray)) $tmpArray[$skey] .= "," . trim((string)($svalue));
								else $tmpArray[$skey] = trim((string)($svalue));
							break;
							
							/*case 'title':
								t3lib_div::debug($svalue);
								t3lib_div::debug(html_entity_decode($svalue));
							*/
							default:
//								$svalue = str_replace('∕', '/', $svalue);
								$svalue = str_replace('&#8260;', '/', $svalue);
								$svalue = html_entity_decode($svalue, ENT_QUOTES, "utf-8");
								//t3lib_div::debug($svalue);
								if($this->config['u8toIso']) $svalue = utf8_decode($svalue);
								if (array_key_exists($skey,$tmpArray)) $tmpArray[$skey] .= trim((string)($svalue));
								else $tmpArray[$skey] = trim((string)($svalue));
							break;
						}
					}
				}
				$tmpArray['personPid'] = $this->mapping['pid_pers'];
				$import[]= $tmpArray;
			}
		}
				
		$ret = $this->doImport($import);
		//Ausgabe der Anzahl behandelten Records
		if ($this->verbose)echo "***<br/>";
		t3lib_div::debug("Inserted: <b>" . $ret['inserted'] . " |</b> Updated: <b>" . $ret['updated'] . " |</b> Deleted: <b>" . $ret['deleted']. " |</b> Failed: <b>" . $ret['failed'] . "</b><br/>");
	
	
		mail("michel@4eyes.ch", "PubDB Import", $this->pubpid . " - PubDB Import beendet am ".date("m.d.y - H:i:s"),"from:awbk");
		if(intval($ret['failed']) > 0 ) {
			return false;
		} else {
			return true;
		}
	}
	
	function doImport($input){
		$count = array( 'updated' => 0, 'inserted' => 0, 'failed' => 0, 'deleted' => 0);
		//Alle fdb_ids aus der Publicationendb zur überprüfung ob bereits vorhanden
		$fdb_ids = array();
		
		//removed hidden = 1, michel 151209
		$fdb = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('fdb_id',$this->config['tablePublDb'],'fdb_id != 0 AND deleted = 0','','fdb_id ASC');
		
		//used to check if record is still in FDB
		$procFdbIds = array();
		foreach($fdb as $f) $fdb_ids[] = $f['fdb_id'];
		foreach($input as $record){
			$mArr = array();
			$author = '';
			$editor = '';
			$authors = '';
			$publisher = '';
			$author = $this->matchPersons($this->parseNames($record['creator']), $record['unibasauthor_dni'], $record['unibasauthor_mcssid'], $record['personPid']);
			$publisher = $this->matchPersons($this->parseNames($record['editor']), $record['unibaseditor_dni'], $record['unibaseditor_mcssid'], $record['personPid']);
			//$author = array_merge($authors,$editors);
			
			// 14.10.09 Hack: Because Edited Books have no authors we define publisher as authors!
			if ($record['otid'] == 8){
				$author = $publisher;
				$publisher = '';
			}
			
			//$publisher = matchPersons(parseNames($record['publisher']), $record['unibascreator_dni']);
			$mArr['pid'] = $this->mapping['pid_publ'];
			$mArr['tstamp'] = strtotime($record['lastupdate']);
			$mArr['title'] = ($record['title']);
			$mArr['description'] = ($record['description']);
			$mArr['year'] = $record['date'];
			$mArr['year_comment'] = $record['date_comment'];
			$mArr['category_sub'] = ($this->mapping['cat_matching'][$record['pubtype_weboffice']])?$this->mapping['cat_matching'][$record['pubtype_weboffice']]:0;
			if($mArr['category_sub'] == 0) $mArr['category_sub'] = ($this->mapping['cat_matching'][$record['otid']])?$this->mapping['cat_matching'][$record['otid']]:0;
			$mArr['hidden'] = ($mArr['category_sub'] == 0) ? 1 : 0; 
			$mArr['authors_ext'] = ($author['ext']);
			$mArr['author_sorting'] = $author['sorting'];
			$mArr['author_sorting_text'] = ($author['sorting_text']);	
			$mArr['publishers_ext'] = ($publisher['ext']);
			$mArr['publisher_sorting'] = $publisher['sorting'];
			$mArr['publisher_sorting_text'] = ($publisher['sorting_text']);
			$mArr['rdborgid'] = $record['rdborgid'];
			
			// Additional specific data:
			$mArr['location'] = ($record['place_of_publication']) ?  $record['place_of_publication']: '';
			$mArr['anthology_title'] = ($record['booktitle']) ?  $record['booktitle']: '';
			$mArr['magazine_title'] = ($record['newspaper_title']) ?  $record['newspaper_title']: '';
			if ($mArr['magazine_title'] == '') $mArr['magazine_title'] = ($record['journal']) ?  $record['journal']: '';
			$mArr['magazine_issue'] = ($record['issue']) ?  $record['issue']: '';
			$mArr['magazine_year'] = ($record['volume']) ?  $record['volume']: '';
			$mArr['daymonth'] = ($record['month_day']) ?  $record['month_day']: '';
	//		$mArr['volume'] = ($record['volume']) ? $record['volume'] : ($record['volume_number']) ?  $record['volume_number']: '';
			$mArr['volume'] = ($record['volume_number']) ?  $record['volume_number']: ($record['series_title']) ?  $record['series_title']: '';
			$mArr['run'] = ($record['edition']) ?  $record['edition']: '';
			$mArr['pages'] = ($record['pages']) ?  $record['pages']: '';
			$mArr['url'] = ($record['url']) ?  $record['url']: '';
			$mArr['other_redaction'] = ($record['addpublicationtranslation']) ?  $record['addpublicationtranslation']: '';
			//$mArr['isbn'] = ($record['issn_isbn']) ?  $record['issn_isbn']: ''; //not in db
			
			// fdb identifier
			$mArr['fdb_id'] = $record['identifier'];
			
			//insert or update mArr to publDB
			if (in_array($mArr['fdb_id'], $fdb_ids)){
				if($GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->config['tablePublDb'],'fdb_id = '. $mArr['fdb_id'],$mArr)){
					if ($this->verbose)echo $mArr['fdb_id'].": UPDATED <br/>";
					$count['updated']++;
				} else {
					if ($this->verbose)echo $mArr['fdb_id'].": FAILED <br/>";
					$count['failed']++;
				}
			} else {
				$mArr['crdate'] = time();
				if($GLOBALS['TYPO3_DB']->exec_INSERTquery($this->config['tablePublDb'],$mArr)){
					if ($this->verbose)echo $mArr['fdb_id'].": INSERTED </br>";
					$count['inserted']++;
				} else {
					if ($this->verbose)echo $mArr['fdb_id'].": FAILED <br/>";
					$count['failed']++;
				}
			}
			$procFdbIds[] = $mArr['fdb_id'];
			
			
			//add relations to mm tables (author and publisher)
			$recUid = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid',$this->config['tablePublDb'],'fdb_id = '.$mArr['fdb_id'].' AND deleted = 0 AND hidden = 0');
			if($recUid){
				$recUid = $recUid[0]['uid'];
				//remove old relations first: 
				
				 	if($GLOBALS['TYPO3_DB']->exec_DELETEquery($this->config['tablePublDbMMAuthor'],'uid_local = '.$recUid)){
						if ($this->verbose) echo " - Author relations removed </br>";
				}
					if($GLOBALS['TYPO3_DB']->exec_DELETEquery($this->config['tablePublDbMMPublisher'],'uid_local = '.$recUid)){
						if ($this->verbose) echo " - Publisher relations removed </br>";
				}
				
				//add new relations:
				foreach(explode(",",$author['int']) as $a_id){
					if ($a_id != '') { 
						if($GLOBALS['TYPO3_DB']->exec_INSERTquery($this->config['tablePublDbMMAuthor'],array('uid_local' => $recUid, 'uid_foreign' => $a_id))){
							if ($this->verbose) echo " + Author relations created </br>";
						}
					}
				}
				foreach(explode(",",$publisher['int']) as $p_id){
					if ($p_id != ''){
						if($GLOBALS['TYPO3_DB']->exec_INSERTquery($this->config['tablePublDbMMPublisher'],array('uid_local' => $recUid, 'uid_foreign' => $p_id))){
							if ($this->verbose) echo " + Publisher relations created </br>";
						}
					}
				}
				
			}
			
			//t3lib_div::debug($mArr);
		}
		//delete publications
		$count['deleted'] = 0; //deleteOldPublications($procFdbIds);
		return $count;
		
	}

	// Es werden alle Publicationen gelöscht welche eine fdb_id besitzen aber nicht im array $fdbids vorhanden sind
	// Dies muss beachtet werden falls der Import angepasst wird, zB nur abrufen von bestimmten Publicationen.
	function deleteOldPublications($fdbids){
		$count = 0;
		$WHERE = 'pid = '.$this->mapping['pid_publ'] . ' AND fdb_id != 0 AND fdb_id NOT IN ('.implode(",", $fdbids).')';
		$delPubs = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('fdb_id',$this->config['tablePublDb'],$WHERE);
		
		if($GLOBALS['TYPO3_DB']->exec_DELETEquery($this->config['tablePublDb'],$WHERE )){
			foreach($delPubs as $pub){
				if ($this->verbose)echo $pub['fdb_id'].": DELETED </br>";
				$count++;
			}
		}
		
		//t3lib_div::debug($delPubs);
		return $count;
	}
	function parseNames($cStr = ''){
		if ($cStr == '') return;
	//	$cArr = explode(';', trim($cStr));
		$cArr = $this->cond_explode(';', trim($cStr), '&');
		$names = array();
		if (is_array($cArr)){
			foreach($cArr as $key => $value){
				$p = explode(',', trim($value));
				//$p[0] = str_replace(chr(20), "", ($p[0]));
				//t3lib_div::debug(trim($p[0],"\xc2\xa0"), "lastname");
				$names[] = array('lastname' => trim($p[0], "\xc2\xa0"), 'firstname' => trim($p[1], "\xc2\xa0"));
				//t3lib_div::debug($names);
			}
		} else {
			$p = explode(',', trim($cArr));
			$names[] = array('lastname' => $p[0], 'firstname' => $p[1]); 
		}
		//t3lib_div::debug($names);
		return $names;
	}
	
	function matchPersons($persons, $int_ids, $int_mcssids, $personPid = 0){
		$sorting = '';
		$sorting_text = array();
		$ext = '';
		$int = '';
		$int_ids = ($int_ids) ? $int_ids : -1;
		$int_mcssids = ($int_mcssids) ? $int_mcssids : -1;
		$ext_ct = 1;
		$pidRestriction = ($personPid != 0) ? ' AND pid = ' . $personPid . ' ' : '';
		$pers_int = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid, lastname, firstname',$this->config['tablePersDb'],'(dni IN ('.$int_ids.') OR mcss_id IN ('.$int_mcssids.')) AND deleted = 0 AND hidden = 0'.$pidRestriction);
		foreach($pers_int as $i){
			$int .= $i['uid'].",";
		}
		
		if ($persons != ''){
			//t3lib_div::debug($persons);
			foreach($persons as $key => $person){
				$is_int = false;
				// Check if name matches
				
				foreach($pers_int as $i){
					if (trim(strtolower(($i['lastname']))) == trim(strtolower($person['lastname'])) && trim(strtolower(($i['firstname']))) == trim(strtolower($person['firstname']))){
						$sorting .= $i['uid'].",";
						$sorting_text[] = ($i['lastname']).", ".($i['firstname']);
						$is_int = true;
						break;
					}
				}
				if (!$is_int){
					// If name not matched before check for short firstnames
					foreach($pers_int as $i){
						if (trim(strtolower(($i['lastname']))) == trim(strtolower($person['lastname'])) && substr(trim(strtolower(($i['firstname']))),0,1) == substr(trim(strtolower($person['firstname'])),0,1)){
							$sorting .= $i['uid'].",";
							$sorting_text[] = ($i['lastname']).", ".($i['firstname']);
							$is_int = true;
							break;
						}
					}
				}
				if(!$is_int){
					$sorting .= 'ext-'.$ext_ct.",";
					$sorting_text[] = $person['lastname'].",".$person['firstname'];
					$ext .= $person['lastname'].",".$person['firstname'].",".$ext_ct."\n";
					$ext_ct++;
				}
			}
		}
		
		return array('sorting' => trim($sorting, ',') , 'sorting_text' => implode("; ",$sorting_text), 'ext' => $ext, 'int' => $int);
	}
	
	function cond_explode($token, $str, $cond = '&'){
		if(strpos($str, $cond)>0){
			$pos = 0;
			$offset = 0;
			$arr = array(); 
			$tpos = strpos($str,$token,$offset);
			$max = 0;
			
			while ($tpos != false && $max++ < 5){
				$t = substr($str, $pos, $tpos-$pos);
				if(preg_match('/[0-9]$/', $t)==0){
					$arr[] = $t;
					$pos = $tpos+1;
					$offset = $pos;
				}else {
					$offset = $tpos+1;
				}
				$tpos = strpos($str,$token,$offset);
			}
			if(sizeof($arr) == 0) $arr[] = $str;
			return $arr;
		} else {
			return explode($token, $str);
		}
	}
	
	
	function xml2phpArray($xml,$arr){
	    $iter = 0;
	        foreach($xml->children() as $b){
	                $a = $b->getName();
	                if(!$b->children()){
	                        $arr[$a] = trim($b[0]);
	                }
	                else{
	                        $arr[$a][$iter] = array();
	                        $arr[$a][$iter] = xml2phpArray($b,$arr[$a][$iter]);
	                }
	        $iter++;
	        }
	        return $arr;
	} 
	
	function file_post_contents($url,$headers=false,$user='',$pass='') {
	    $url = parse_url($url);
		
	    if (!isset($url['port'])) {
	      if ($url['scheme'] == 'http') { $url['port']=80; }
	      elseif ($url['scheme'] == 'https') { $url['port']=443; }
	    }
	    $url['query']=isset($url['query'])?$url['query']:'';
	
	    $url['protocol']=$url['scheme'].'://';
	    $eol="\r\n";
	
	    $headers =  "GET ".$url['protocol'].$url['host'].$url['path']."?".$url['query']." HTTP/1.0".$eol.
	                "Host: ".$url['host'].$eol.
	                "Referer: ".$url['protocol'].$url['host'].$url['path'].$eol.
	                "Content-Type: application/x-www-form-urlencoded".$eol.
	                "Content-Length: ".strlen($url['query']).$eol.
	                "Authorization: Basic ".base64_encode("$user:$pass").$eol.
	                $eol.$url['query'];
	    $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);
	    
		if($fp) {
	      fputs($fp, $headers);
	      $result = '';
	      while(!feof($fp)) { $result .= fgets($fp, 4096); }
	      fclose($fp);
		  
		  
		  
	      //old "remove headers" which had errors
		  /*
	        //removes headers
	        $pattern="/^.*\r\n\r\n/s";
			echo "pos:" . strstr($result, $pattern);
	        $result=preg_replace($pattern,'',$result,1);
	      */
		 
		  // new "remove headers" looks like this version works:
		  if (1) {
		  	  	$pattern="/^(.+?)\r\n\r\n(.+)/s"; //as per RFC
		  	  	//print_r($result);
		        $result=preg_match($pattern,$result,$matches);
		        if (!empty($matches[1])) $headers=$matches[1];
		        if (!empty($matches[2])) return $matches[2];
		  }
	      //return $result;
	    }
		
	
	}
	
	function utf8_conv(&$item, $key){ // Wichtig: Die Referenz übergeben
	  if(is_string($item)){
	  	$item = utf8_decode($item);
	  }
	}
	
	
	
	public function getAdditionalInformation() {
        return 'PUBPID: '.$this->pubpid . 
        ', OAIUSER: ' . $this->oaiuser . 
        ', OAIPW: ' . ' *****';
    }
    
	public function addMessage($text, $severity){
		$message = t3lib_div::makeInstance(
		't3lib_FlashMessage',
			$text,
			'',
			$severity
		);
		t3lib_FlashMessageQueue::addMessage($message);
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/tasks/class.tx_x4epublication_import.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/tasks/class.tx_x4epublication_import.php']);
}
?>