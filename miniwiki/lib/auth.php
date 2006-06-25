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
    * returns true if current user has permission to execute action specified by request on given page
    * see is_action_permitted() for more information
    * @param req MW_Request
    * @param page MW_Page
    */
    function is_permitted($req, $page) {
      $this->init();
      return $this->is_action_permitted($req->action, $page);
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
      switch ($action) {
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

?>
