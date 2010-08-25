<?php
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
?>