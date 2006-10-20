<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * main entrance page
  */

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
  
  ini_set('include_path', ini_get('include_path').':.');

  include('miniwiki.php');
  miniwiki_boot();
  $req =& get_request("MW_PageRequest");
  $page = $req->get_page();
  set_current_page($page);
  $auth =& get_auth();
  if ($auth->is_invalid()) {
    add_info_text(_t('Invalid login.'));
  }

  $req =& get_request("MW_ActionRequest");
  $action = $req->get_action();
  if ($action === null) {
    trigger_error(_t("Unknown action."), E_USER_ERROR);
  }
  while ($action !== null) {
    if (!$action->is_valid()) {
      trigger_error(_t("Unknown action."), E_USER_ERROR);
      break;
    } elseif (!$action->is_permitted()) {
      add_info_text(_t('Insufficient user rights. Access denied to action: %0%', _t($action->get_name())));
      render_ui(MW_LAYOUT_HEADER);
      render_ui(MW_LAYOUT_FOOTER);
      break;
    }
    $action = $action->handle();
  }

?>
