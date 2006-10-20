<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * Install/upgrade script (for use on web).
  */

  /** user name which allows access to this script */
  $install_user = 'install';
  /** password which allows access to this script - MUST BE SET BEFORE USE */
  $install_pass = null;
#  $install_pass = 'something';
  
  define("MW_DEBUG", false);
  
  /**
  * error handler
  */
  function error_handler($errno, $errstr, $errfile, $errline) {
    echo "<b>OOPS! Something is wrong: $errstr</b><br/>(error code $errno, file $errfile, line $errline)<br/><br/>\n";
  }
  set_error_handler("error_handler");
  
  /**
  * echo debug message (if MW_DEBUG is true)
  * msg: message to show
  */
  function debug($msg) {
    if (MW_DEBUG) {
      echo '<div class="debug">'.htmlspecialchars('DEBUG: '.$msg, ENT_NOQUOTES),"</div>\n";
    }
  }
  
  ini_set('include_path', ini_get('include_path').':../lib');

  if ($install_pass === null) {
    die("Password is not set - aborting.");
  }

  include('../userdefs.php');
  $mw_enabled_MW_CoreObsoleteExtension = false;
  include('miniwiki.php');

  $real_user = (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : NULL);
  $real_pass = (isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : NULL);
  if (($install_user != $real_user) || ($install_pass != $real_pass)) {
    header('WWW-Authenticate: Basic realm="'.config('auth_realm').' (install/upgrade)"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
  }

  echo('<h1>Install/upgrade</h1>');
  echo('<p>'.MW_NAME.' version: '.MW_VERSION.'</p>');

  class MW_WebInstallHandler extends MW_InstallHandler {
    function show_install_message($msg) {
      echo(htmlspecialchars($msg, ENT_NOQUOTES).'<br>');
    }
  }
    
  register_install_handler(new MW_WebInstallHandler());
  
  class MW_WebExportingHandler extends MW_ExportingHandler {
    function show_exporting_message($msg) {
      echo(htmlspecialchars($msg, ENT_NOQUOTES).'<br>');
    }
  }
    
  register_exporting_handler(new MW_WebExportingHandler());
  
  miniwiki_boot(true);

  $old_main_page = new_page("MainPage", MW_REVISION_HEAD);
  if ($old_main_page->exists()) {
    $main_page = new_page(MW_PAGE_NAME_MAIN, MW_REVISION_HEAD);
    if (!$main_page->exists()) {
      show_install_message('Renaming old main page '.$old_main_page->name.' to '.$main_page->name);
      $old_main_page->rename($main_page->name);
    }
  }

  $storage =& get_storage();
  $old_user_pages = $storage->get_resource_names(MW_DS_PAGES);
  foreach ($old_user_pages as $name) {
    if (strpos($name, 'User:') === 0) {
      $old_page = new_page($name, MW_REVISION_HEAD);
      $new_name = str_replace("User:", "User/", $name);
      show_install_message('Renaming user page '.$old_page->name.' to '.$new_name);
      $old_page->rename($new_name);
    }
  }

  function import_with_check($file) {
    show_install_message('Importing data from '.$file);
    $status = import($file);
    if ($status === null) {
      trigger_error("Unable to import $file - is required extension missing?", E_USER_ERROR);
    } else if ($status !== true) {
      trigger_error("Error occurred while importing $file: ", $status, E_USER_ERROR);
    }
  }
  
  import_with_check('data/users.xml');
  import_with_check('data/pages.xml');
  import_with_check('data/layout.xml');
  import_with_check('data/special.xml');
  
  echo('<p><b>Success</b></p>');
?>
