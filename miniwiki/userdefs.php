<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY
  
  /** @file
   * user definitions
   */
  
  /* General */
  
  /** name of this Wiki (will be shown in page title etc.; default is miniWiki) */
  $mw_wiki_name = 'miniWiki';
  /** HTTP Basic Auth realm name (default is the same as mw_wiki_name) */
  $mw_auth_realm = $mw_wiki_name;
  /** HTML output encoding (default is utf-8) */
  $mw_encoding = 'utf-8';
  /** date/time format string (default is %Y/%m/%d %H:%M:%S) */
  $mw_datetime_format = "%Y/%m/%d %H:%M:%S";
  /** whether reading is allowed only for logged users (default is false) */
  $mw_auth_read_logged_only = false;
  /** whether changing pages is allowed only for administrator (default is false) */
  $mw_auth_write_admin_only = false;
  /** name of UI layout to use (stored in MW/Layout/LAYOUT/*; default is Default) */
  $mw_layout = 'Default';
  
  /* Storage - exactly one storage must be enabled */
  
  /** MySQL storage (enabled by default) */
  $mw_enabled_MW_CoreMySQLStorageExtension = true;
  /** database host name (default is localhost) */
  $mw_db_host = 'localhost';
  /** database user name (default is miniwiki) */
  $mw_db_user = 'miniwiki';
  /** database user password (default is miniwiki) */
  $mw_db_pass = 'miniwiki';
  /** database name (default is miniwiki) */
  $mw_db_name = 'miniwiki';
  /** database encoding (must represent the same encoding as mw_encoding; starting with MySQL 4.1 database will reencode between charsets if necessary; default is utf8) */
  $mw_db_encoding = 'utf8';
  /** whether to let database sort strings according to its settings or according to wanted mw_db_encoding (default is true) */
  $mw_db_use_server_collation = true;
  
  /** Filesystem storage (disabled by default) */
  $mw_enabled_MW_CoreFilesystemStorageExtension = false;
  /** root directory (default is not set) */
#  $mw_fs_root_dir = '';

  /** WebDAV storage (disabled by default; disable Core Users Manager if enabling this) */
  $mw_enabled_MW_WebDAVStorageExtension = false;
#  $mw_enabled_MW_CoreUsersManagerExtension = false;
  /** root URL (default is not set) */
#  $mw_webdav_root_url = '';
  
  /* Extensions */

  /** User Info extension (disabled by default) */
  $mw_enabled_MW_UserInfoExtension = false;
  /**
  * returns information file name for given user (for use by user_info wiki function)
  */
  function get_user_info_file($user) {
    return 'users/'.$user;
  }
  /** callback which will return location of file with information about user (for use by user_info wiki function; default is not set) */
  $mw_user_info_file_callback = 'get_user_info_file';

?>
