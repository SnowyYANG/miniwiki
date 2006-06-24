<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY
  
  /** @file
  * global settings
  */
  
  # texts array definition

  define("MWT_UNKNOWN_ACTION", 0);
  define("MWT_PAGE_DELETED", 1);
  define("MWT_PAGE_UPDATED", 2);
  define("MWT_REVISION", 3);
  define("MWT_EDITING", 4);
  define("MWT_ACTION_VIEW", 5);
  define("MWT_ACTION_EDIT", 6);
  define("MWT_ACTION_DELETE", 7);
  define("MWT_ACTION_HISTORY", 8);
  define("MWT_LAST_MODIFIED", 9);
  define("MWT_EDIT_MESSAGE", 10);
  define("MWT_EDIT_SUBMIT", 11);
  define("MWT_ACTION_LOGIN", 12);
  define("MWT_LOGGED_AS", 13);
  define("MWT_LOGIN_INVALID", 14);
  define("MWT_ACCESS_DENIED", 15);
  define("MWT_EMPTY_PAGE", 16);
  define("MWT_CREATE_USER_BUTTON", 17);
  define("MWT_USER_CREATED", 18);
  define("MWT_USER_DELETED", 19);
  define("MWT_DELETE_USER_BUTTON", 20);
  define("MWT_CHANGE_PASSWORD_BUTTON", 21);
  define("MWT_PASSWORD_CHANGED", 22);
  define("MWT_ACTION_VIEW_SOURCE", 23);
  define("MWT_VIEWING", 24);
  define("MWT_EDIT_PREVIEW", 25);
  define("MWT_UPLOAD_PAGE_TEXT", 26);
  define("MWT_SOURCE_FILENAME", 27);
  define("MWT_DEST_FILENAME", 28);
  define("MWT_UPLOAD_BUTTON", 29);
  define("MWT_UPLOAD_MESSAGE", 30);
  define("MWT_FILE_UPLOADED", 31);
  define("MWT_PAGE_NOT_UPDATED", 32);
  define("MWT_ACTION_UPDATE", MWT_ACTION_EDIT);
  define("MWT_ACTION_RELOGIN", MWT_ACTION_LOGIN);
  define("MWT_ACTION_CHANGE_PASSWORD", MWT_CHANGE_PASSWORD_BUTTON);
  define("MWT_ACTION_CREATE_USER", MWT_CREATE_USER_BUTTON);
  define("MWT_ACTION_DELETE_USER", MWT_DELETE_USER_BUTTON);
  define("MWT_ACTION_UPLOAD", MWT_UPLOAD_BUTTON);
  
  $mw_texts = array(
    MWT_UNKNOWN_ACTION => "Unknown action.",
    MWT_PAGE_DELETED => "Page deleted.",
    MWT_PAGE_UPDATED => "Page updated.",
    MWT_REVISION => "Revision",
    MWT_EDITING => "Editing",
    MWT_ACTION_VIEW => 'View',
    MWT_ACTION_EDIT => 'Edit',
    MWT_ACTION_DELETE => 'Delete',
    MWT_ACTION_HISTORY => 'History',
    MWT_LAST_MODIFIED => 'Last modified',
    MWT_EDIT_MESSAGE => 'Edit message',
    MWT_EDIT_SUBMIT => 'Update Page',
    MWT_ACTION_LOGIN => 'Login',
    MWT_LOGGED_AS => 'Logged as',
    MWT_LOGIN_INVALID => 'Invalid login.',
    MWT_ACCESS_DENIED => 'Insufficient user rights. Access denied to action: ',
    MWT_EMPTY_PAGE => '(empty)',
    MWT_CREATE_USER_BUTTON => 'Create User',
    MWT_USER_CREATED => 'User was created.',
    MWT_USER_DELETED => 'User was deleted.',
    MWT_DELETE_USER_BUTTON => 'Delete User',
    MWT_CHANGE_PASSWORD_BUTTON => 'Change Password',
    MWT_PASSWORD_CHANGED => 'Password was changed',
    MWT_ACTION_VIEW_SOURCE => 'View Source',
    MWT_VIEWING => "Viewing",
    MWT_EDIT_PREVIEW => "Preview",
    MWT_UPLOAD_PAGE_TEXT => "''This page represents uploaded file named '''%FILENAME%''' (of type %MIMETYPE% and size %LENGTH% B).''
    
[[%LINK%|Download file %FILENAME%]]

%MESSAGE%
    
---

For uploading new version, please, use '''Edit''' link at the bottom.",
    MWT_SOURCE_FILENAME => "Source filename: ",
    MWT_DEST_FILENAME => "Destination filename (may be empty): ",
    MWT_UPLOAD_BUTTON => "Upload",
    MWT_UPLOAD_MESSAGE => "Upload message: ",
    MWT_FILE_UPLOADED => "File uploaded.",
    MWT_PAGE_NOT_UPDATED => "No edits. Page was not updated.",
  );
  
  /**
  * returns information file name for given user (for use by user_info wiki function)
  */
  function get_user_info_file($user) {
    return 'users/'.$user;
  }

?>
