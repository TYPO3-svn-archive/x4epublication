<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_'.$_EXTKEY.'_category_main=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_'.$_EXTKEY.'_category_sub=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_'.$_EXTKEY.'_pi1 = < plugin.tx_'.$_EXTKEY.'_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_'.$_EXTKEY.'_pi1.php','_pi1','list_type',1);


t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_'.$_EXTKEY.'_publication = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1
	tt_content.shortcut.20.0.conf.tx_'.$_EXTKEY.'_publication.CMD = singleView
',43);

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_'.$_EXTKEY.'_pi2 = < plugin.tx_'.$_EXTKEY.'_pi2.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_'.$_EXTKEY.'_pi2.php','_pi2','list_type',1);

t3lib_extMgm::addPItoST43($_EXTKEY,'publselect/class.tx_x4epublication_publselect.php','_publselect','list_type',1);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_x4epublication_import'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'Publication import',
    'description'      => 'imports new or updated publications',
	'additionalFields' => 'tx_x4epublication_import_additionalfieldprovider'
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_x4epublication_rsspubimport'] = array(
    'extension'        => $_EXTKEY,
    'title'            => 'RSS Publication import',
    'description'      => 'imports new or updated publications',
	'additionalFields' => 'tx_x4epublication_RssPubImport_AdditionalFieldProvider'
);

?>