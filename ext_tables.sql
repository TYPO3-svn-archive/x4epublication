#
# Table structure for table 'tx_x4epublication_category_main'
#
CREATE TABLE tx_x4epublication_category_main (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

ALTER TABLE `tx_x4epublication_category_main` ADD INDEX ( `deleted` );
ALTER TABLE `tx_x4epublication_category_main` ADD INDEX ( `hidden` ) ;

#
# Table structure for table 'tx_x4epublication_category_sub'
#
CREATE TABLE tx_x4epublication_category_sub (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
    l18n_parent int(11) DEFAULT '0' NOT NULL,
    l18n_diffsource mediumblob NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	title_plural tinytext NOT NULL,
	cat_main int(11) DEFAULT '0' NOT NULL,
	show_columns text NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

ALTER TABLE `tx_x4epublication_category_sub` ADD INDEX ( `cat_main` ) ;
ALTER TABLE `tx_x4epublication_category_sub` ADD INDEX ( `deleted` ) ;
ALTER TABLE `tx_x4epublication_category_sub` ADD INDEX ( `hidden` );


#
# Table structure for table 'tx_x4epublication_publication_persons_auth_mm'
#
#
CREATE TABLE tx_x4epublication_publication_persons_auth_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);




#
# Table structure for table 'tx_x4epublication_publication_persons_publ_mm'
#
#
CREATE TABLE tx_x4epublication_publication_persons_publ_mm (
  uid_local int(11) DEFAULT '0' NOT NULL,
  uid_foreign int(11) DEFAULT '0' NOT NULL,
  tablenames varchar(30) DEFAULT '' NOT NULL,
  sorting int(11) DEFAULT '0' NOT NULL,
  KEY uid_local (uid_local),
  KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_x4epublication_publication'
#
CREATE TABLE tx_x4epublication_publication (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sorting int(10) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	category_sub int(11) DEFAULT '0' NOT NULL,
	auth_publ tinyint(4) DEFAULT '0' NOT NULL,
	authors int(11) DEFAULT '0' NOT NULL,
	authors_ext text NOT NULL,
	publishers int(11) DEFAULT '0' NOT NULL,
	publishers_ext text NOT NULL,
	title text NOT NULL,
	location tinytext NOT NULL,
	run tinytext NOT NULL,
	year tinytext NOT NULL,
	volume text NOT NULL,
	magazine_title tinytext NOT NULL,
	magazine_year tinytext NOT NULL,
	magazine_issue tinytext NOT NULL,
	anthology_title text NOT NULL,
	anthology_publisher tinytext NOT NULL,
	pages tinytext NOT NULL,
	event_date tinytext NOT NULL,
	other_redaction tinytext NOT NULL,
	daymonth tinytext NOT NULL,
	url tinytext NOT NULL,
	impact tinytext NOT NULL,
	publ_company tinytext NOT NULL,
	author_sorting text NOT NULL,
	author_sorting_text text NOT NULL,
	publisher_sorting text NOT NULL,
	publisher_sorting_text text NOT NULL,
	fdb_id int(11) DEFAULT '0' NOT NULL,
	description text NOT NULL,
	file_ref text NOT NULL,
	pub_language int(11) DEFAULT '0' NOT NULL,
	abstract text NOT NULL,
	keywords text NOT NULL,
	jel_classification text NOT NULL,
	department_id int(11) NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

ALTER TABLE `tx_x4epublication_publication` ADD INDEX ( `deleted` ) ;
ALTER TABLE `tx_x4epublication_publication` ADD INDEX ( `hidden` ) ;
ALTER TABLE `tx_x4epublication_publication` ADD INDEX ( `category_sub` ) ;
ALTER TABLE `tx_x4epublication_publication` ADD INDEX ( `pub_language` ) ;

#
# Table structure for table 'x4epersdb'
#
CREATE TABLE tx_x4epersdb_person (
	tx_x4epublication_displayselected tinyint DEFAULT 0 NOT NULL,
	tx_x4epublication_selectedpubls tinytext NOT NULL,
);