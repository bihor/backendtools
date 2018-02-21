#
# Table structure for table 'tx_backendtools_domain_model_session'
#
CREATE TABLE tx_backendtools_domain_model_session (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	action varchar(255) DEFAULT '' NOT NULL,
	value1 int(11) DEFAULT '0' NOT NULL,
	value2 int(11) DEFAULT '0' NOT NULL,
	value3 int(11) DEFAULT '0' NOT NULL,
	value4 varchar(255) DEFAULT '' NOT NULL,
	value5 varchar(255) DEFAULT '' NOT NULL,
	value6 varchar(255) DEFAULT '' NOT NULL,
	pageel int(11) DEFAULT '0' NOT NULL,
	beuser int(11) unsigned DEFAULT '0',

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),

);
