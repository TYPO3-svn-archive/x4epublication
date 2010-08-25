<?php

########################################################################
# Extension Manager/Repository config file for ext: "x4epublication"
#
# Auto generated 10-05-2010 16:57
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => '4eyes - Publication Database',
	'description' => 'Datebase to show publications of fe_users (e.g. Members of universities) on your website',
	'category' => 'plugin',
	'author' => 'Markus Stauffiger',
	'author_email' => 'markus@4eyes.ch',
	'shy' => '',
	'dependencies' => 'x4epibase,x4epersdb',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '4eyes GmbH',
	'version' => '1.0.3',
	'_md5_values_when_last_written' => 'a:243:{s:9:"ChangeLog";s:4:"dc2f";s:10:"README.txt";s:4:"9fa9";s:36:"class.tx_x4epublication_itemproc.php";s:4:"8c6d";s:54:"class.tx_x4epublication_tx_x4epublication_tca_proc.php";s:4:"e86e";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"ca6a";s:14:"ext_tables.php";s:4:"e58c";s:14:"ext_tables.sql";s:4:"b660";s:24:"ext_typoscript_setup.txt";s:4:"95ea";s:40:"icon_tx_x4epublication_category_main.gif";s:4:"475a";s:39:"icon_tx_x4epublication_category_sub.gif";s:4:"475a";s:38:"icon_tx_x4epublication_publication.gif";s:4:"475a";s:13:"locallang.php";s:4:"6afd";s:16:"locallang_db.php";s:4:"89a9";s:7:"tca.php";s:4:"2b30";s:17:"ufpdf/LICENSE.txt";s:4:"393a";s:16:"ufpdf/README.txt";s:4:"0f1b";s:24:"ufpdf/class.pdfTable.php";s:4:"c4e9";s:14:"ufpdf/fpdf.css";s:4:"490a";s:14:"ufpdf/fpdf.php";s:4:"78eb";s:20:"ufpdf/ufpdf-test.php";s:4:"afc0";s:15:"ufpdf/ufpdf.php";s:4:"e6ce";s:20:"ufpdf/ufpdf_ital.php";s:4:"1e32";s:17:"ufpdf/unicode.pdf";s:4:"dcd2";s:27:"ufpdf/tools/makefontuni.php";s:4:"4399";s:23:"ufpdf/tools/ttf2ufm.exe";s:4:"3324";s:30:"ufpdf/ttf2ufm-src/CHANGES.html";s:4:"683b";s:27:"ufpdf/ttf2ufm-src/COPYRIGHT";s:4:"d4fe";s:33:"ufpdf/ttf2ufm-src/FONTS.hpux.html";s:4:"8fc9";s:28:"ufpdf/ttf2ufm-src/FONTS.html";s:4:"7381";s:26:"ufpdf/ttf2ufm-src/Makefile";s:4:"8d91";s:30:"ufpdf/ttf2ufm-src/README.FIRST";s:4:"1c8e";s:29:"ufpdf/ttf2ufm-src/README.html";s:4:"eff4";s:23:"ufpdf/ttf2ufm-src/bdf.c";s:4:"2ac3";s:26:"ufpdf/ttf2ufm-src/bitmap.c";s:4:"eea6";s:29:"ufpdf/ttf2ufm-src/byteorder.h";s:4:"1fc1";s:29:"ufpdf/ttf2ufm-src/cygbuild.sh";s:4:"0fb5";s:22:"ufpdf/ttf2ufm-src/ft.c";s:4:"5814";s:26:"ufpdf/ttf2ufm-src/global.h";s:4:"4530";s:23:"ufpdf/ttf2ufm-src/pt1.c";s:4:"fc34";s:23:"ufpdf/ttf2ufm-src/pt1.h";s:4:"cde2";s:28:"ufpdf/ttf2ufm-src/runt1asm.c";s:4:"7a75";s:25:"ufpdf/ttf2ufm-src/t1asm.c";s:4:"5f35";s:23:"ufpdf/ttf2ufm-src/ttf.c";s:4:"f3b5";s:23:"ufpdf/ttf2ufm-src/ttf.h";s:4:"d8a2";s:27:"ufpdf/ttf2ufm-src/ttf2pt1.1";s:4:"bc17";s:27:"ufpdf/ttf2ufm-src/ttf2pt1.c";s:4:"1aac";s:35:"ufpdf/ttf2ufm-src/ttf2pt1_convert.1";s:4:"69f2";s:32:"ufpdf/ttf2ufm-src/ttf2pt1_x2gs.1";s:4:"5880";s:27:"ufpdf/ttf2ufm-src/version.h";s:4:"f05f";s:30:"ufpdf/ttf2ufm-src/winbuild.bat";s:4:"2811";s:27:"ufpdf/ttf2ufm-src/windows.h";s:4:"bd6d";s:39:"ufpdf/ttf2ufm-src/encodings/README.html";s:4:"81bc";s:44:"ufpdf/ttf2ufm-src/encodings/latin4/iso8859-4";s:4:"d41d";s:48:"ufpdf/ttf2ufm-src/encodings/latin4/iso8859-4.tbl";s:4:"d41d";s:44:"ufpdf/ttf2ufm-src/encodings/latin5/iso8859-9";s:4:"d41d";s:44:"ufpdf/ttf2ufm-src/encodings/bulgarian/README";s:4:"6bef";s:53:"ufpdf/ttf2ufm-src/encodings/bulgarian/encodings.alias";s:4:"b04b";s:50:"ufpdf/ttf2ufm-src/encodings/bulgarian/ibm-1251.tbl";s:4:"ff38";s:49:"ufpdf/ttf2ufm-src/encodings/bulgarian/ibm-866.tbl";s:4:"596e";s:51:"ufpdf/ttf2ufm-src/encodings/bulgarian/iso8859-5.tbl";s:4:"c803";s:48:"ufpdf/ttf2ufm-src/encodings/bulgarian/koi8-r.tbl";s:4:"500a";s:42:"ufpdf/ttf2ufm-src/encodings/russian/README";s:4:"6bef";s:51:"ufpdf/ttf2ufm-src/encodings/russian/encodings.alias";s:4:"b04b";s:48:"ufpdf/ttf2ufm-src/encodings/russian/ibm-1251.tbl";s:4:"ff38";s:47:"ufpdf/ttf2ufm-src/encodings/russian/ibm-866.tbl";s:4:"596e";s:49:"ufpdf/ttf2ufm-src/encodings/russian/iso8859-5.tbl";s:4:"c803";s:46:"ufpdf/ttf2ufm-src/encodings/russian/koi8-r.tbl";s:4:"500a";s:50:"ufpdf/ttf2ufm-src/encodings/adobestd/adobe-std.tbl";s:4:"d41d";s:52:"ufpdf/ttf2ufm-src/encodings/cyrillic/encodings.alias";s:4:"b04b";s:49:"ufpdf/ttf2ufm-src/encodings/cyrillic/ibm-1251.tbl";s:4:"ff38";s:48:"ufpdf/ttf2ufm-src/encodings/cyrillic/ibm-866.tbl";s:4:"596e";s:50:"ufpdf/ttf2ufm-src/encodings/cyrillic/iso8859-5.tbl";s:4:"c803";s:47:"ufpdf/ttf2ufm-src/encodings/cyrillic/koi8-r.tbl";s:4:"500a";s:48:"ufpdf/ttf2ufm-src/encodings/latin2/iso8859-2.tbl";s:4:"d41d";s:48:"ufpdf/ttf2ufm-src/encodings/latin1/iso8859-1.tbl";s:4:"d41d";s:39:"ufpdf/ttf2ufm-src/app/netscape/Makefile";s:4:"1aa0";s:42:"ufpdf/ttf2ufm-src/app/netscape/README.html";s:4:"dcae";s:40:"ufpdf/ttf2ufm-src/app/netscape/fontsz.cf";s:4:"6816";s:39:"ufpdf/ttf2ufm-src/app/netscape/notscape";s:4:"6174";s:39:"ufpdf/ttf2ufm-src/app/netscape/nsfilter";s:4:"aa2f";s:38:"ufpdf/ttf2ufm-src/app/netscape/nsfix.c";s:4:"3d7f";s:35:"ufpdf/ttf2ufm-src/app/netscape/nspr";s:4:"882f";s:38:"ufpdf/ttf2ufm-src/app/netscape/nsprint";s:4:"352b";s:41:"ufpdf/ttf2ufm-src/app/netscape/psfonts.cf";s:4:"4ead";s:37:"ufpdf/ttf2ufm-src/app/TeX/README.html";s:4:"eb82";s:42:"ufpdf/ttf2ufm-src/app/TeX/cjk-latex-config";s:4:"133a";s:44:"ufpdf/ttf2ufm-src/app/TeX/cjk-latex-t1mapgen";s:4:"5a66";s:33:"ufpdf/ttf2ufm-src/app/TeX/sfd2map";s:4:"4eb7";s:42:"ufpdf/ttf2ufm-src/app/RPM/ttf2pt1.spec.src";s:4:"64e7";s:37:"ufpdf/ttf2ufm-src/app/X11/README.html";s:4:"3468";s:43:"ufpdf/ttf2ufm-src/app/X11/t1-xf86.334.patch";s:4:"df15";s:42:"ufpdf/ttf2ufm-src/app/X11/t1-xf86.39.patch";s:4:"3a83";s:33:"ufpdf/ttf2ufm-src/scripts/convert";s:4:"4b62";s:44:"ufpdf/ttf2ufm-src/scripts/convert.cfg.sample";s:4:"3057";s:34:"ufpdf/ttf2ufm-src/scripts/forceiso";s:4:"d41f";s:33:"ufpdf/ttf2ufm-src/scripts/frommap";s:4:"8ef3";s:34:"ufpdf/ttf2ufm-src/scripts/html2man";s:4:"f053";s:34:"ufpdf/ttf2ufm-src/scripts/inst_dir";s:4:"370c";s:35:"ufpdf/ttf2ufm-src/scripts/inst_file";s:4:"620f";s:31:"ufpdf/ttf2ufm-src/scripts/mkrel";s:4:"42ac";s:32:"ufpdf/ttf2ufm-src/scripts/t1fdir";s:4:"0543";s:31:"ufpdf/ttf2ufm-src/scripts/trans";s:4:"db19";s:32:"ufpdf/ttf2ufm-src/scripts/unhtml";s:4:"daa7";s:30:"ufpdf/ttf2ufm-src/scripts/x2gs";s:4:"71d9";s:33:"ufpdf/ttf2ufm-src/maps/CP1250.map";s:4:"3608";s:33:"ufpdf/ttf2ufm-src/maps/CP1251.map";s:4:"bd1b";s:38:"ufpdf/ttf2ufm-src/maps/T2A_compact.map";s:4:"c606";s:50:"ufpdf/ttf2ufm-src/maps/adobe-standard-encoding.map";s:4:"cd7b";s:41:"ufpdf/ttf2ufm-src/maps/unicode-sample.map";s:4:"b6b6";s:32:"ufpdf/ttf2ufm-src/other/Makefile";s:4:"1793";s:35:"ufpdf/ttf2ufm-src/other/README.html";s:4:"e447";s:33:"ufpdf/ttf2ufm-src/other/bmpfont.h";s:4:"78a9";s:28:"ufpdf/ttf2ufm-src/other/bz.c";s:4:"06ce";s:34:"ufpdf/ttf2ufm-src/other/bzscreen.c";s:4:"e660";s:34:"ufpdf/ttf2ufm-src/other/bzscreen.h";s:4:"0185";s:30:"ufpdf/ttf2ufm-src/other/cmpf.c";s:4:"7ad2";s:35:"ufpdf/ttf2ufm-src/other/cntstems.pl";s:4:"2c86";s:30:"ufpdf/ttf2ufm-src/other/dmpf.c";s:4:"7260";s:30:"ufpdf/ttf2ufm-src/other/lst.pl";s:4:"666b";s:30:"ufpdf/ttf2ufm-src/other/showdf";s:4:"82f7";s:29:"ufpdf/ttf2ufm-src/other/showg";s:4:"030b";s:25:"ufpdf/font/FreeSans.ctg.z";s:4:"56ba";s:23:"ufpdf/font/FreeSans.php";s:4:"35a2";s:21:"ufpdf/font/FreeSans.z";s:4:"bf32";s:29:"ufpdf/font/FreeSansBold.ctg.z";s:4:"75da";s:27:"ufpdf/font/FreeSansBold.php";s:4:"0bf4";s:25:"ufpdf/font/FreeSansBold.z";s:4:"11c4";s:26:"ufpdf/font/placeholder.txt";s:4:"2722";s:18:"templates/add.html";s:4:"c949";s:27:"templates/authorSearch.html";s:4:"3e19";s:27:"templates/author_search.css";s:4:"3857";s:32:"templates/choosepublication.html";s:4:"0b6b";s:17:"templates/code.js";s:4:"01b6";s:19:"templates/list.html";s:4:"766d";s:19:"templates/menu.html";s:4:"a283";s:32:"templates/publicationSearch.html";s:4:"2281";s:27:"templates/publications.html";s:4:"f42b";s:30:"templates/publisherSearch.html";s:4:"1fab";s:21:"templates/search.html";s:4:"2f49";s:24:"templates/singleView.php";s:4:"9b02";s:19:"templates/style.css";s:4:"c3c4";s:27:"templates/images/delete.gif";s:4:"7b5c";s:25:"templates/images/down.gif";s:4:"1655";s:24:"templates/images/new.gif";s:4:"2354";s:23:"templates/images/up.gif";s:4:"7b3b";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:26:"pi1/class.pdfTextTable.php";s:4:"4da3";s:35:"pi1/class.tx_x4epublication_pi1.php";s:4:"b478";s:43:"pi1/class.tx_x4epublication_pi1_wizicon.php";s:4:"3285";s:13:"pi1/clear.gif";s:4:"cc11";s:19:"pi1/flexform_ds.xml";s:4:"3e41";s:19:"pi1/generatePdf.php";s:4:"2bb0";s:21:"pi1/generateRepec.php";s:4:"e83a";s:19:"pi1/generateTxt.php";s:4:"e0b8";s:17:"pi1/locallang.php";s:4:"e3e5";s:22:"pi1/locallang_flex.php";s:4:"43d3";s:28:"pi1/static/editorcfg.txt.old";s:4:"6151";s:17:"rtf/class_rtf.php";s:4:"9492";s:14:"rtf/getrtf.php";s:4:"951f";s:18:"rtf/rtf_config.php";s:4:"fd17";s:18:"rtf/source_rtf.php";s:4:"d719";s:49:"publselect/class.tx_x4epublication_publselect.php";s:4:"0bfa";s:20:"publselect/clear.gif";s:4:"cc11";s:24:"publselect/locallang.php";s:4:"c582";s:12:"fpdi/LICENSE";s:4:"3b83";s:11:"fpdi/NOTICE";s:4:"4202";s:23:"fpdi/class.pdfTable.php";s:4:"990b";s:13:"fpdi/demo.php";s:4:"de34";s:13:"fpdi/fpdf.php";s:4:"9c04";s:17:"fpdi/fpdf_tpl.php";s:4:"7624";s:13:"fpdi/fpdi.php";s:4:"4f7e";s:24:"fpdi/fpdi_pdf_parser.php";s:4:"bcfc";s:20:"fpdi/pdf_context.php";s:4:"4e4e";s:19:"fpdi/pdf_parser.php";s:4:"2bd6";s:26:"fpdi/wrapper_functions.php";s:4:"ddd5";s:25:"fpdi/decoders/ascii85.php";s:4:"d0d6";s:21:"fpdi/decoders/lzw.php";s:4:"40ab";s:21:"fpdi/font/courier.php";s:4:"fc24";s:23:"fpdi/font/helvetica.php";s:4:"18a8";s:24:"fpdi/font/helveticab.php";s:4:"5363";s:25:"fpdi/font/helveticabi.php";s:4:"8eba";s:24:"fpdi/font/helveticai.php";s:4:"54e8";s:20:"fpdi/font/symbol.php";s:4:"56b0";s:19:"fpdi/font/times.php";s:4:"bbf9";s:20:"fpdi/font/timesb.php";s:4:"6704";s:21:"fpdi/font/timesbi.php";s:4:"7295";s:20:"fpdi/font/timesi.php";s:4:"4ff5";s:26:"fpdi/font/zapfdingbats.php";s:4:"0529";s:29:"fpdi/font/makefont/cp1250.map";s:4:"8a02";s:29:"fpdi/font/makefont/cp1251.map";s:4:"ee2f";s:29:"fpdi/font/makefont/cp1252.map";s:4:"8d73";s:29:"fpdi/font/makefont/cp1253.map";s:4:"9073";s:29:"fpdi/font/makefont/cp1254.map";s:4:"46e4";s:29:"fpdi/font/makefont/cp1255.map";s:4:"c469";s:29:"fpdi/font/makefont/cp1257.map";s:4:"fe87";s:29:"fpdi/font/makefont/cp1258.map";s:4:"86a4";s:28:"fpdi/font/makefont/cp874.map";s:4:"4fba";s:33:"fpdi/font/makefont/iso-8859-1.map";s:4:"53bf";s:34:"fpdi/font/makefont/iso-8859-11.map";s:4:"83ec";s:34:"fpdi/font/makefont/iso-8859-15.map";s:4:"3d09";s:34:"fpdi/font/makefont/iso-8859-16.map";s:4:"b56b";s:33:"fpdi/font/makefont/iso-8859-2.map";s:4:"4750";s:33:"fpdi/font/makefont/iso-8859-4.map";s:4:"0355";s:33:"fpdi/font/makefont/iso-8859-5.map";s:4:"82a2";s:33:"fpdi/font/makefont/iso-8859-7.map";s:4:"d071";s:33:"fpdi/font/makefont/iso-8859-9.map";s:4:"8647";s:29:"fpdi/font/makefont/koi8-r.map";s:4:"04f5";s:29:"fpdi/font/makefont/koi8-u.map";s:4:"9046";s:31:"fpdi/font/makefont/makefont.php";s:4:"eff3";s:17:"images/delete.gif";s:4:"7b5c";s:15:"images/down.gif";s:4:"1655";s:14:"images/new.gif";s:4:"2354";s:13:"images/up.gif";s:4:"7b3b";s:35:"pi2/class.tx_x4epublication_pi2.php";s:4:"8ead";s:19:"pi2/flexform_ds.xml";s:4:"5534";s:17:"pi2/locallang.php";s:4:"30d1";s:21:"pi2/locallang.php.old";s:4:"ff48";s:22:"pi2/locallang_flex.php";s:4:"9af9";s:14:"pi2/search.php";s:4:"0610";s:24:"pi2/static/editorcfg.txt";s:4:"0b9c";s:18:"scripts/config.php";s:4:"5a8b";s:22:"scripts/config.php_old";s:4:"71e5";s:20:"scripts/debuglib.php";s:4:"69e5";s:18:"scripts/import.php";s:4:"0c98";s:25:"scripts/import.php_060210";s:4:"f6a2";s:25:"scripts/import.php_090110";s:4:"17fc";s:22:"scripts/import.php_old";s:4:"ad9d";s:21:"scripts/inittypo3.php";s:4:"6dfd";s:20:"scripts/mappings.php";s:4:"cb3f";s:25:"scripts/rssPub_import.php";s:4:"ac1c";s:21:"fpdf/font/courier.php";s:4:"fc24";s:23:"fpdf/font/helvetica.php";s:4:"18a8";s:24:"fpdf/font/helveticab.php";s:4:"5363";s:25:"fpdf/font/helveticabi.php";s:4:"8eba";s:20:"fpdf/font/symbol.php";s:4:"56b0";s:19:"fpdf/font/times.php";s:4:"bbf9";s:20:"fpdf/font/timesb.php";s:4:"6704";s:20:"fpdf/font/timesi.php";s:4:"4ff5";s:26:"fpdf/font/zapfdingbats.php";s:4:"0529";s:34:"fpdf/font/makefont/iso-8859-15.map";s:4:"3d09";s:34:"fpdf/font/makefont/iso-8859-16.map";s:4:"b56b";s:33:"fpdf/font/makefont/iso-8859-2.map";s:4:"4750";}',
	'constraints' => array(
		'depends' => array(
			'x4epibase' => '',
			'x4epersdb' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>