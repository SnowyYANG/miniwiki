<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY
  
  /** @file
   * user definitions
   */
  
  /** name of this Wiki (will be shown in page title etc.; default is miniWiki) */
  $mw_wiki_name = 'miniWiki';
  
  /** database settings for MySQL storage (extension MW_CoreMySQLStorageExtension, enabled by default) */
  /** database host name (default is localhost) */
  $mw_db_host = 'localhost';
  /** database user name (default is miniwiki) */
  $mw_db_user = 'miniwiki';
  /** database user password (default is miniwiki) */
  $mw_db_pass = 'miniwiki';
  /** database name (default is miniwiki) */
  $mw_db_name = 'miniwiki';
  /** HTTP Basic Auth realm name (default is the same as mw_wiki_name) */
  $mw_auth_realm = $mw_wiki_name;
  /** HTML output encoding (default is utf-8) */
  $mw_encoding = 'iso-8859-2';
  /** database encoding (must represent the same encoding as mw_encoding; default is utf8) */
  $mw_db_encoding = 'latin2';
  /** whether to let database sort strings according to its settings or according to wanted mw_db_encoding (default is true) */
  $mw_db_use_server_collation = true;

  /** root directory for filesystem storage (extension MW_CoreFilesystemStorageExtension, disabled by default) */
#  $mw_fs_root_dir = '';

  /** date/time format string (default is %Y/%m/%d %H:%M:%S) */
  $mw_datetime_format = "%Y/%m/%d %H:%M:%S";

  /** whether reading is allowed only for logged users (default is false) */
  $mw_auth_read_logged_only = false;
  /** whether changing pages is allowed only for administrator (default is false) */
  $mw_auth_write_admin_only = false;

  /** list of MW_Extension classes which should be enabled or disabled (for default see lib/miniwiki.php) */
#  $mw_enabled_MW_CoreMySQLStorageExtension = false;
#  $mw_enabled_MW_CoreFilesystemStorageExtension = true;

  /**
  * returns information file name for given user (for use by user_info wiki function)
  */
  function get_user_info_file($user) {
    return 'users/'.$user;
  }

  /** callback which will return location of file with information about user (for use by user_info wiki function; default is null) */
  $mw_user_info_file_callback = 'get_user_info_file';

  /** name of UI layout to use (stored in MW/Layout/LAYOUT/*; default is Default) */
#  $mw_layout = '';

?>
