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

  $install_mode = true;
  
  include('../userdefs.php');

  $real_user = (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : NULL);
  $real_pass = (isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : NULL);
  if (($install_user != $real_user) || ($install_pass != $real_pass)) {
    header('WWW-Authenticate: Basic realm="'.$mw_auth_realm.' (install/upgrade)"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
  }

  echo('<h1>Install/upgrade</h1>');
    
  include('settings.php');
  include('miniwiki.php');

  echo('<p>miniWiki version: '.MW_VERSION.'</p>');

  class MW_WebInstallHandler extends MW_InstallHandler {
    function show_install_message($msg) {
      echo(htmlspecialchars($msg, ENT_NOQUOTES).'<br>');
    }
  }
    
  $install_handler = new MW_WebInstallHandler();
  
  miniwiki_boot();
  # forces intialization of delayed dataspace definitions which is what we need
  new_storage();

  echo('<p><b>Success</b></p>');
?>
