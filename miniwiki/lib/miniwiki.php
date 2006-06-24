<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * miniWiki library
  * @param auth current MW_Auth
  * @param db curent MW_Database
  * @param mw_db_encoding database encoding
  * @param mw_db_use_server_collation whether to let database sort strings according to its settings or
  *                             according to wanted mw_db_encoding
  * @param mw_texts texts array
  * @param renderer current MW_Renderer
  */

  /** miniWiki product name */
  define("MW_NAME", "miniWiki");
  /** miniWiki version as X.Y string */
  define("MW_VERSION", "0.3-svn");

  if (!function_exists('stripos')) {
    function stripos($haystack,$needle,$offset = 0) {
      return(strpos(strtolower($haystack),strtolower($needle),$offset));
    }
  }

  /** main page name */
  define("MW_PAGE_NAME_MAIN", "Main Page");
  /** default page name (if none requested) */
  define("MW_DEFAULT_PAGE_NAME", MW_PAGE_NAME_MAIN);
  /** view page action (renders Wiki page) */
  define("MW_ACTION_VIEW", "view");
  /** view page source action (shows Wiki markup) */
  define("MW_ACTION_VIEW_SOURCE", "view_source");
  /** edit page action (shows Wiki editor) */
  define("MW_ACTION_EDIT", "edit");
  /** delete page action (really deletes page) */
  define("MW_ACTION_DELETE", "delete");
  /** show history action (shows history page) */
  define("MW_ACTION_HISTORY", "history");
  /** update page action (really changes Wiki page or shows a preview) */
  define("MW_ACTION_UPDATE", "update");
  /** login action (will show login dialog if current credentials are invalid) */
  define("MW_ACTION_LOGIN", "login");
  /** relogin action (will show login dialog even if current credentials are valid - needs correct old_user) */
  define("MW_ACTION_RELOGIN", "relogin");
  /** change password action (really changes password) */
  define("MW_ACTION_CHANGE_PASSWORD", "change_password");
  /** create user action (really creates user with disabled login) */
  define("MW_ACTION_CREATE_USER", "create_user");
  /** delete user action (really deletes user, user page is not deleted) */
  define("MW_ACTION_DELETE_USER", "delete_user");
  /** upload action */
  define("MW_ACTION_UPLOAD", "upload");
  /** default action (if none requested) */
  define("MW_DEFAULT_ACTION", MW_ACTION_VIEW);
  /** page name request variable */
  define("MW_REQVAR_PAGE_NAME", "page_name");
  /** action request variable */
  define("MW_REQVAR_ACTION", "action");
  /** page revision request variable */
  define("MW_REQVAR_REVISION", "revision");
  /** HEAD pseudo-revision name (latest revision will be used when talking to database) */
  define("MW_REVISION_HEAD", "HEAD");
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
  /** users list special page name */
  define("MW_PAGE_NAME_USERS", "Special:Users");
  /** pages list special page name */
  define("MW_PAGE_NAME_PAGES", "Special:Pages");
  /** user page name prefix */
  define("MW_PAGE_NAME_PREFIX_USER", "User:");
  /** admin user name */
  define("MW_USER_NAME_ADMIN", "admin");
  /** uploads list special page name */
  define("MW_PAGE_NAME_UPLOADS", "Special:Uploads");
  /** upload page prefix */
  define("MW_PAGE_NAME_PREFIX_UPLOAD", "Upload:");
  /** data page prefix (raw uploaded file) */
  define("MW_PAGE_NAME_PREFIX_DATA", "data/");
  /** data page prefix (raw uploaded file) from miniWiki 0.2 */
  define("MW_PAGE_NAME_PREFIX_DATA_0_2", "Data:");
  /** default MIME type for uploaded files */
  define("MW_DEFAULT_MIME_TYPE", "application/octet-stream");
  /** image link page prefix (will render image directly) */
  define("MW_LINK_NAME_PREFIX_IMAGE", "Image:");
  /** default stylesheet upload name */
  define("MW_DEFAULT_STYLESHEET_NAME", "default.css");
  /** default javascript functions upload name */
  define("MW_DEFAULT_JAVASCRIPT_FUNCTIONS_NAME", "functions.js");
  /** internal miniWiki namespace prefix */
  define("MW_PAGE_NAME_PREFIX_MINIWIKI", "MW:");
  /** internal miniWiki namespace prefix for uploads */
  define("MW_PAGE_NAME_PREFIX_UPLOAD_MINIWIKI", MW_PAGE_NAME_PREFIX_UPLOAD . "mw/");
  /** layout page prefix */
  define("MW_PAGE_NAME_PREFIX_LAYOUT", MW_PAGE_NAME_PREFIX_MINIWIKI . "Layout:");
  /** footer layout page name */
  define("MW_PAGE_NAME_LAYOUT_FOOTER", MW_PAGE_NAME_PREFIX_LAYOUT . "Footer");
  /** header layout page name */
  define("MW_PAGE_NAME_LAYOUT_HEADER", MW_PAGE_NAME_PREFIX_LAYOUT . "Header");
  define("MW_PAGE_TAG_USER", "user");
  define("MW_PAGE_TAG_UPLOAD", "upload");
  define("MW_RESOURCE_KEY_NAME", "name");
  define("MW_RESOURCE_KEY_CONTENT", "content");
  define("MW_RESOURCE_KEY_CONTENT_LENGTH", "length");
  define("MW_RESOURCE_KEY_LAST_MODIFIED", "last_modified");
  define("MW_RESOURCE_KEY_MESSAGE", "message");
  define("MW_RESOURCE_KEY_AUTHOR", "author");
  define("MW_RESOURCE_KEY_REVISION", "revision");
  define("MW_RESOURCE_CONTENT_TYPE_NONE", "none");
  define("MW_RESOURCE_CONTENT_TYPE_TEXT", "text");
  define("MW_RESOURCE_CONTENT_TYPE_BINARY", "binary");
  define("MW_RESOURCE_CUSTOM_KEY_TYPE_TEXT", "text:");

  # assign user-visible texts to action names
  $mw_texts[MW_ACTION_VIEW] = $mw_texts[MWT_ACTION_VIEW];
  $mw_texts[MW_ACTION_VIEW_SOURCE] = $mw_texts[MWT_ACTION_VIEW_SOURCE];
  $mw_texts[MW_ACTION_EDIT] = $mw_texts[MWT_ACTION_EDIT];
  $mw_texts[MW_ACTION_DELETE] = $mw_texts[MWT_ACTION_DELETE];
  $mw_texts[MW_ACTION_HISTORY] = $mw_texts[MWT_ACTION_HISTORY];
  $mw_texts[MW_ACTION_UPDATE] = $mw_texts[MWT_ACTION_UPDATE];
  $mw_texts[MW_ACTION_LOGIN] = $mw_texts[MWT_ACTION_LOGIN];
  $mw_texts[MW_ACTION_RELOGIN] = $mw_texts[MWT_ACTION_RELOGIN];
  $mw_texts[MW_ACTION_CHANGE_PASSWORD] = $mw_texts[MWT_ACTION_CHANGE_PASSWORD];
  $mw_texts[MW_ACTION_CREATE_USER] = $mw_texts[MWT_ACTION_CREATE_USER];
  $mw_texts[MW_ACTION_DELETE_USER] = $mw_texts[MWT_ACTION_DELETE_USER];
  $mw_texts[MW_ACTION_UPLOAD] = $mw_texts[MWT_ACTION_UPLOAD];

  # info text functions
  /** info text array (do not use directly, use add_info_text() and get_info_text()) */
  $info_text = array();
  /**
  * add new information to be shown to the user
  * @param text text to show
  */
  function add_info_text($text) {
    global $info_text;
    array_push ($info_text, $text);
  }
  /**
  * returns array with all information texts
  */
  function get_info_text() {
    global $info_text;
    return $info_text;
  }

  /** wiki functions handling */
  $wiki_functions = array();
  /**
  * register wiki function
  * @param name wiki function name
  * @param cb function callback
  */
  function register_wiki_function($name, $cb) {
    global $wiki_functions;
    $wiki_functions[$name] = $cb;
  }
  /**
  * call wiki function
  * @param name wiki function name
  * @param args wiki function argument
  * @param renderer_state MW_RendererState
  */
  function call_wiki_function($name, $args, $renderer_state) {
    global $wiki_functions;
    if (isset ($wiki_functions[$name])) {
      return call_user_func($wiki_functions[$name], $args, $renderer_state);
    }
    return null;
  }

  /**
  * returns new MW_Variables with prefilled global values
  */
  function new_global_wiki_variables() {
    global $auth, $req;
    $vars = new MW_Variables(null);
    $vars->set('version', MW_VERSION);
    $vars->set('user', ($auth->is_logged ? $auth->user : ''));
    $vars->set('main_page', MW_PAGE_NAME_MAIN);
    $vars->set('req_action', $req->action);
    $vars->set('self_link_dir', $_SERVER['SCRIPT_NAME'].'/../');
    return $vars;
  }

  /**
  * returns new MW_Variables
  * @param supervars super MW_Variables to use
  */
  function new_wiki_variables($supervars) {
    return new MW_Variables($supervars);
  }

  /**
  * returns instance of MW_Request
  */
  function new_request() {
    return new MW_Request();
  }

  /**
  * returns instance of MW_Auth
  */
  function new_auth() {
    return new MW_Auth();
  }

  /**
  * returns filtered page name
  * _ is replaced with space
  * " # $ * + < > = @ [ ] \ ^ ` { } | ~ are removed
  * @param name page name
  */
  function filter_page_name($name) {
    $name = str_replace('_', ' ', $name);
    return str_replace(array('"', '#' ,"$" ,'*', '+' ,'<' ,'>' ,'=' ,'@' ,'[' ,']' ,'\\', '^', '`', '{', '}' ,'|', '~'), '', $name);
  }

  /**
  * returns encoded page name
  * space is replaced with _
  * rawurlencode must still be used if this should be part of URL
  * @param name page name
  */
  function encode_page_name($name) {
    return str_replace(' ', '_', $name);
  }

  /**
  * returns encoded page name
  * + and _ are replaced with space
  * rawurldecode must still be used if this comes from URL
  * @param name page name
  */
  function decode_page_name($name) {
    return str_replace(array('+', '_'), ' ', $name);
  }

  /**
  * returns urlencoded page name
  * does not encode forward slash as %2F
  * see encode_page_name for more
  * @param name page name
  */
  function urlencode_page_name($name) {
    return str_replace(array('%2F', '%2f'), '/', rawurlencode(encode_page_name($name)));
  }
  
  class MW_PageHandler {
    var $next;
    function get_priority() {
      return 0;
    }
    function get_page($tag, $name, $revision) {
      die("abstract: get_page");
    }
  }
  
  class MW_LastPageHandler extends MW_PageHandler {
    function get_priority() {
      return 10000;
    }
    function get_page($tag, $name, $revision) {
      return null;
    }
  }
  
  $page_handlers = array(new MW_LastPageHandler());
  
  function register_page_handler($handler) {
    global $page_handlers;
    array_push($page_handlers, $handler);
    /** @todo maybe later */
    initialize_page_handlers();
  }
  
  function page_handler_cmp($a, $b) {
    return ($a->get_priority()) - ($b->get_priority());
  }
  
  function initialize_page_handlers() {
    global $page_handlers;
    usort($page_handlers, "page_handler_cmp");
    $last = null;
    for ($i = 0; $i < sizeof($page_handlers); $i++) {
      $handler =& $page_handlers[$i];
      if ($last !== null) {
        $last->next =& $handler;
      }
      $last =& $handler;
    }
    if ($last !== null) {
      $last->next = null;
    }
  }
  
  function new_page_with_tag($tag, $name, $revision) {
    debug("new_page_with_tag: tag=".$tag. ", name=".$name. ", revision=".$revision);
    $name = filter_page_name($name);
    global $page_handlers;
    if (sizeof($page_handlers) > 0) {
      return $page_handlers[0]->get_page($tag, $name, $revision);
    }
    return null;
  }

  /**
  * returns instance of MW_Page
  * @param name page name
  * @param revision wanted revision
  */
  function new_page($name, $revision) {
    return new_page_with_tag(null, $name, $revision);
  }

  /**
  * returns instance of MW_SpecialUserPage
  * @param user user name (not user page name)
  */
  function new_user_page($user) {
    return new_page_with_tag(MW_PAGE_TAG_USER, $user, MW_REVISION_HEAD);
  }

  /** returns instance of MW_SpecialUploadPage
  * @param name upload name (not upload page name)
  * @param revision wanted revision
  */
  function new_upload_page($name, $revision) {
    return new_page_with_tag(MW_PAGE_TAG_UPLOAD, $name, $revision);
  }

  /**
  * returns instance of MW_Renderer
  */
  function new_renderer() {
    global $renderer_class_name;
    return new $renderer_class_name();
  }

  function new_users_manager() {
    global $users_manager_class_name;
    return new $users_manager_class_name();
  }
  
  function new_storage() {
    global $storage_class_name, $delayed_dataspace_registration;
    $storage = new $storage_class_name();
    foreach ($delayed_dataspace_registration as $dataspace_def) {
      $storage->register_dataspace($dataspace_def);
    }
    return $storage;
  }

  $delayed_dataspace_registration = array();

  function register_dataspace($dataspace_def) {
    global $storage, $delayed_dataspace_registration;
    if (isset($storage)) {
      $storage->register_dataspace($dataspace_def);
    } else {
      array_push($delayed_dataspace_registration, $dataspace_def);
    }
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

    /** @protected constructor (do not use directly, use new_request()) */
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

    /** @protected constructor (do not use directly, use new_auth()) */
    function MW_Auth() {
      $this->has_credentials = isset($_SERVER['PHP_AUTH_USER']);
      $this->user = (isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : NULL);
      $pass = (isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : NULL);
      if ($this->has_credentials) {
        $this->validate($pass);
      } else {
        $this->is_logged = false;
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
      return ($this->has_credentials && !$this->is_logged);
    }
    
    /**
    * returns true if current user has permission to execute action specified by request on given page
    * see is_action_permitted() for more information
    * @param req MW_Request
    * @param page MW_Page
    */
    function is_permitted($req, $page) {
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
      global $auth_read_logged_only, $auth_write_admin_only;
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
          return ($auth_read_logged_only ? $is_logged : true);
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
          return ($auth_write_admin_only ? $is_admin : $is_logged);
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
    
    function destroy() {
    }
  }

  class MW_Resource {
    var $data = array();
    function get($key) {
      return $this->data[$key];
    }
    function set($key, $value) {
      $this->data[$key] = $value;
    }
  }

  class MW_DataSpaceDefinition {
    var $name;
    var $versioned;
    var $content_type;
    var $custom_keys;

    function MW_DataSpaceDefinition($name, $versioned, $content_type) {
      $this->name = $name;
      $this->versioned = $versioned;
      $this->content_type = $content_type;
      $this->custom_keys = array();
    }

    function get_name() {
      return $this->name;
    }

    function is_versioned() {
      return $this->versioned;
    }

    function get_content_type() {
      return $this->content_type;
    }

    function get_custom_keys() {
      return $this->custom_keys;
    }

    function add_custom_key($name, $type) {
      $this->custom_keys[$name] = $type;
    }
  }
  
  class MW_Storage {
    # ordered by name
    function get_resource_names($dataspace) {
      die("abstract: get_resource_names");
    }
    
    function exists($dataspace, $name) {
      die("abstract: exists");
    }
    
    function get_resource($dataspace, $name, $revision, $with_data) {
      die("abstract: get_resource");
    }
    
    function delete_resource($dataspace, $name) {
      die("abstract: delete_resource");
    }
    
    function update_resource($dataspace, $resource) {
      die("abstract: update_resource");
    }
    
    # for versioned resources one can use update_resource() too
    function create_resource($dataspace, $resource) {
      die("abstract: create_resource");
    }
    
    /** ordered by revision from last to first */
    function get_resource_history($dataspace, $name, $with_data) {
      die("abstract: get_resource_history");
    }

    function register_dataspace($dataspace_def) {
      die("abstract: register_dataspace");
    }

    function get_dataspace_names() {
      die("abstract: get_dataspace_names");
    }

    function get_dataspace_definition($dataspace) {
      die("abstract: get_dataspace_definition");
    }

    function destroy() {
    }
  }

  /**
  * returns last modified value as UNIX timestamp (see mktime())
  * @param val last modified value (as loaded from database)
  */
  function last_modified_as_timestamp($val) {
    # detect whether we have MySQL's "INTERNAL" or "ISO" (or similar) timestamp format - default changed in MySQL 4.1.x
    if (strlen($val) == 14) {
      $year = substr($val, 0, 4);
      $month = substr($val, 4, 2);
      $day = substr($val, 6, 2);
      $hour = substr($val, 8, 2);
      $min = substr($val, 10, 2);
      $sec = substr($val, 12, 2);
    } else {
      $year = substr($val, 0, 4);
      $month = substr($val, 5, 2);
      $day = substr($val, 8, 2);
      $hour = substr($val, 11, 2);
      $min = substr($val, 14, 2);
      $sec = substr($val, 17, 2);
    }
    return mktime($hour, $min, $sec, $month, $day, $year);
  }

  /**
  * returns last modified value returned as YEAR/MONTH/DAY HOUR:MIN:SEC
  * @param val last modified value (as loaded from database)
  */
  function format_last_modified($val) {
    $ts = last_modified_as_timestamp($val);
    /** @todo configurable */
    return strftime("%Y/%m/%d %H:%M:%S", $ts);
  }
  
  /**
  * returns current date and time as last modified value
  */
  function now_as_last_modified() {
    return strftime("%Y%m%d%H%M%S");
  }
  
  /**
  * [abstract] Wiki page
  */
  class MW_Page {
    # [read-only] attributes
    /** page name */
    var $name;
    /** page revision */
    var $revision;
    /** is some content loaded? */
    var $has_content;
    /** raw content (may be empty even if has_content is true) - valid after load() */
    var $raw_content;
    /** time of last modification (special format) - valid after load() */
    var $last_modified;
    /** page revision message (if any) - valid after load() */
    var $message;
    /** page revision author (if any) - valid after load() */
    var $user;
    /** page title - valid after load() */
    var $title;
    /** raw content length in bytes - maybe valid before load(), but may be set to null after load() if still not known */
    var $raw_content_length;

    /** constructor */
    function MW_Page($name) {
      $this->name = $name;
      $this->revision = MW_REVISION_HEAD;
      $this->has_content = false;
      $this->raw_content = '';
      $this->last_modified = 0;
      $this->message = '';
      $this->user = '';
      $this->title = '';
      $this->raw_content_length = null;
    }
    
    /**
    * [override, returns false] returns true if this page supports given action
    * @param action action
    */
    function has_action($action) {
      return false;
    }
    
    /** [override, returns false] returns true if this page (with revision) exists */
    function exists() {
      return false;
    }
    
    /**
    * [override, returns false] load page (with revision) content
    * @returns true if content has been loaded successfully
    */
    function load() {
      $this->title = $this->name;
      return false;
    }
    
    /** [override] delete page (including all revisions) */
    function delete() {
    }
    
    /** [override] update and reload page (revision will change)
    * @param content new content
    * @param message change message
    * @returns true if content has been set (it was different from old content)
    */
    function update($content, $message) {
      return false;
    }
    
    /**
    * [override] set content for preview
    * @param content new content
    */
    function update_for_preview($content) {
    }
    
    /** [override] render page (with revision) content (must be loaded first) to output */
    function render() {
    }
    
    /**
    * returns URL for this page and given action
    * @param action action name
    * @param rev revision - defaults to current
    */
    function url_for_action($action, $rev = null) {
      if ($rev === null) {
        $rev = $this->revision;
      }
      $ret = $_SERVER['SCRIPT_NAME'] . '/' . urlencode_page_name($this->name);
      $in_query = false;
      if ($action != MW_DEFAULT_ACTION) {
        $ret .= ($in_query ? '&' : '?') . MW_REQVAR_ACTION . '=' . rawurlencode($action);
        $in_query = true;
      }
      if ($rev != MW_REVISION_HEAD) {
        $ret .= ($in_query ? '&' : '?') . MW_REQVAR_REVISION . '=' . rawurlencode($rev);
        $in_query = true;
      }
      return $ret;
    }
    
    /**
    * [override, returns empty array] returns array of MW_Page instances representing all revisions including current one
    * returned array is ordered by revision in descending order (HEAD first)
    */
    function get_all_revisions() {
      return array();
    }
  }
  
  /** [abstract] special page */
  class MW_SpecialPage extends MW_Page {

    function MW_SpecialPage($name) {
      parent::MW_Page($name);
      $this->has_content = true;
      $this->last_modified = now_as_last_modified();
    }

    function has_action($action) {
      switch ($action) {
        case MW_ACTION_HISTORY:
        case MW_ACTION_EDIT:
        case MW_ACTION_VIEW_SOURCE:
        case MW_ACTION_DELETE:
        case MW_ACTION_UPDATE:
        case MW_ACTION_UPLOAD;
          return false;
        default:
          return true;
      }
    }
    
    function exists() {
      return true;
    }
    
    function load() {
      $this->title = $this->name;
      return true;
    }
    
  }

  /** wiki variables */
  class MW_Variables {
    # [read-only] attributes
    /** variables array */
    var $variables;
    /** super MW_Variables */
    var $supervars;
    
    /** @protected
    * constructor (do not call)
    * @param supervars super MW_Variables to use
    */
    function MW_Variables($supervars) {
      $this->variables = array();
      $this->supervars = $supervars;
    }
  
    /**
    * returns value of given variable
    * @param name variable name
    */
    function get($name) {
      if (isset($this->variables[$name])) {
        return $this->variables[$name];
      }
      if (isset($this->supervars)) {
        return $this->supervars->get($name);
      }
      return null;
    }
  
    /**
    * sets value of given variable
    * @param name variable name
    * @param value: variable value
    */
    function set($name, $value) {
      $this->variables[$name] = $value;
    }
  
  }

  /** Wiki renderer state */
  class MW_RendererState {
    # [read-only] attributes
    /** MW_Renderer */
    var $renderer;
    /** raw text to render */
    var $raw;
    /** current MW_Variables */
    var $wiki_variables;
    
    /** @protected
    * constructor (do not call)
    * @param renderer MW_Renderer
    * @param page MW_Page or null
    * @param raw raw text to render
    * @param super_wiki_variables: super MW_Variables to use
    */
    function MW_RendererState($renderer, $page, $raw, $super_wiki_variables) {
      $this->renderer = $renderer;
      $this->raw = $raw;
      $this->wiki_variables = new_wiki_variables($super_wiki_variables);
      if ($page !== null) {
        $this->wiki_variables->set('page', $page->name);
        $this->wiki_variables->set('curpage', $page->name);
        $this->wiki_variables->set('revision', $page->revision);
        $this->wiki_variables->set('last_modified', format_last_modified($page->last_modified));
        $this->wiki_variables->set('has_content', ($page->has_content ? 'true' : ''));
      }
    }

    /** push new wiki_variables on top of existing ones */
    function push_variables() {
      $this->wiki_variables = new_wiki_variables($this->wiki_variables);
    }

    /** pop wiki_variables and restore their super ones */
    function pop_variables() {
      # we are on original variables which have globals as super
      if ($this->wiki_variables->supervars->supervars === null) {
        return;
      }
      $this->wiki_variables = $this->wiki_variables->supervars;
    }
    
    /** render Wiki markup to output */
    function render() {
      die ("abstract: render");
    }
    
  }

  /** Wiki renderer */
  class MW_Renderer {

    /**
    * render Wiki markup to output
    * @param page MW_Page (may be null)
    * @param raw raw text (empty message is output if raw text is empty)
    * @param vars (optional) MW_Variables to be used as global variables
    */
    function render($page, $raw, $vars = null) {
      die ("abstract: render");
    }
    
  }

  $importers = array();

  function register_importer($importer) {
    global $importers;
    array_push($importers, $importer);
  }

  function get_importers() {
    global $importers;
    return $importers;
  }

  function import($file, $with_history = true, $dataspaces = array(), $force_import = false) {
    global $importers;
    foreach ($importers as $importer) {
      # null is "unknown format", true is OK and string is error message
      $ret = $importer->import($file, $with_history, $dataspaces, $force_import);
      if ($ret !== null) {
        return $ret;
      }
    }
    return null;
  }

  class MW_Importer {

    function import($file, $with_history = true, $dataspaces = array(), $force_import = false) {
      die("abstract: import");
    }

    function get_format() {
      die("abstract: get_format");
    }
  
  }

  $exporters = array();

  function register_exporter($exporter) {
    global $exporters;
    $exporters[$exporter->get_format()] = $exporter;
  }

  function get_exporters() {
    global $exporters;
    return $exporters;
  }

  function export($format, $file, $with_history = true, $dataspaces = array()) {
    global $exporters;
    if (isset($exporters[$format])) {
      $exporter = $exporters[$format];
      return $exporter->export($file, $with_history, $dataspaces);
    }
    return null;
  }

  class MW_Exporter {

    function export($file, $with_history = true, $dataspaces = array()) {
      die("abstract: export");
    }
  
    function get_format() {
      die("abstract: get_format");
    }
    
  }

  class MW_InstallHandler {
    function show_install_message($msg) {
      die ("abstract: show_install_message");
    }
  }

  function show_install_message($msg) {
    global $install_handler;
    $install_handler->show_install_message($msg);
  }

  class MW_Extension {
    function get_name() {
      die("abstract: get_name");
    }
    function get_version() {
      return null;
    }
    function get_author() {
      return null;
    }
    function get_link() {
      return null;
    }
    function get_description() {
      return null;
    }
    function initialize() {
      return true;
    }
  }

  $extensions = array();

  function register_extension($extension) {
    global $extensions;
    array_push ($extensions, $extension);
  }

  function load_extensions($path, $recurse) {
    $d = dir($path);
    while (false !== ($entry = $d->read())) {
      if (($entry == '.') || ($entry == '..')) {
        continue;
      }
      $f = $path.DIRECTORY_SEPARATOR.$entry;
      if ($recurse && is_dir($f)) {
        load_extensions($f, false);
      }
      if ((is_file($f) || is_link($f)) && preg_match('/\.php$/i', $entry)) {
        include($f);
        debug("Loaded extension: $f");
      }
    }
    $d->close();
  }

  function initialize_extensions() {
    global $extensions;
    foreach ($extensions as $ext) {
      if (!$ext->initialize()) {
        die("Extension " . $ext->get_name() . " failed to initalize");
      }
      debug("Initialized extension: ".$ext->get_name());
    }
  }

  /**
  * Initialize miniWiki infrastructure.
  * <p>
  * Will load and initialize extensions.
  */
  function miniwiki_boot() {
    load_extensions(realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."ext"), true);
    initialize_extensions();
  }

?>
