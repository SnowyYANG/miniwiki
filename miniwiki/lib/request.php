<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for HTTP requests
  */

  require_once('registry.php');
  
  /** default action (if none requested) */
  define("MW_DEFAULT_ACTION", MW_ACTION_VIEW);
  /** page name request variable */
  define("MW_REQVAR_PAGE_NAME", "page_name");
  /** action request variable */
  define("MW_REQVAR_ACTION", "action");
  /** page revision request variable */
  define("MW_REQVAR_REVISION", "revision");
  /** page content request variable (for update action) */
  define("MW_REQVAR_CONTENT", "content");
  /** update message (for update action) */
  define("MW_REQVAR_MESSAGE", "message");
  /** preview submit (for update action) */
  define("MW_REQVAR_PREVIEW", "preview");
  /** old user request variable (for relogin action) */
  define("MW_REQVAR_OLD_USER", "old_user");
  /** user request variable (for create user, delete user and change password actions) */
  define("MW_REQVAR_USER", "user");
  /** password request variable (for change password action) */
  define("MW_REQVAR_PASS", "pass");
  /** source file (for upload action) */
  define("MW_REQVAR_SOURCEFILE", "sourcefile");
  /** destination file (for upload action) */
  define("MW_REQVAR_DESTFILE", "destfile");
  
  define("MW_COMPONENT_ROLE_REQUEST", "MW_Request");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_REQUEST);
  $registry->register(new MW_Request(), MW_COMPONENT_ROLE_REQUEST);
  
  /**
  * returns instance of MW_Request
  */
  function &get_request() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_REQUEST);
  }

  /**
  * HTTP request class
  */
  class MW_Request {
    # [read-only] attributes
    /** MW_REQVAR_PAGE_NAME */
    var $page_name;
    /** MW_REQVAR_ACTION */
    var $action;
    /** MW_REQVAR_REVISION */
    var $revision;
    /** MW_REQVAR_CONTENT */
    var $content;
    /** MW_REQVAR_MESSAGE */
    var $message;
    /** MW_REQVAR_PREVIEW */
    var $preview;
    /** MW_REQVAR_OLD_USER */
    var $old_user;
    /** MW_REQVAR_USER */
    var $user;
    /** MW_REQVAR_PASS */
    var $pass;
    /** MW_REQVAR_SOURCEFILE (as associative array with keys name, type, size and tmp_name) */
    var $sourcefile;
    /** MW_REQVAR_DESTFILE */
    var $destfile;
    /** whether this is head request */
    var $is_head;

    /** @protected constructor (do not use directly, use get_request()) */
    function MW_Request() {
      $req_array = $_REQUEST;
      if (get_magic_quotes_gpc()) {
        $req_array = array_map("stripslashes", $req_array);
      }
      $path_info = '';
      if (isset($_SERVER['FILEPATH_INFO'])) {
        $path_info = $_SERVER['FILEPATH_INFO'];
      } elseif (isset($_SERVER['PATH_INFO'])) {
        $path_info = $_SERVER['PATH_INFO'];
      }
      $this->page_name = MW_DEFAULT_PAGE_NAME;
      if (strlen(trim($path_info)) > 0) {
        $path_info = preg_replace('/^\/+/', '', $path_info);
        $this->page_name = $path_info;
      } elseif (isset($req_array[MW_REQVAR_PAGE_NAME])) {
        $this->page_name = $req_array[MW_REQVAR_PAGE_NAME];
      }
      $this->page_name = filter_page_name(decode_page_name($this->page_name));
      $this->action = (isset($req_array[MW_REQVAR_ACTION]) ? $req_array[MW_REQVAR_ACTION] : MW_DEFAULT_ACTION);
      $this->revision = (isset($req_array[MW_REQVAR_REVISION]) ? $req_array[MW_REQVAR_REVISION] : MW_REVISION_HEAD);
      $this->content = (isset($req_array[MW_REQVAR_CONTENT]) ? $req_array[MW_REQVAR_CONTENT] : NULL);
      $this->message = (isset($req_array[MW_REQVAR_MESSAGE]) ? $req_array[MW_REQVAR_MESSAGE] : NULL);
      $this->preview = (isset($req_array[MW_REQVAR_PREVIEW]) ? $req_array[MW_REQVAR_PREVIEW] : NULL);
      $this->old_user = (isset($req_array[MW_REQVAR_OLD_USER]) ? $req_array[MW_REQVAR_OLD_USER] : NULL);
      $this->user = (isset($req_array[MW_REQVAR_USER]) ? $req_array[MW_REQVAR_USER] : NULL);
      $this->pass = (isset($req_array[MW_REQVAR_PASS]) ? $req_array[MW_REQVAR_PASS] : NULL);
      $this->sourcefile = (isset($_FILES[MW_REQVAR_SOURCEFILE]) ? $_FILES[MW_REQVAR_SOURCEFILE] : NULL);
      $this->destfile = (isset($req_array[MW_REQVAR_DESTFILE]) ? $req_array[MW_REQVAR_DESTFILE] : NULL);
      $this->is_head = ($_SERVER["REQUEST_METHOD"] == "HEAD");
    }
  }

?>
