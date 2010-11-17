<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_x4epublication_category_main"] = Array (
	"ctrl" => $TCA["tx_x4epublication_category_main"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title"
	),
	"feInterface" => $TCA["tx_x4epublication_category_main"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_category_main.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_x4epublication_category_sub"] = Array (
	"ctrl" => $TCA["tx_x4epublication_category_sub"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title,title_plural,cat_main,show_columns"
	),
	"feInterface" => $TCA["tx_x4epublication_category_sub"]["feInterface"],
	"columns" => Array (
		"sys_language_uid" => array (        
            "exclude" => 1,
            "label"  => "LLL:EXT:lang/locallang_general.php:LGL.language",
            "config" => array (
                "type"                => "select",
                "foreign_table"       => "sys_language",
                "foreign_table_where" => "ORDER BY sys_language.title",
                "items" => array(
                    array("LLL:EXT:lang/locallang_general.xml:LGL.allLanguages", -1),
                    array("LLL:EXT:lang/locallang_general.xml:LGL.default_value", 0)
                )
            )
        ),
        "l18n_parent" => array (        
            "displayCond" => "FIELD:sys_language_uid:>:0",
            "exclude"     => 1,
            "label"       => "LLL:EXT:lang/locallang_general.php:LGL.l18n_parent",
            "config"      => array (
                "type"  => "select",
                "items" => array (
                    array("", 0),
                ),
                "foreign_table"       => "tx_x4epublication_category_sub",
                "foreign_table_where" => "AND tx_x4epublication_category_sub.pid=###CURRENT_PID### AND tx_x4epublication_category_sub.sys_language_uid IN (-1,0)",
            )
        ),
        "l18n_diffsource" => array (        
            "config" => array (
                "type" => "passthrough"
            )
        ),

		"hidden" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_category_sub.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"title_plural" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_category_sub.title_plural",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"cat_main" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_category_sub.cat_main",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "tx_x4epublication_category_main",
				"foreign_table_where" => "  AND tx_x4epublication_category_main.pid = ###CURRENT_PID### ORDER BY tx_x4epublication_category_main.uid",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"show_columns" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_category_sub.show_columns",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "15",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource,hidden;;1;;1-1-1, title;;;;2-2-2, title_plural, cat_main;;;;3-3-3, show_columns")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);



