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
  $req =& get_request();
  $page = new_page($req->page_name, $req->revision);
  set_current_page($page);
  $auth =& get_auth();
  if ($auth->is_invalid()) {
    add_info_text(_('Invalid login.'));
  }

  $action =& get_action($req->action);
  while ($action !== null) {
    if (!$action->is_valid()) {
      trigger_error(_("Unknown action."), E_USER_ERROR);
      break;
    } elseif (!$auth->is_action_permitted($action, $page)) {
      add_info_text(_('Insufficient user rights. Access denied to action: %0%', _($action->get_name())));
      include('header.php');
      include('footer.php');
      break;
    }
    $action =& $action->handle();
  }

?>
