<?php

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=="BE"){
	include_once(t3lib_extMgm::extPath("x4epublication")."class.tx_x4epublication_tx_x4epublication_tca_proc.php");
}

$TCA["tx_x4epublication_category_main"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_category_main",
		"label" => "title",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_x4epublication_category_main.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title",
	)
);

$TCA["tx_x4epublication_category_sub"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_category_sub",
		"label" => "title",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"languageField"            =>"sys_language_uid",    
        "transOrigPointerField"    => "l18n_parent",    
        "transOrigDiffSourceField" => "l18n_diffsource",
		"sortby" => "sorting",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_x4epublication_category_sub.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource,hidden, title, cat_main, show_columns",
	)
);

$TCA["tx_x4epublication_publication"] = Array (
	"ctrl" => Array (
		"title" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication",
		"label" => "title",
		"tstamp" => "tstamp",
		"crdate" => "crdate",
		"cruser_id" => "cruser_id",
		"sortby" => "sorting",
		"delete" => "deleted",
		"enablecolumns" => Array (
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_x4epublication_publication.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, category_sub, authors, authors_ext, publishers, title, location, run, year, volume, magazine_title, magazine_year, magazine_issue, anthology_title, anthology_publisher, pages, event_date, other_redaction",
	)
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';


t3lib_extMgm::addPlugin(Array('LLL:EXT:x4epublication/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPlugin(Array('LLL:EXT:x4epublication/locallang_db.php:tt_content.list_type_publselect', $_EXTKEY.'_publselect'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','Publications view');


if (TYPO3_MODE=="BE") {
	require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_x4epublication_itemproc.php');
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages';

t3lib_extMgm::addPlugin(Array('LLL:EXT:x4epublication/locallang_db.php:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","Publications entry");
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:x4epublication/pi1/flexform_ds.xml');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:x4epublication/pi2/flexform_ds.xml');
?>