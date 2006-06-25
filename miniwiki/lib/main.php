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

  /**
  * handle given action
  * action: action to handle
  * returns new action to handle or null
  */
  function handle_action($action) {
    $auth =& get_auth();
    $page =& get_current_page();
    $req =& get_request();

    if (!$auth->is_action_permitted($action, $page)) {
      $action_text = (is_valid_action($action) ? _($action) : _("Unknown action."));
      add_info_text(_('Insufficient user rights. Access denied to action: %0%', $action_text));
      include('header.php');
      include('footer.php');
    } elseif (!$page->has_action($action)) {
      trigger_error(_("Unknown action."), E_USER_ERROR);
    } else switch ($action) {
      case MW_ACTION_RELOGIN:
      case MW_ACTION_LOGIN:
        # bit hackish
        if ($auth->is_invalid() || ((($action == MW_ACTION_RELOGIN) && ($req->old_user == $auth->user)) || !$auth->has_credentials)) {
          header('WWW-Authenticate: Basic realm="'.config('auth_realm').'"');
          header('HTTP/1.0 401 Unauthorized');
          $auth->is_logged = false;
        } else {
          add_info_text(_('Logged as %0%', $auth->user));
        }
        return MW_ACTION_VIEW;
      case MW_ACTION_CREATE_USER:
        $user_page = new_user_page($req->user);
        $user_page->create_user();
        add_info_text(_('User was created.'));
        return MW_ACTION_VIEW;
      case MW_ACTION_DELETE_USER:
        $user_page = new_user_page($req->user);
        $user_page->delete_user();
        add_info_text(_('User was deleted.'));
        return MW_ACTION_VIEW;
      case MW_ACTION_CHANGE_PASSWORD:
        $user_page = new_user_page($req->user);
        $user_page->change_password($req->pass);
        add_info_text(_('Password was changed'));
        return MW_ACTION_VIEW;
      case MW_ACTION_VIEW_SOURCE:
      case MW_ACTION_VIEW:
        if ($page->load()) {
          include(($action == MW_ACTION_VIEW_SOURCE) ? 'viewsource.php' : 'viewpage.php');
          break;
        } else if (is_a($page, 'MW_SpecialUploadPage') && $page->is_data_page) {
          # missing data page should raise 404 Not Found
          header ("HTTP/1.0 404 Not Found");
        }
        # fallback to edit if page does not exist
        return MW_ACTION_EDIT;
      case MW_ACTION_EDIT:
        # prevent double-load or preview overwriting
        if (!$page->has_content) {
          $page->load();
        }
        if (is_a($page, 'MW_SpecialUploadPage')) {
          include('editupload.php');
        } else {
          include('editpage.php');
        }
        break;
      case MW_ACTION_DELETE:
        $page->delete();
        add_info_text(_("Page deleted."));
        return MW_ACTION_VIEW;
      case MW_ACTION_HISTORY:
        include('history.php');
        break;
      case MW_ACTION_UPDATE:
        if ($req->preview) {
          $page->update_for_preview($req->content);
          return MW_ACTION_EDIT;
        } else {
          $changed = $page->update($req->content, $req->message);
          add_info_text($changed ? _("Page updated.") : _("No edits. Page was not updated."));
          return MW_ACTION_VIEW;
        }
      case MW_ACTION_UPLOAD:
        if (!is_uploaded_file($req->sourcefile['tmp_name'])) {
          trigger_error('Possible upload attack with file '.$req->sourcefile['name'], E_USER_ERROR);
          break;
        }
        if (is_a($page, 'MW_SpecialUploadsPage')) {
          $filename = $req->destfile ? $req->destfile : $req->sourcefile['name'];
          $page = $page->upload(file_get_contents($req->sourcefile['tmp_name']), $req->message, $filename);
        } else {
          $page->update(file_get_contents($req->sourcefile['tmp_name']), $req->message);
        }
        add_info_text(_("File uploaded."));
        unlink($req->sourcefile['tmp_name']);
        return MW_ACTION_VIEW;
      default:
        trigger_error(_("Unknown action."), E_USER_ERROR);
        break;
    }
    return null;
  }
  
  $action = $req->action;
  while ($action != null) {
    $action = handle_action($action);
  }
  
?>