$TCA["tx_x4epublication_publication"] = Array (
	"ctrl" => $TCA["tx_x4epublication_publication"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,category_sub,authors,authors_ext,publishers,title,location,run,year,volume,magazine_title,magazine_year,magazine_issue,anthology_title,anthology_publisher,pages,event_date,other_redaction"
	),
	"feInterface" => $TCA["tx_x4epublication_publication"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.php:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"category_sub" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.category_sub",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "tx_x4epublication_category_sub",
				"foreign_table_where" => " AND tx_x4epublication_category_sub.pid = ###CURRENT_PID### ORDER BY tx_x4epublication_category_sub.uid",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"auth_publ" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.auth_publ",
            "config" => Array (
                "type" => "radio",
                "items" => Array (
                    Array("LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.auth_publ.I.0", "0"),
                    Array("LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.auth_publ.I.1", "1"),
                ),
            )
        ),
		"authors" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.authors",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "tx_x4epersdb_person",
				"foreign_table_where" => "AND tx_x4epersdb_person.pid=###PAGE_TSCONFIG_ID### and sys_language_uid = 0 ORDER BY tx_x4epersdb_person.lastname",
				"size" => 10,
				"minitems" => 0,
				"maxitems" => 100,
				"MM" => "tx_x4epublication_publication_persons_auth_mm",
				"itemsProcFunc" => "tx_x4epublication_tx_x4epublication_tca_proc->main",
			)
		),
		"authors_ext" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.authors_ext",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"publishers" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.publishers",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "tx_x4epersdb_person",
				"foreign_table_where" => "AND tx_x4epersdb_person.pid=###PAGE_TSCONFIG_ID### and sys_language_uid = 0 ORDER BY tx_x4epersdb_person.lastname",
				"size" => 10,
				"minitems" => 0,
				"maxitems" => 100,
				"MM" => "tx_x4epublication_publication_persons_publ_mm",
				"itemsProcFunc" => "tx_x4epublication_tx_x4epublication_tca_proc->main",
			)
		),
		"publishers_ext" => Array (
			"exclude" => 0,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.publishers_ext",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.title",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "3",
				"eval" => "required",
			)
		),
		"location" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.location",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"run" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.run",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"year" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.year",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required",
			)
		),
		"volume" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.volume",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"magazine_title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.magazine_title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"magazine_year" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.magazine_year",
			"config" => Array (
				"type" => "input",
				"size" => "5",
				"max" => "4",
			)
		),
		"magazine_issue" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.magazine_issue",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"abstract" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.abstract",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => Array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		/*"department_id" => Array (
			'l10n_mode' => 'exclude',
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.department_id",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "tx_x4edivision_divisiongroup",
				"foreign_table_where" => "AND tx_x4edivision_divisiongroup.sys_language_uid = 0 AND tx_x4edivision_divisiongroup.pid=4996",
				"size" => 8,
				"minitems" => 1,
				"maxitems" => 1,
			)
		),*/
		"anthology_title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.anthology_title",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"anthology_publisher" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.anthology_publisher",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"keywords" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.keywords",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "2",
			)
		),
		"jel_classification" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.jel_classification",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "2",
			)
		),
		"pages" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.pages",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"event_date" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.event_date",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"other_redaction" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.other_redaction",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"description" => Array (
			"exclude" => 0,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.description",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"url" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.url",
			"config" => Array (
				"type" => "input",
				"size" => "15",
				"max" => "255",
				"checkbox" => "",
				"eval" => "trim",
				"wizards" => Array(
					"_PADDING" => 2,
					"link" => Array(
						"type" => "popup",
						"title" => "Link",
						"icon" => "link_popup.gif",
						"script" => "browse_links.php?mode=wizard",
						"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
					)
				)
			)
		),
		"daymonth" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.daymonth",
			"config" => Array (
				"type" => "input",
				"size" => "15",
				"max" => "255",
				"checkbox" => "",
				"eval" => "trim",
			)
		),
		"impact" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.impact",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"publ_company" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.publ_company",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"file_ref" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.file_ref",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "1",
			)
		),
		"pub_language" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:x4epublication/locallang_db.php:tx_x4epublication_publication.pub_language",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "static_languages",
				"foreign_table_where" => " AND static_languages.uid IN (###PAGE_TSCONFIG_STR###) ORDER BY static_languages.lg_name_en",
				"size" => 1,
				"minitems" => 1,
				"maxitems" => 1,
				"default" => 30,
			)
		),
	),
	"types" => Array (
		//"0" => Array("showitem" => "hidden;;1;;1-1-1, category_sub, auth_publ, authors, authors_ext, publishers, publishers_ext, title;;;;2-2-2, location;;;;3-3-3, run, year, pub_language, volume, magazine_title, magazine_year, magazine_issue, anthology_title, anthology_publisher,abstract;;;richtext[paste|bold|italic|formatblock|class|orderedlist|unorderedlist|link]:rte_transform[flag=rte_enabled|mode=ts], pages, event_date, other_redaction, department_id, keywords, jel_classification, daymonth, url, file_ref, impact, publ_company")
		"0" => Array("showitem" => "hidden;;1;;1-1-1, category_sub, auth_publ, authors, authors_ext, publishers, publishers_ext, title, description, location;;;;3-3-3, run, year, pub_language, volume, magazine_title, magazine_year, magazine_issue, anthology_title, anthology_publisher,abstract;;;richtext[paste|bold|italic|formatblock|class|orderedlist|unorderedlist|link]:rte_transform[flag=rte_enabled|mode=ts], pages, event_date, other_redaction, keywords, jel_classification, daymonth, url, file_ref, impact, publ_company")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
?>