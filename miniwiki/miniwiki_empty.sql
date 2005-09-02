-- $Id$

CREATE TABLE pages (
  name varchar(100) NOT NULL default '',
  revision int(11) NOT NULL default '0',
  content text,
  last_modified timestamp(14) NOT NULL,
  message varchar(250) default NULL,
  user varchar(100) default NULL,
  PRIMARY KEY  (name,revision)
);

CREATE TABLE users (
  name varchar(100) NOT NULL default '',
  password varchar(32) default NULL,
  PRIMARY KEY  (name)
);

CREATE TABLE uploads (
  name varchar(100) NOT NULL default '',
  revision int(11) NOT NULL default '0',
  content mediumblob,
  last_modified timestamp(14) NOT NULL,
  message varchar(250) default NULL,
  user varchar(100) default NULL,
  PRIMARY KEY  (name,revision)
);

INSERT INTO users VALUES ('admin','21232f297a57a5a743894a0e4a801fc3');
