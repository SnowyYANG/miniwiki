-- $Id$

CREATE TABLE uploads (
  name varchar(100) NOT NULL default '',
  revision int(11) NOT NULL default '0',
  content mediumblob,
  last_modified timestamp(14) NOT NULL,
  message varchar(250) default NULL,
  user varchar(100) default NULL,
  PRIMARY KEY  (name,revision)
);
