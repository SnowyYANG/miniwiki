<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for authorization and authentication
  */

  require_once('registry.php');
  require_once('settings.php');

  /** admin user name */
  define("MW_USER_NAME_ADMIN", "admin");

  define("MW_COMPONENT_ROLE_AUTH", "MW_Auth");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_AUTH);
  $registry->register(new MW_Auth(), MW_COMPONENT_ROLE_AUTH);
  define("MW_COMPONENT_ROLE_USERS_MANAGER", "MW_UsersManager");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_USERS_MANAGER);
  
  set_default_config('auth_realm', 'miniWiki');
  set_default_config('auth_read_logged_only', false);
  set_default_config('auth_write_admin_only', false);
  
  /**
  * returns instance of MW_Auth
  */
  function &get_auth() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_AUTH);
  }

  $users_manager_class_name = null;
  
  function register_users_manager(&$users_manager) {
    global $registry;
    $registry->register($users_manager, MW_COMPONENT_ROLE_USERS_MANAGER);
  }
  
  function &get_users_manager() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_USERS_MANAGER);
  }
  
  /**
  * HTTP Auth class
  */
  class MW_Auth {
    # [read-only] attributes
    /** were credentials specified by user? */
    var $has_credentials;
    /** current user name */
    var $user;
    /** is user logged in? */
    var $is_logged;

    /** @protected constructor (do not use directly, use get_auth()) */
    function MW_Auth() {
      # void
    }

    var $initialized = false;

    /** @private */
    function init() {
      if (!$this->initialized) {
        $this->has_credentials = isset($_SERVER['PHP_AUTH_USER']);
        $this->user = (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : NULL);
        $pass = (isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : NULL);
        if ($this->has_credentials) {
          $this->validate($pass);
        } else {
          $this->is_logged = false;
        }
        $this->initialized = true;
      }
    }
    
    /** @private
    * check if given password is valid for current user
    * @param pass password
    * @returns true if password is valid
    */
    function validate($pass) {
      $user_page = new_user_page($this->user);
      $this->is_logged = $user_page->is_password_valid($pass);
    }
    
    /** returns true if user specified credentials, but those were not valid */
    function is_invalid() {
      $this->init();
      return ($this->has_credentials && !$this->is_logged);
    }
    
    /**
    * returns true if current user has permission to execute given action on given page
    * everyone can relogin, login, view, view source and show history
    * logged user can edit, delete and update
    * only admin or same user can change password
    * only admin can create or delete user
    * only admin can edit pages from internal miniWiki namespace (MW:)
    * @param action action name
    * @param page MW_Page
    */
    function is_action_permitted($action, $page) {
      $this->init();
      $is_logged = $this->is_logged;
      $is_admin = $this->is_logged && ($this->user == MW_USER_NAME_ADMIN);
      $is_related = isset($page->related_user) && $this->is_logged && ($this->user == $page->related_user);
      switch ($action->get_name()) {
        case MW_ACTION_RELOGIN:
        case MW_ACTION_LOGIN:
          return true;
        case MW_ACTION_VIEW:
        case MW_ACTION_VIEW_SOURCE:
        case MW_ACTION_HISTORY:
          return (config('auth_read_logged_only') ? $is_logged : true);
        case MW_ACTION_EDIT:
        case MW_ACTION_DELETE:
        case MW_ACTION_UPDATE:
        case MW_ACTION_UPLOAD:
        case MW_ACTION_RENAME:
          if (strpos($page->name, MW_PAGE_NAME_PREFIX_MINIWIKI) === 0) {
            return $is_admin;
          }
          if (strpos($page->name, MW_PAGE_NAME_PREFIX_UPLOAD_MINIWIKI) === 0) {
            return $is_admin;
          }
          return (config('auth_write_admin_only') ? $is_admin : $is_logged);
        case MW_ACTION_CHANGE_PASSWORD:
          return $is_related || $is_admin;
        case MW_ACTION_CREATE_USER:
        case MW_ACTION_DELETE_USER:
          return $is_admin;
        default:
          return false;
      }
    }
    
  }

  class MW_UsersManager {
    function get_all_usernames() {
      die("abstract: get_all_usernames");
    }

    function create_user($user) {
      die("abstract: create_user");
    }

    function delete_user($user) {
      die("abstract: delete_user");
    }

    function change_password($user, $pass) {
      die("abstract: change_password");
    }
    
    function is_password_valid($user, $pass) {
      die("abstract: is_password_valid");
    }
  }

  /** login action (will show login dialog if current credentials are invalid) */
  define("MW_ACTION_LOGIN", "login");
  /** relogin action (will show login dialog even if current credentials are valid - needs correct old_user) */
  define("MW_ACTION_RELOGIN", "relogin");
  
  class MW_LoginAction extends MW_Action {
  
    /** @private */
    var $name;

    function MW_LoginAction($name) {
      $this->name = $name;
    }
    
    function get_name() {
      return $this->name;
    }
  
    function &handle() {
      $auth =& get_auth();
      $req =& get_request("MW_AuthRequest");
      # bit hackish
      if ($auth->is_invalid() || ((($this->get_name() == MW_ACTION_RELOGIN) && ($req->get_old_user() == $auth->user)) || !$auth->has_credentials)) {
        header('WWW-Authenticate: Basic realm="'.config('auth_realm').'"');
        header('HTTP/1.0 401 Unauthorized');
        $auth->is_logged = false;
      } else {
        add_info_text(_('Logged as %0%', $auth->user));
      }
      return get_default_action();
    }

    function is_valid() {
      # always
      return true;
    }

    function link() {
      $link = parent::link();
      if ($this->get_name() == MW_ACTION_RELOGIN) {
        $auth =& get_auth();
        $link->set_old_user($auth->user);
      }
      return $link;
    }

    /** @protected */
    function _link() {
      return new MW_LoginLink();
    }
    
  }

  register_action(new MW_LoginAction(MW_ACTION_LOGIN));
  register_action(new MW_LoginAction(MW_ACTION_RELOGIN));

  /** change password action (really changes password) */
  define("MW_ACTION_CHANGE_PASSWORD", "change_password");
  /** create user action (really creates user with disabled login) */
  define("MW_ACTION_CREATE_USER", "create_user");
  /** delete user action (really deletes user, user page is not deleted) */
  define("MW_ACTION_DELETE_USER", "delete_user");

  class MW_ChangePasswordAction extends MW_Action {
  
    function get_name() {
      return MW_ACTION_CHANGE_PASSWORD;
    }
  
    function &handle() {
      $req =& get_request("MW_AuthRequest");
      $user_page = new_user_page($req->get_user());
      $user_page->change_password($req->get_pass());
      add_info_text(_('Password was changed'));
      return get_default_action();
    }

    function is_valid() {
      # always
      return true;
    }
    
    /** @protected */
    function _link() {
      return new MW_UserLink();
    }
    
  }

  register_action(new MW_ChangePasswordAction());
  
  class MW_CreateUserAction extends MW_Action {
  
    function get_name() {
      return MW_ACTION_CREATE_USER;
    }
  
    function &handle() {
      $req =& get_request("MW_AuthRequest");
      $user_page = new_user_page($req->get_user());
      $user_page->create_user();
      add_info_text(_('User was created.'));
      return get_default_action();
    }

    function is_valid() {
      # always
      return true;
    }
    
    /** @protected */
    function _link() {
      return new MW_UserLink();
    }
    
  }
  
  register_action(new MW_CreateUserAction());
  
  class MW_DeleteUserAction extends MW_Action {
  
    function get_name() {
      return MW_ACTION_DELETE_USER;
    }
  
    function &handle() {
      $req =& get_request("MW_AuthRequest");
      $user_page = new_user_page($req->get_user());
      $user_page->delete_user();
      add_info_text(_('User was deleted.'));
      return get_default_action();
    }

    function is_valid() {
      # always
      return true;
    }
    
    /** @protected */
    function _link() {
      return new MW_UserLink();
    }
    
  }
  
  register_action(new MW_DeleteUserAction());
  
  /** old user request variable (for relogin action) */
  define("MW_REQVAR_OLD_USER", "old_user");
  /** user request variable (for create user, delete user and change password actions) */
  define("MW_REQVAR_USER", "user");
  /** password request variable (for change password action) */
  define("MW_REQVAR_PASS", "pass");
  
  class MW_AuthRequest extends MW_Request {
    /** @private */
    var $user;
    /** @private */
    var $old_user;
    /** @private */
    var $pass;

    function MW_AuthRequest($http_request) {
      $this->user = $http_request->get_param(MW_REQVAR_USER);
      $this->old_user = $http_request->get_param(MW_REQVAR_OLD_USER);
      $this->pass = $http_request->get_param(MW_REQVAR_PASS);
    }
  
    function get_user() {
      return $this->user;
    }
    
    function get_old_user() {
      return $this->old_user;
    }
    
    function get_pass() {
      return $this->pass;
    }
    
  }
  
  class MW_LoginLink extends MW_PageLink {

    function get_old_user_param_name() {
      return MW_REQVAR_OLD_USER;
    }

    function set_old_user($old_user) {
      $this->set_param(MW_REQVAR_OLD_USER, $old_user);
    }

  }

  class MW_UserLink extends MW_PageLink {

    function get_user_param_name() {
      return MW_REQVAR_USER;
    }

    function set_user($user) {
      $this->set_param(MW_REQVAR_USER, $user);
    }

    function get_pass_param_name() {
      return MW_REQVAR_PASS;
    }

    function set_pass($pass) {
      $this->set_param(MW_REQVAR_PASS, $pass);
    }

  }

?>
