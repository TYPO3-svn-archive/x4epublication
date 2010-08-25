<?php

class tx_x4epublication_RssPubImport_AdditionalFieldProvider implements tx_scheduler_AdditionalFieldProvider {
    
    
    
    public function getAdditionalFields(array &$taskInfo,$task, tx_scheduler_Module $parentObject) { 	
		
		// Initialize extra field value
		if (empty($taskInfo['pubPid'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['pubPid'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['pubPid'] = $task->pubPid;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['pubPid'] = '';
			}
		}
		
		if (empty($taskInfo['personPid'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default sleep time
				$taskInfo['personPid'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, set to internal value if no data was submitted already
				$taskInfo['personPid'] = $task->personPid;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['personPid'] = '';
			}
		}
		
		

			// Write the code for the field
		$pubPidFieldID = 'task_pubPid';
		$pubPidFieldCode = '<input type="text" name="tx_scheduler[pubPid]" id="' . $pubPidFieldID . '" value="' . $taskInfo['pubPid'] . '" size="10" />';
		
		$personPidFieldID = 'task_personPid';
		$personPidFieldCode = '<input type="text" name="tx_scheduler[personPid]" id="' . $personPidFieldID . '" value="' . $taskInfo['personPid'] . '" size="10" />';
		
		
		$additionalFields = array();
		$additionalFields[$pubPidFieldID] = array(
			'code'     => $pubPidFieldCode,
			'label'    => 'Publication PID',
			'cshLabel' => $pubPidFieldID
		);
		
		$additionalFields[$personPidFieldID] = array(
			'code'     => $personPidFieldCode,
			'label'    => 'Person PID',
			'cshLabel' => $personPidFieldID
		);

		return $additionalFields;
	}

	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		$submittedData['pubPid'] = trim($submittedData['pubPid']);
		$submittedData['personPid'] = trim($submittedData['personPid']);
		

		if (empty($submittedData['pubPid'])) {
			$parentObject->addMessage('No publication pid given', t3lib_FlashMessage::ERROR);
			$result = false;
		} else if (empty($submittedData['personPid'])){
			$parentObject->addMessage('No person pid given', t3lib_FlashMessage::ERROR);
			$result = false;
		} else {
			$result = true;
		}
		return $result;
    }

    public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->pubPid = $submittedData['pubPid'];
		$task->personPid = $submittedData['personPid'];
    }
}
?>