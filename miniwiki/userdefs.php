<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY
  
  /** @file
   * user definitions
   */
  
  /** database host name */
  $mw_db_host = 'localhost';
  /** database user name */
  $mw_db_user = 'miniwiki';
  /** database user password */
  $mw_db_pass = 'miniwiki';
  /** database name */
  $mw_db_name = 'miniwiki';
  /** HTTP Basic Auth realm name */
  $mw_auth_realm = 'miniWiki';
  /** HTML output encoding */
  $mw_encoding = 'iso-8859-2';
  /** database encoding (must represent the same encoding as mw_encoding) */
  $mw_db_encoding = 'latin2';
  /** whether to let database sort strings according to its settings or according to wanted mw_db_encoding */
  $mw_db_use_server_collation = true;

  /** whether reading is allowed only for logged users */
  $auth_read_logged_only = false;
  /** whether changing pages is allowed only for administrator */
  $auth_write_admin_only = false;

  /** list of MW_Extension classes which should not be initialized */
  $disabled_extensions = array(
  );

?>
