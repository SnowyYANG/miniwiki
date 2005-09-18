<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # main entrance page

  ini_set('include_path', ini_get('include_path').':.');

  include('settings.php');
  include('debug.php');
  include('error.php');
  include('miniwiki.php');
  include('functions.php');
  $db = new_database($mw_db_host, $mw_db_user, $mw_db_pass, $mw_db_name);
  $renderer = new_renderer($db);
  $req = new_request();
  $auth = new_auth();
  $page = new_page($db, $req->page_name, $req->revision);
  if ($auth->is_invalid()) {
    add_info_text($mw_texts[MWT_LOGIN_INVALID]);
  }

  # handle given action
  # action: action to handle
  # returns new action to handle or null
  function handle_action($action) {
    global $db, $renderer, $req, $auth, $page, $mw_texts, $mw_db_host, $mw_db_user, $mw_db_pass,
           $mw_db_name, $mw_auth_realm, $mw_encoding, $mw_db_encoding, $mw_db_use_server_collation;

    if (!$auth->is_action_permitted($action, $page)) {
      $action_text = (isset($mw_texts[$action]) ? $mw_texts[$action] : $mw_texts[MWT_UNKNOWN_ACTION]);
      add_info_text($mw_texts[MWT_ACCESS_DENIED].$action_text);
      include('header.php');
      include('footer.php');
    } elseif (!$page->has_action($action)) {
      trigger_error($mw_texts[MWT_UNKNOWN_ACTION], E_USER_ERROR);
    } else switch ($action) {
      case MW_ACTION_RELOGIN:
      case MW_ACTION_LOGIN:
        # bit hackish
        if ($auth->is_invalid() || ((($action == MW_ACTION_RELOGIN) && ($req->old_user == $auth->user)) || !$auth->has_credentials)) {
          header('WWW-Authenticate: Basic realm="'.$mw_auth_realm.'"');
          header('HTTP/1.0 401 Unauthorized');
          $auth->is_logged = false;
        } else {
          add_info_text($mw_texts[MWT_LOGGED_AS].' '.$auth->user);
        }
        return MW_ACTION_VIEW;
      case MW_ACTION_CREATE_USER:
        $user_page = new_user_page($db, $req->user);
        $user_page->create_user();
        add_info_text($mw_texts[MWT_USER_CREATED]);
        return MW_ACTION_VIEW;
      case MW_ACTION_DELETE_USER:
        $user_page = new_user_page($db, $req->user);
        $user_page->delete_user();
        add_info_text($mw_texts[MWT_USER_DELETED]);
        return MW_ACTION_VIEW;
      case MW_ACTION_CHANGE_PASSWORD:
        $user_page = new_user_page($db, $req->user);
        $user_page->change_password($req->pass);
        add_info_text($mw_texts[MWT_PASSWORD_CHANGED]);
        return MW_ACTION_VIEW;
      case MW_ACTION_VIEW_SOURCE:
      case MW_ACTION_VIEW:
        if ($page->load()) {
          include(($action == MW_ACTION_VIEW_SOURCE) ? 'viewsource.php' : 'viewpage.php');
          break;
        } else if (is_a($page, 'MW_Special_Upload_Page') && $page->is_data_page) {
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
        if (is_a($page, 'MW_Special_Upload_Page')) {
          include('editupload.php');
        } else {
          include('editpage.php');
        }
        break;
      case MW_ACTION_DELETE:
        $page->delete();
        add_info_text($mw_texts[MWT_PAGE_DELETED]);
        return MW_ACTION_VIEW;
      case MW_ACTION_HISTORY:
        include('history.php');
        break;
      case MW_ACTION_UPDATE:
        if ($req->preview) {
          $page->update_for_preview($req->content);
          return MW_ACTION_EDIT;
        } else {
          $page->update($req->content, $req->message);
          add_info_text($mw_texts[MWT_PAGE_UPDATED]);
          return MW_ACTION_VIEW;
        }
      case MW_ACTION_UPLOAD:
        if (!is_uploaded_file($req->sourcefile['tmp_name'])) {
          trigger_error('Possible upload attack with file '.$req->sourcefile['name'], E_USER_ERROR);
          break;
        }
        if (is_a($page, 'MW_Special_Uploads_Page')) {
          $filename = $req->destfile ? $req->destfile : $req->sourcefile['name'];
          $page = $page->upload(file_get_contents($req->sourcefile['tmp_name']), $req->message, $filename);
        } else {
          $page->update(file_get_contents($req->sourcefile['tmp_name']), $req->message);
        }
        add_info_text($mw_texts[MWT_FILE_UPLOADED]);
        unlink($req->sourcefile['tmp_name']);
        return MW_ACTION_VIEW;
      default:
        trigger_error($mw_texts[MWT_UNKNOWN_ACTION], E_USER_ERROR);
        break;
    }
    return null;
  }
  
  $action = $req->action;
  while ($action != null) {
    $action = handle_action($action);
  }
  
  $db->destroy();
?>
