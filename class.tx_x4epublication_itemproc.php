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
/**
 * Class/Function which manipulates the item-array for the listing
 *
 * @author	Markus Stauffiger <markus-at-4eyes.ch>
 */
class tx_x4epublication_itemproc {

	/**
	 * Adding fe_users field list to selector box array
	 *
	 * @param	array		Parameters, changing "items". Passed by reference.
	 * @param	object		Parent object
	 * @return	void
	 */
	function main(&$params,&$pObj)	{
		global $TCA;
		$tableName = 'tx_publics_publication';
		t3lib_div::loadTCA($tableName);

		$params['items']=array();
		if (is_array($TCA[$tableName]['columns']))	{
			foreach($TCA[$tableName]['columns'] as $key => $config)	{
				if ($config['label'] && !t3lib_div::inList('password',$key))	{
					$label = t3lib_div::fixed_lgd(ereg_replace(':$','',$GLOBALS['LANG']->sL($config['label'])),30).' ('.$key.')';
					$params['items'][]=Array($label, $key);
				}
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/class.tx_x4epublication_itemproc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4epublication/class.tx_x4epublication_itemproc.php']);
}
?>