<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY
  
  /** @file
   * user definitions
   */
  
  /** database host name (default is localhost) */
  $mw_db_host = 'localhost';
  /** database user name (default is miniwiki) */
  $mw_db_user = 'miniwiki';
  /** database user password (default is miniwiki) */
  $mw_db_pass = 'miniwiki';
  /** database name (default is miniwiki) */
  $mw_db_name = 'miniwiki';
  /** HTTP Basic Auth realm name (default is miniWiki) */
  $mw_auth_realm = 'miniWiki';
  /** HTML output encoding (default is utf-8) */
  $mw_encoding = 'iso-8859-2';
  /** database encoding (must represent the same encoding as mw_encoding; default is utf8) */
  $mw_db_encoding = 'latin2';
  /** whether to let database sort strings according to its settings or according to wanted mw_db_encoding (default is true) */
  $mw_db_use_server_collation = true;

  /** whether reading is allowed only for logged users (default is false) */
  $mw_auth_read_logged_only = false;
  /** whether changing pages is allowed only for administrator (default is false) */
  $mw_auth_write_admin_only = false;

  /** list of MW_Extension classes which should not be initialized (default is empty) */
  $mw_disabled_extensions = array(
  );

?>
