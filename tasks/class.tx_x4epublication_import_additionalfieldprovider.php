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
class tx_x4epublication_Import_AdditionalFieldProvider implements tx_scheduler_AdditionalFieldProvider {
    public function getAdditionalFields(array &$taskInfo,$task, tx_scheduler_Module $parentObject) { 	
		// Initialize extra field value
		if (empty($taskInfo['pubpid'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['pubpid'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['pubpid'] = $task->pubpid;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['pubpid'] = '';
			}
		}
		
		if (empty($taskInfo['oaiuser'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['oaiuser'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['oaiuser'] = $task->oaiuser;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['oaiuser'] = '';
			}
		}
		
		if (empty($taskInfo['oaipw'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['oaipw'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['oaipw'] = $task->oaipw;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['oaipw'] = '';
			}
		}
		
		if (empty($taskInfo['oaiurl'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['oaiurl'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['oaiurl'] = $task->oaiurl;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['oaiurl'] = '';
			}
		}

			// Write the code for the field
		$pubIdFieldID = 'task_pubpid';
		$pubIdFieldCode = '<input type="text" name="tx_scheduler[pubpid]" id="' . $pubIdFieldID . '" value="' . $taskInfo['pubpid'] . '" size="10" />';
		
		$oaiUserFieldID = 'task_oaiuser';
		$oaiUserFieldCode = '<input type="text" name="tx_scheduler[oaiuser]" id="' . $oaiUserFieldID . '" value="' . $taskInfo['oaiuser'] . '" size="30" />';
		
		$oaiPwFieldID = 'task_oaipw';
		$oaiPwFieldCode = '<input type="password" name="tx_scheduler[oaipw]" id="' . $oaiPwFieldID . '" value="' . $taskInfo['oaipw'] . '" size="30" />';
		
		$oaiUrlFieldID = 'task_oaiurl';
		$oaiUrlFieldCode = '<input type="text" name="tx_scheduler[oaiurl]" id="' . $oaiUrlFieldID . '" value="' . $taskInfo['oaiurl'] . '" size="50" />';
		
		
		$additionalFields = array();
		$additionalFields[$pubIdFieldID] = array(
			'code'     => $pubIdFieldCode,
			'label'    => 'Publication PID',
			'cshLabel' => $pubIdFieldID
		);
		
		$additionalFields[$oaiUserFieldID] = array(
			'code'     => $oaiUserFieldCode,
			'label'    => 'OAI Import User',
			'cshLabel' => $oaiUserFieldID
		);
		
		$additionalFields[$oaiPwFieldID] = array(
			'code'     => $oaiPwFieldCode,
			'label'    => 'OAI Import Pw',
			'cshLabel' => $oaiPwFieldID
		);
		
		$additionalFields[$oaiUrlFieldID] = array(
			'code'     => $oaiUrlFieldCode,
			'label'    => 'OAI Import Url',
			'cshLabel' => $oaiUrlFieldID
		);

		return $additionalFields;
	}

	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		$submittedData['pubpid'] = trim($submittedData['pubpid']);
		$submittedData['oaiuser'] = trim($submittedData['oaiuser']);
		$submittedData['oaipw'] = trim($submittedData['oaipw']);
		$submittedData['oaiurl'] = trim($submittedData['oaiurl']);

		if (empty($submittedData['pubpid'])) {
			$parentObject->addMessage('No publication pid given', t3lib_FlashMessage::ERROR);
			$result = false;
		} else if (empty($submittedData['oaiuser'])){
			$parentObject->addMessage('No oai user given', t3lib_FlashMessage::ERROR);
			$result = false;
		} else if (empty($submittedData['oaipw'])){
			$parentObject->addMessage('No oai pw given', t3lib_FlashMessage::ERROR);
			$result = false;
		} else if (empty($submittedData['oaiurl'])){
			$parentObject->addMessage('No oai url given', t3lib_FlashMessage::ERROR);
			$result = false;
		}
		else {
			$result = true;
		}
		return $result;
    }

    public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->pubpid = $submittedData['pubpid'];
		$task->oaiuser = $submittedData['oaiuser'];
		$task->oaipw = $submittedData['oaipw'];
		$task->oaiurl = $submittedData['oaiurl'];
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/tasks/class.tx_x4epublication_import_additionalfieldprovider.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/tasks/class.tx_x4epublication_import_additionalfieldprovider.php']);
}
?>