<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # miniWiki library
  # auth: current MW_Auth
  # db: curent MW_Database
  # mw_db_encoding: database encoding
  # mw_db_use_server_collation: whether to let database sort strings according to its settings or
  #                             according to wanted mw_db_encoding
  # mw_texts: texts array
  # renderer: current MW_Renderer

  # miniWiki product name
  define("MW_NAME", "miniWiki");
  # miniWiki version as X.Y string
  define("MW_VERSION", "0.2");

  # main page name
  define("MW_PAGE_NAME_MAIN", "MainPage");
  # default page name (if none requested)
  define("MW_DEFAULT_PAGE_NAME", MW_PAGE_NAME_MAIN);
  # view page action (renders Wiki page)
  define("MW_ACTION_VIEW", "view");
  # view page source action (shows Wiki markup)
  define("MW_ACTION_VIEW_SOURCE", "view_source");
  # edit page action (shows Wiki editor)
  define("MW_ACTION_EDIT", "edit");
  # delete page action (really deletes page)
  define("MW_ACTION_DELETE", "delete");
  # show history action (shows history page)
  define("MW_ACTION_HISTORY", "history");
  # update page action (really changes Wiki page or shows a preview)
  define("MW_ACTION_UPDATE", "update");
  # login action (will show login dialog if current credentials are invalid)
  define("MW_ACTION_LOGIN", "login");
  # relogin action (will show login dialog even if current credentials are valid - needs correct old_user)
  define("MW_ACTION_RELOGIN", "relogin");
  # change password action (really changes password)
  define("MW_ACTION_CHANGE_PASSWORD", "change_password");
  # create user action (really creates user with disabled login)
  define("MW_ACTION_CREATE_USER", "create_user");
  # delete user action (really deletes user, user page is not deleted)
  define("MW_ACTION_DELETE_USER", "delete_user");
  # upload action
  define("MW_ACTION_UPLOAD", "upload");
  # default action (if none requested)
  define("MW_DEFAULT_ACTION", MW_ACTION_VIEW);
  # page name request variable
  define("MW_REQVAR_PAGE_NAME", "page_name");
  # action request variable
  define("MW_REQVAR_ACTION", "action");
  # page revision request variable
  define("MW_REQVAR_REVISION", "revision");
  # HEAD pseudo-revision name (latest revision will be used when talking to database)
  define("MW_REVISION_HEAD", "HEAD");
  # page content request variable (for update action)
  define("MW_REQVAR_CONTENT", "content");
  # update message (for update action)
  define("MW_REQVAR_MESSAGE", "message");
  # preview submit (for update action)
  define("MW_REQVAR_PREVIEW", "preview");
  # old user request variable (for relogin action)
  define("MW_REQVAR_OLD_USER", "old_user");
  # user request variable (for create user, delete user and change password actions)
  define("MW_REQVAR_USER", "user");
  # password request variable (for change password action)
  define("MW_REQVAR_PASS", "pass");
  # source file (for upload action)
  define("MW_REQVAR_SOURCEFILE", "sourcefile");
  # destination file (for upload action)
  define("MW_REQVAR_DESTFILE", "destfile");
  # name of table with pages
  define("MW_DB_TABLE_PAGES", "pages");
  # name of page name column
  define("MW_DB_TABLE_PAGES_COLUMN_NAME", "name");
  # name of page revision column
  define("MW_DB_TABLE_PAGES_COLUMN_REVISION", "revision");
  # name of page content column
  define("MW_DB_TABLE_PAGES_COLUMN_CONTENT", "content");
  # name of page last modification time column
  define("MW_DB_TABLE_PAGES_COLUMN_LAST_MODIFIED", "last_modified");
  # name of page revision message column
  define("MW_DB_TABLE_PAGES_COLUMN_MESSAGE", "message");
  # name of page revision author column
  define("MW_DB_TABLE_PAGES_COLUMN_USER", "user");
  # users list special page name
  define("MW_PAGE_NAME_USERS", "Special:Users");
  # pages list special page name
  define("MW_PAGE_NAME_PAGES", "Special:Pages");
  # user page name prefix
  define("MW_PAGE_NAME_PREFIX_USER", "User:");
  # admin user name
  define("MW_USER_NAME_ADMIN", "admin");
  # name of table with users
  define("MW_DB_TABLE_USERS", "users");
  # name of user name column
  define("MW_DB_TABLE_USERS_COLUMN_NAME", "name");
  # name of user password column (password is hashed with MD5 before storing)
  define("MW_DB_TABLE_USERS_COLUMN_PASSWORD", "password");
  # name of table with uploads
  define("MW_DB_TABLE_UPLOADS", "uploads");
  # name of upload name column
  define("MW_DB_TABLE_UPLOADS_COLUMN_NAME", "name");
  # name of upload revision column
  define("MW_DB_TABLE_UPLOADS_COLUMN_REVISION", "revision");
  # name of upload content column
  define("MW_DB_TABLE_UPLOADS_COLUMN_CONTENT", "content");
  # name of upload last modification time column
  define("MW_DB_TABLE_UPLOADS_COLUMN_LAST_MODIFIED", "last_modified");
  # name of upload revision message column
  define("MW_DB_TABLE_UPLOADS_COLUMN_MESSAGE", "message");
  # name of upload revision author column
  define("MW_DB_TABLE_UPLOADS_COLUMN_USER", "user");
  # uploads list special page name
  define("MW_PAGE_NAME_UPLOADS", "Special:Uploads");
  # upload page prefix
  define("MW_PAGE_NAME_PREFIX_UPLOAD", "Upload:");
  # data page prefix (raw uploaded file)
  define("MW_PAGE_NAME_PREFIX_DATA", "Data:");
  # default MIME type for uploaded files
  define("MW_DEFAULT_MIME_TYPE", "application/octet-stream");
  # image link page prefix (will render image directly)
  define("MW_LINK_NAME_PREFIX_IMAGE", "Image:");
  # default stylesheet upload name
  define("MW_DEFAULT_STYLESHEET_NAME", "default.css");
  # default javascript functions upload name
  define("MW_DEFAULT_JAVASCRIPT_FUNCTIONS_NAME", "functions.js");

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
  # info text array (do not use directly, use add_info_text() and get_info_text())
  $info_text = array();
  # add new information to be shown to the user
  # text: text to show
  function add_info_text($text) {
    global $info_text;
    array_push ($info_text, $text);
  }
  # returns array with all information texts
  function get_info_text() {
    global $info_text;
    return $info_text;
  }

  # wiki functions handling
  $wiki_functions = array();
  # register wiki function
  # name: wiki function name
  # cb: function callback
  function register_wiki_function($name, $cb) {
    global $wiki_functions;
    $wiki_functions[$name] = $cb;
  }
  # call wiki function
  # name: wiki function name
  # arg: wiki function argument
  function call_wiki_function($name, $arg) {
    global $wiki_functions;
    if (isset ($wiki_functions[$name])) {
      return call_user_func($wiki_functions[$name], $arg);
    }
    return null;
  }

  # returns value of wiki variable
  # name: wiki variable name
  function get_wiki_variable($name) {
    global $auth;
    if ($name == 'user') {
      if ($auth->is_logged) {
        return $auth->user;
      }
      return '';
    }
    if ($name == 'version') {
      return MW_VERSION;
    }
    return null;
  }

  # returns instance of MW_Request
  function new_request() {
    return new MW_Request();
  }

  # returns instance of MW_Auth
  function new_auth() {
    return new MW_Auth();
  }

  # returns instance of MW_Page
  # db: MW_Database
  # name: page name
  # revision: wanted revision
  function new_page($db, $name, $revision) {
    if ($name == MW_PAGE_NAME_USERS) {
      return new MW_Special_Users_Page($db);
    } elseif ($name == MW_PAGE_NAME_PAGES) {
      return new MW_Special_Pages_Page($db);
    } elseif ($name == MW_PAGE_NAME_UPLOADS) {
      return new MW_Special_Uploads_Page($db);
    } elseif (preg_match('/^'.MW_PAGE_NAME_PREFIX_USER.'/', $name)) {
      return new MW_Special_User_Page($db, $name, $revision);
    } elseif (preg_match('/^'.MW_PAGE_NAME_PREFIX_UPLOAD.'/', $name)) {
      return new MW_Special_Upload_Page($db, $name, $revision);
    } elseif (preg_match('/^'.MW_PAGE_NAME_PREFIX_DATA.'/', $name)) {
      return new MW_Special_Upload_Page($db, $name, $revision);
    }
    return new MW_DB_Page($db, $name, $revision);
  }

  # returns instance of MW_Special_User_Page
  # db: MW_Database
  # user: user name (not user page name)
  function new_user_page($db, $user) {
    return new MW_Special_User_Page($db, MW_PAGE_NAME_PREFIX_USER.$user, MW_REVISION_HEAD);
  }

  # returns instance of MW_Special_Upload_Page
  # db: MW_Database
  # name: upload name (not upload page name)
  # revision: wanted revision
  function new_upload_page($db, $name, $revision) {
    return new MW_Special_Upload_Page($db, MW_PAGE_NAME_PREFIX_UPLOAD.$name, $revision);
  }

  # returns instance of MW_Database
  # host: database host name
  # user: database user name
  # pass: database user password
  # dbname: database name
  function new_database($host, $user, $pass, $dbname) {
    return new MW_Database($host, $user, $pass, $dbname);
  }

  # returns instance of MW_Renderer
  # db: MW_Database
  function new_renderer($db) {
    return new MW_Renderer($db);
  }

  # HTTP request class
  class MW_Request {
    # [read-only] attributes
    # MW_REQVAR_PAGE_NAME
    var $page_name;
    # MW_REQVAR_ACTION
    var $action;
    # MW_REQVAR_REVISION
    var $revision;
    # MW_REQVAR_CONTENT
    var $content;
    # MW_REQVAR_MESSAGE
    var $message;
    # MW_REQVAR_PREVIEW
    var $preview;
    # MW_REQVAR_OLD_USER
    var $old_user;
    # MW_REQVAR_USER
    var $user;
    # MW_REQVAR_PASS
    var $pass;
    # MW_REQVAR_SOURCEFILE (as associative array with keys name, type, size and tmp_name)
    var $sourcefile;
    # MW_REQVAR_DESTFILE
    var $destfile;
    # whether this is head request
    var $is_head;

    # constructor (do not use directly, use new_request())
    function MW_Request() {
      $req_array = $_REQUEST;
      if (get_magic_quotes_gpc()) {
        $req_array = array_map("stripslashes", $req_array);
      }
      $this->page_name = (isset($req_array[MW_REQVAR_PAGE_NAME]) ? $req_array[MW_REQVAR_PAGE_NAME] : MW_DEFAULT_PAGE_NAME);
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

  # HTTP Auth class
  class MW_Auth {
    # [read-only] attributes
    # were credentials specified by user?
    var $has_credentials;
    # current user name
    var $user;
    # is user logged in?
    var $is_logged;

    # constructor (do not use directly, use new_auth())
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
    
    # [private] check if given password is valid for current user
    # pass: password
    # returns true if password is valid
    function validate($pass) {
      global $db;
      $user_page = new_user_page($db, $this->user);
      $this->is_logged = $user_page->is_password_valid($pass);
    }
    
    # returns true if user specified credentials, but those were not valid
    function is_invalid() {
      return ($this->has_credentials && !$this->is_logged);
    }
    
    # returns true if current user has permission to execute action specified by request on given page
    # see is_action_permitted() for more information
    # req: MW_Request
    # page: MW_Page
    function is_permitted($req, $page) {
      return $this->is_action_permitted($req->action, $page);
    }
    
    # returns true if current user has permission to execute given action on given page
    # everyone can relogin, login, view, view source and show history
    # logged user can edit, delete and update
    # only admin or same user can change password
    # only admin can create or delete user
    # action: action name
    # page: MW_Page
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

  # database access class
  class MW_Database {
    # [private] attributes
    # host name
    var $host;
    # user name
    var $user;
    # user password
    var $pass;
    # database name
    var $dbname;
    # MySQL connection or null
    var $conn;
    
    # constructor (do not use directly, use new_database())
    function MW_Database($host, $user, $pass, $dbname) {
      $this->host = $host;
      $this->user = $user;
      $this->pass = $pass;
      $this->dbname = $dbname;
    }
    
    # [private] initialize connection if not already open
    function init() {
      if (!isset ($this->conn)) {
        $this->conn = mysql_connect($this->host, $this->user, $this->pass) or die ("Can't connect to server : " . mysql_error());
        mysql_select_db($this->dbname, $this->conn) or die ("Can't select database : " . mysql_error());
        global $mw_db_use_server_collation, $mw_db_encoding;
        if ($mw_db_use_server_collation) {
          mysql_query("SET CHARACTER SET '".$mw_db_encoding."'", $this->conn);
        } else {
          mysql_query("SET NAMES '".$mw_db_encoding."'", $this->conn);
        }
      }
    }
    
    # destroy database connection
    # must be called before script ends
    function destroy() {
      if (isset ($this->conn)) {
        mysql_close($this->conn) or die ("Can't close connection : " . mysql_error());
        unset($this->conn);
      }
    }

    # [private] escape dangerous chars and quote value if needed
    # value: value to escape and quote
    # returns escaped and quoted value
    function quote_smart($value) {
       if (get_magic_quotes_gpc()) {
           $value = stripslashes($value);
       }
       if (!is_numeric($value)) {
           $value = "'" . mysql_real_escape_string($value) . "'";
       }
       return $value;
    }
    
    # execute non-query statement
    # st: statement with placeholders ('?')
    # ...: values to be used instead of placeholders
    # returns TRUE on success and FALSE on error
    function exec_statement($st) {
      return $this->open_query_from_array(func_get_args());
    }

    # [private] execute statement
    # query_array: array (statement with placeholders ('?'), values to be used instead of placeholders)
    # returns TRUE or MySQL resource on success and FALSE on error
    function open_query_from_array($query_array) {
      $query = array_shift($query_array);
      $args = $query_array;
      debug('MW_Database.open_query(query='.$query.')');
      $this->init();
      $i = 0;
      # this is the only preg_replace() with inline PHP code, but since we do not use backreferences
      # (they must be surrounded by ' or " and some chars are escaped in the process) we are safe here
      $query = preg_replace('/(\?)/e', '$this->quote_smart($args[$i++])', $query);
      debug('MW_Database.open_query: query='.$query);
      $result = mysql_query($query, $this->conn) or die ("Can't perform query : " . mysql_error());
      return $result;
    }

    # execute query
    # query: query with placeholders ('?')
    # ...: values to be used instead of placeholders
    # returns query result identifier
    function open_query($query) {
      return $this->open_query_from_array(func_get_args());
    }
    
    # close query result
    # result: result from open_query()
    function close_query($result) {
      mysql_free_result($result);
    }

    # returns next row from query result as array indexed by column numbers and also by column names
    # result: result from open_query()
    function fetch_query_result($result) {
      return mysql_fetch_array($result);
    }
    
  }

  # returns last modified value as UNIX timestamp (see mktime())
  # val: last modified value (as loaded from database)
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

  # returns last modified value retruned as YEAR/MONTH/DAY HOUR:MIN:SEC
  # val: last modified value (as loaded from database)
  function format_last_modified($val) {
    $ts = last_modified_as_timestamp($val);
    # TODO configurable
    return strftime("%Y/%m/%d %H:%M:%S", $ts);
  }
  
  # returns current date and time as last modified value
  function now_as_last_modified() {
    return strftime("%Y%m%d%H%M%S");
  }
  
  # [abstract] Wiki page
  class MW_Page {
    # [read-only] attributes
    # page name
    var $name;
    # page revision
    var $revision;
    # is some content loaded?
    var $has_content;
    # raw content (may be empty even if has_content is true) - valid after load()
    var $raw_content;
    # time of last modification (special format) - valid after load()
    var $last_modified;
    # page revision message (if any) - valid after load()
    var $message;
    # page revision author (if any) - valid after load()
    var $user;
    # page title - valid after load()
    var $title;
    # raw content length in bytes - maybe valid before load(), but may be set to null after load() if still not known
    var $raw_content_length;

    # constructor
    function MW_Page() {
      $this->name = '';
      $this->revision = MW_REVISION_HEAD;
      $this->has_content = false;
      $this->raw_content = '';
      $this->last_modified = 0;
      $this->message = '';
      $this->user = '';
      $this->title = '';
      $this->raw_content_length = null;
    }
    
    # [override, returns false] returns true if this page supports given action
    # action: action
    function has_action($action) {
      return false;
    }
    
    # [override, returns false] returns true if this page (with revision) exists
    function exists() {
      return false;
    }
    
    # [override, returns false] load page (with revision) content
    # returns true if content has been loaded successfully
    function load() {
      $this->title = $this->name;
      return false;
    }
    
    # [override] delete page (including all revisions)
    function delete() {
    }
    
    # [override] update and reload page (revision will change)
    # content: new content
    # message: change message
    function update($content, $message) {
    }
    
    # [override] set content for preview
    # content: new content
    function update_for_preview($content) {
    }
    
    # [override] render page (with revision) content (must be loaded first) to output
    function render() {
    }
    
    # returns URL for this page and given action
    # action: action name
    # ...rev: revision - defaults to current
    function url_for_action($action) {
      $rev = $this->revision;
      if (func_num_args() > 1) {
        $rev = func_get_arg(1);
      }
      return $_SERVER['PHP_SELF'] . '?' . MW_REQVAR_PAGE_NAME . '=' . urlencode($this->name) .
        (($action == MW_DEFAULT_ACTION) ? '' : '&' . MW_REQVAR_ACTION . '=' . urlencode($action)) .
        (($rev == MW_REVISION_HEAD) ? '' : '&' . MW_REQVAR_REVISION . '=' . urlencode($rev));
    }
    
    # [override, returns empty array] returns array of MW_Page instances representing all revisions including current one
    # returned array is ordered by revision in descending order (HEAD first)
    function get_all_revisions() {
      return array();
    }
  }
  
  # [abstract] special page
  class MW_Special_Page extends MW_Page {

    function MW_Special_Page() {
      parent::MW_Page();
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

  # special page with list of users (MW_PAGE_NAME_USERS)
  # allows user creation and deletion (if permitted)
  class MW_Special_Users_Page extends MW_Special_Page {
    # [read-only] attributes
    # MW_Database
    var $db;

    # constructor (do not use directly, use new_page())
    # db: MW_Database
    function MW_Special_Users_Page($db) {
      parent::MW_Special_Page();
      $this->name = MW_PAGE_NAME_USERS;
      $this->db = $db;
    }

    function render() {
      echo '<div class="special-users">', "\n";
      global $auth;
      if ($auth->is_action_permitted(MW_ACTION_CREATE_USER, $this)) {
        echo '<form method="post" action="', htmlspecialchars($this->url_for_action(MW_ACTION_CREATE_USER), ENT_QUOTES), '">', "\n";
        echo '<input type="text" size="40" name="', MW_REQVAR_USER,'"/>', "\n";
        global $mw_texts;
        echo '<input type="submit" value="', htmlspecialchars($mw_texts[MWT_CREATE_USER_BUTTON], ENT_QUOTES),'"/>', "\n";
        echo '</form>', "\n";
      }
      echo '<ul>', "\n";
      $query = $this->db->open_query('select '.MW_DB_TABLE_USERS_COLUMN_NAME.' from '.MW_DB_TABLE_USERS.' order by '.
         MW_DB_TABLE_USERS_COLUMN_NAME);
      while (($result = $this->db->fetch_query_result($query))) {
        $name = $result[MW_DB_TABLE_USERS_COLUMN_NAME];
        $page = new_user_page($this->db, $name);
        echo '<li><a href="', htmlspecialchars($page->url_for_action(MW_ACTION_VIEW), ENT_QUOTES), '">',
          htmlspecialchars($page->name, ENT_NOQUOTES), "</a>";
        if ($auth->is_action_permitted(MW_ACTION_DELETE_USER, $this)) {
          echo '<form class="delete-user" method="post" action="', htmlspecialchars($this->url_for_action(MW_ACTION_DELETE_USER), ENT_QUOTES), '">', "\n";
          echo '<input type="hidden" name="', MW_REQVAR_USER,'" value="', $name, '"/>', "\n";
          global $mw_texts;
          echo '<input type="submit" value="', htmlspecialchars($mw_texts[MWT_DELETE_USER_BUTTON], ENT_QUOTES),'"/>', "\n";
          echo '</form>', "\n";
        }
        echo "</li>\n";
      }
      $this->db->close_query($query);
      echo "</ul></div>\n";
    }

  }

  # special page with list of all regular pages (MW_PAGE_NAME_PAGES)
  class MW_Special_Pages_Page extends MW_Special_Page {
    # [read-only] attributes
    # MW_Database
    var $db;

    # constructor (do not use directly, use new_page())
    # db: MW_Database
    function MW_Special_Pages_Page($db) {
      parent::MW_Special_Page();
      $this->name = MW_PAGE_NAME_PAGES;
      $this->db = $db;
    }

    function render() {
      echo '<div class="special-pages"><ul>', "\n";
      $query = $this->db->open_query('select distinct('.MW_DB_TABLE_PAGES_COLUMN_NAME.') from '.MW_DB_TABLE_PAGES.' order by '.
         MW_DB_TABLE_PAGES_COLUMN_NAME);
      while (($result = $this->db->fetch_query_result($query))) {
        $name = $result[MW_DB_TABLE_PAGES_COLUMN_NAME];
        $page = new_page($this->db, $name, MW_REVISION_HEAD);
        echo '<li><a href="', htmlspecialchars($page->url_for_action(MW_ACTION_VIEW), ENT_QUOTES), '">',
          htmlspecialchars($page->name, ENT_NOQUOTES), "</a></li>\n";
      }
      $this->db->close_query($query);
      echo "</ul></div>\n";
    }

  }

  # special page with list of all uploads (MW_PAGE_NAME_UPLOADS)
  class MW_Special_Uploads_Page extends MW_Special_Page {
    # [read-only] attributes
    # MW_Database
    var $db;

    # constructor (do not use directly, use new_page())
    # db: MW_Database
    function MW_Special_Uploads_Page($db) {
      parent::MW_Special_Page();
      $this->name = MW_PAGE_NAME_UPLOADS;
      $this->db = $db;
    }

    function has_action($action) {
      if ($action == MW_ACTION_UPLOAD) {
        return true;
      }
      return parent::has_action($action);
    }
    
    function render() {
      echo '<div class="special-uploads">', "\n";
      global $auth;
      if ($auth->is_action_permitted(MW_ACTION_UPLOAD, $this)) {
        echo '<form enctype="multipart/form-data" action="', htmlspecialchars($this->url_for_action(MW_ACTION_UPLOAD), ENT_QUOTES), '" method="post">'. "\n";
        global $mw_texts;
        echo $mw_texts[MWT_SOURCE_FILENAME], '<input type="file" size="40" name="', MW_REQVAR_SOURCEFILE, '"/><br/>', "\n";
        echo $mw_texts[MWT_DEST_FILENAME], '<input type="text" size="40" name="', MW_REQVAR_DESTFILE, '"/><br/>', "\n";
        echo $mw_texts[MWT_UPLOAD_MESSAGE], "<br/>\n";
        echo '<textarea name="', MW_REQVAR_MESSAGE, '" rows="10" cols="60"/></textarea><br/>', "\n";
        echo '<input type="submit" value="', htmlspecialchars($mw_texts[MWT_UPLOAD_BUTTON], ENT_QUOTES),'"/><br/>', "\n";
        echo '</form>', "\n";
      }
      echo "<ul>\n";
      $query = $this->db->open_query('select distinct('.MW_DB_TABLE_UPLOADS_COLUMN_NAME.') from '.MW_DB_TABLE_UPLOADS.' order by '.
         MW_DB_TABLE_UPLOADS_COLUMN_NAME);
      while (($result = $this->db->fetch_query_result($query))) {
        $name = $result[MW_DB_TABLE_UPLOADS_COLUMN_NAME];
        $page = new_upload_page($this->db, $name, MW_REVISION_HEAD);
        echo '<li><a href="', htmlspecialchars($page->url_for_action(MW_ACTION_VIEW), ENT_QUOTES), '">',
          htmlspecialchars($page->name, ENT_NOQUOTES), "</a></li>\n";
      }
      $this->db->close_query($query);
      echo "</ul></div>\n";
    }

    # upload new file
    # content: new content
    # message: change message
    # name: file name
    function upload($content, $message, $name) {
      $page = new_upload_page($this->db, $name, MW_REVISION_HEAD);
      $page->update($content, $message);
      return $page;
    }

  }

  # regular Wiki page
  class MW_DB_Page extends MW_Page {
    # [read-only] attributes
    # MW_Database
    var $db;
    
    # constructor (do not use directly, use new_page())
    # db: MW_Database
    # name: page name
    # revision: page revision
    function MW_DB_Page($db, $name, $revision) {
      parent::MW_Page();
      $this->db = $db;
      $this->name = $name;
      $this->revision = $revision;
    }

    function has_action($action) {
      if ($action == MW_ACTION_UPLOAD) {
        return false;
      }
      return true;
    }
    
    function exists() {
      $query = $this->db->open_query('select * from '.MW_DB_TABLE_PAGES.' where '.
         MW_DB_TABLE_PAGES_COLUMN_NAME.'=?',
         $this->name);
      $ret = (($result = $this->db->fetch_query_result($query)));
      $this->db->close_query($query);
      return $ret;
    }

    # [private] process content (mainly directives)
    function process_content() {
      if ($this->has_content) {
        # TODO will happily process directives inside <pre> blocks
        if (preg_match_all("/(?:^|\n)#TITLE\s+(.*?)(?:$|\n)/", $this->raw_content, $matches)) {
          $this->title = $matches[1][count($matches[1]) - 1];
          $this->title = str_replace("\r", '', $this->title);
        }
      }
    }

    function load() {
      $rev = $this->revision;
      if ($rev == MW_REVISION_HEAD) {
        $query = $this->db->open_query('select max(revision) from '.MW_DB_TABLE_PAGES.' where '.MW_DB_TABLE_PAGES_COLUMN_NAME.'=?', $this->name);
        if (($result = $this->db->fetch_query_result($query))) {
          $rev = $result[0];
        }
        $this->db->close_query($query);
      }
      $query = $this->db->open_query('select *,length('.MW_DB_TABLE_PAGES_COLUMN_CONTENT.') as length from '.MW_DB_TABLE_PAGES.' where '.
         MW_DB_TABLE_PAGES_COLUMN_NAME.'=? and '.MW_DB_TABLE_PAGES_COLUMN_REVISION.'=?',
         $this->name, $rev);
      $this->has_content = false;
      if (($result = $this->db->fetch_query_result($query))) {
        $this->has_content = true;
        $this->raw_content = $result[MW_DB_TABLE_PAGES_COLUMN_CONTENT];
        $this->raw_content_length = $result['length'];
        $this->last_modified = $result[MW_DB_TABLE_PAGES_COLUMN_LAST_MODIFIED];
        $this->message = $result[MW_DB_TABLE_PAGES_COLUMN_MESSAGE];
        $this->user = $result[MW_DB_TABLE_PAGES_COLUMN_USER];
      }
      $this->db->close_query($query);
      $this->title = $this->name;
      $this->process_content();
      return $this->has_content;
    }
    
    function delete() {
      $this->db->exec_statement('delete from '.MW_DB_TABLE_PAGES.' where '.MW_DB_TABLE_PAGES_COLUMN_NAME.'=?', $this->name);
      $this->has_content = false;
    }
    
    function update($content, $message) {
      global $auth;
      $this->user = $auth->user;
      $this->revision = MW_REVISION_HEAD;
      $this->db->exec_statement('lock tables '.MW_DB_TABLE_PAGES.' write');
      $rev = 1;
      $query = $this->db->open_query('select max(revision) from '.MW_DB_TABLE_PAGES.' where '.MW_DB_TABLE_PAGES_COLUMN_NAME.'=?', $this->name);
      if (($result = $this->db->fetch_query_result($query))) {
        $rev = $result[0] + 1;
      }
      $this->db->close_query($query);
      $this->db->exec_statement('insert into '.MW_DB_TABLE_PAGES.' ('.
        MW_DB_TABLE_PAGES_COLUMN_NAME. ', '.
        MW_DB_TABLE_PAGES_COLUMN_REVISION. ', '.
        MW_DB_TABLE_PAGES_COLUMN_CONTENT. ', '.
        MW_DB_TABLE_PAGES_COLUMN_MESSAGE. ', '.
        MW_DB_TABLE_PAGES_COLUMN_USER. ') values (?, ?, ?, ?, ?)',
        $this->name, $rev, $content, $message, $this->user);
      $this->db->exec_statement('unlock tables');
      $this->load();
    }

    function update_for_preview($content) {
      if ($this->exists()) {
        $this->load();
      }
      $this->raw_content = $content;
      $this->has_content = true;
      $this->process_content();
    }
    
    function render() {
      global $renderer;
      $renderer->render($this->raw_content);
    }
    
    function get_all_revisions() {
      $query = $this->db->open_query('select '.
         'length('.MW_DB_TABLE_PAGES_COLUMN_CONTENT.') as length,'.
         MW_DB_TABLE_PAGES_COLUMN_LAST_MODIFIED.','.
         MW_DB_TABLE_PAGES_COLUMN_MESSAGE.','.
         MW_DB_TABLE_PAGES_COLUMN_USER.','.
         MW_DB_TABLE_PAGES_COLUMN_NAME.','.
         MW_DB_TABLE_PAGES_COLUMN_REVISION.
        ' from '.MW_DB_TABLE_PAGES.' where '.
         MW_DB_TABLE_PAGES_COLUMN_NAME.'=? order by '.MW_DB_TABLE_PAGES_COLUMN_REVISION.' desc',
         $this->name);
      $ret = array();
      $is_head = true;
      while (($result = $this->db->fetch_query_result($query))) {
        $page = new_page($this->db, $result[MW_DB_TABLE_PAGES_COLUMN_NAME],
          $result[MW_DB_TABLE_PAGES_COLUMN_REVISION]);
        $page->last_modified = $result[MW_DB_TABLE_PAGES_COLUMN_LAST_MODIFIED];
        $page->message = $result[MW_DB_TABLE_PAGES_COLUMN_MESSAGE];
        $page->user = $result[MW_DB_TABLE_PAGES_COLUMN_USER];
        if ($is_head) {
          $page->revision = MW_REVISION_HEAD;
        }
        $page->raw_content_length = $result['length'];
        array_push ($ret, $page);
        $is_head = false;
      }
      $this->db->close_query($query);
      return $ret;
    }
    
  }

  # special upload page (MW_PAGE_NAME_PREFIX_UPLOAD or MW_PAGE_NAME_PREFIX_DATA)
  class MW_Special_Upload_Page extends MW_Page {
    # [read-only] attributes
    # MW_Database
    var $db;
    # upload name
    var $upload_name;
    # is this an upload (MW_PAGE_NAME_PREFIX_UPLOAD) or data (MW_PAGE_NAME_PREFIX_DATA) page?
    var $is_data_page;
    # MIME type
    var $mime_type;
    
    # constructor (do not use directly, use new_page())
    # db: MW_Database
    # name: page name
    # revision: page revision
    function MW_Special_Upload_Page($db, $name, $revision) {
      parent::MW_Page();
      $this->db = $db;
      $this->name = $name;
      if (strpos($name, MW_PAGE_NAME_PREFIX_UPLOAD) === 0) {
        $this->upload_name = substr($name, strlen(MW_PAGE_NAME_PREFIX_UPLOAD));
        $this->is_data_page = false;
      } else {
        $this->upload_name = substr($name, strlen(MW_PAGE_NAME_PREFIX_DATA));
        $this->is_data_page = true;
      }
      $this->revision = $revision;
      $this->mime_type = $this->guess_type($this->upload_name);
    }

    # [private] guess MIME type from file name (form extension)
    # name: file name
    function guess_type($name) {
      if (preg_match('/\.txt$/i', $name)) {
        return "text/plain";
      }
      if (preg_match('/\.html?$/i', $name)) {
        return "text/html";
      }
      if (preg_match('/\.xml?$/i', $name)) {
        return "text/xml";
      }
      if (preg_match('/\.css$/i', $name)) {
        return "text/css";
      }
      if (preg_match('/\.js$/i', $name)) {
        return "application/x-javascript";
      }
      if (preg_match('/\.(jpeg|jpg|jpe)$/i', $name)) {
        return "image/jpeg";
      }
      if (preg_match('/\.png$/i', $name)) {
        return "image/png";
      }
      if (preg_match('/\.gif$/i', $name)) {
        return "image/gif";
      }
      return MW_DEFAULT_MIME_TYPE;
    }

    function has_action($action) {
      switch ($action) {
        case MW_ACTION_VIEW_SOURCE:
          return false;
      }
      return true;
    }
    
    function exists() {
      $query = $this->db->open_query('select * from '.MW_DB_TABLE_UPLOADS.' where '.
         MW_DB_TABLE_UPLOADS_COLUMN_NAME.'=?',
         $this->upload_name);
      $ret = (($result = $this->db->fetch_query_result($query)));
      $this->db->close_query($query);
      return $ret;
    }

    # unlike general contract this function does not load raw content - use load_with_raw_content()
    # note that render() does not render raw content, but dynamic content
    function load() {
      return $this->load_internal(false);
    }
    
    # same as load(), but also loads raw content (as described by general contract for load())
    function load_with_raw_content() {
      return $this->load_internal(true);
    }
    
    # [private] internal load() function
    # with_raw: whether to load raw_content or not
    function load_internal($with_raw) {
      $rev = $this->revision;
      if ($rev == MW_REVISION_HEAD) {
        $query = $this->db->open_query('select max(revision) from '.MW_DB_TABLE_UPLOADS.' where '.MW_DB_TABLE_UPLOADS_COLUMN_NAME.'=?', $this->upload_name);
        if (($result = $this->db->fetch_query_result($query))) {
          $rev = $result[0];
        }
        $this->db->close_query($query);
      }
      $query = $this->db->open_query('select '.
         ($with_raw ? MW_DB_TABLE_UPLOADS_COLUMN_CONTENT.',' : '').
         'length('.MW_DB_TABLE_UPLOADS_COLUMN_CONTENT.') as length,'.
         MW_DB_TABLE_UPLOADS_COLUMN_LAST_MODIFIED.','.
         MW_DB_TABLE_UPLOADS_COLUMN_MESSAGE.','.
         MW_DB_TABLE_UPLOADS_COLUMN_USER.
         ' from '.MW_DB_TABLE_UPLOADS.' where '.
         MW_DB_TABLE_UPLOADS_COLUMN_NAME.'=? and '.MW_DB_TABLE_UPLOADS_COLUMN_REVISION.'=?',
         $this->upload_name, $rev);
      $this->has_content = false;
      if (($result = $this->db->fetch_query_result($query))) {
        $this->has_content = true;
        if ($with_raw) {
          $this->raw_content = $result[MW_DB_TABLE_UPLOADS_COLUMN_CONTENT];
        }
        $this->raw_content_length = $result['length'];
        $this->last_modified = $result[MW_DB_TABLE_UPLOADS_COLUMN_LAST_MODIFIED];
        $this->message = $result[MW_DB_TABLE_UPLOADS_COLUMN_MESSAGE];
        $this->user = $result[MW_DB_TABLE_UPLOADS_COLUMN_USER];
      }
      $this->db->close_query($query);
      $this->title = $this->name;
      return $this->has_content;
    }
    
    function delete() {
      $this->db->exec_statement('delete from '.MW_DB_TABLE_UPLOADS.' where '.MW_DB_TABLE_UPLOADS_COLUMN_NAME.'=?', $this->upload_name);
      $this->has_content = false;
    }
    
    function update($content, $message) {
      global $auth;
      $this->user = $auth->user;
      $this->revision = MW_REVISION_HEAD;
      $this->db->exec_statement('lock tables '.MW_DB_TABLE_UPLOADS.' write');
      $rev = 1;
      $query = $this->db->open_query('select max(revision) from '.MW_DB_TABLE_UPLOADS.' where '.MW_DB_TABLE_UPLOADS_COLUMN_NAME.'=?', $this->upload_name);
      if (($result = $this->db->fetch_query_result($query))) {
        $rev = $result[0] + 1;
      }
      $this->db->close_query($query);
      $this->db->exec_statement('insert into '.MW_DB_TABLE_UPLOADS.' ('.
        MW_DB_TABLE_UPLOADS_COLUMN_NAME. ', '.
        MW_DB_TABLE_UPLOADS_COLUMN_REVISION. ', '.
        MW_DB_TABLE_UPLOADS_COLUMN_CONTENT. ', '.
        MW_DB_TABLE_UPLOADS_COLUMN_MESSAGE. ', '.
        MW_DB_TABLE_UPLOADS_COLUMN_USER. ') values (?, ?, ?, ?, ?)',
        $this->upload_name, $rev, $content, $message, $this->user);
      $this->db->exec_statement('unlock tables');
      $this->load();
    }

    function render() {
      global $renderer, $mw_texts;
      if ($this->is_data_page) {
        trigger_error("INTERNAL: MW_Special_Upload_Page.render(): is_data_page is true", E_USER_ERROR);
      } else {
        $text = $mw_texts[MWT_UPLOAD_PAGE_TEXT];
        $link_prefix = (strpos($this->mime_type, "image/") === 0) ? MW_LINK_NAME_PREFIX_IMAGE : MW_PAGE_NAME_PREFIX_DATA;
        $text = str_replace('%LINK%', $link_prefix.$this->upload_name.($this->revision != MW_REVISION_HEAD ? '$'.$this->revision : ''), $text);
        $text = str_replace('%MESSAGE%', $this->message, $text);
        $text = str_replace('%FILENAME%', $this->upload_name, $text);
        $text = str_replace('%MIMETYPE%', $this->mime_type, $text);
        $text = str_replace('%LENGTH%', $this->raw_content_length, $text);
        $renderer->render($text);
      }
    }

    function get_all_revisions() {
      $query = $this->db->open_query('select '.
         'length('.MW_DB_TABLE_UPLOADS_COLUMN_CONTENT.') as length,'.
         MW_DB_TABLE_UPLOADS_COLUMN_LAST_MODIFIED.','.
         MW_DB_TABLE_UPLOADS_COLUMN_MESSAGE.','.
         MW_DB_TABLE_UPLOADS_COLUMN_USER.','.
         MW_DB_TABLE_UPLOADS_COLUMN_NAME.','.
         MW_DB_TABLE_UPLOADS_COLUMN_REVISION.
        ' from '.MW_DB_TABLE_UPLOADS.' where '.
         MW_DB_TABLE_UPLOADS_COLUMN_NAME.'=? order by '.MW_DB_TABLE_UPLOADS_COLUMN_REVISION.' desc',
         $this->upload_name);
      $ret = array();
      $is_head = true;
      while (($result = $this->db->fetch_query_result($query))) {
        $page = new_upload_page($this->db, $result[MW_DB_TABLE_UPLOADS_COLUMN_NAME],
          $result[MW_DB_TABLE_UPLOADS_COLUMN_REVISION]);
        $page->last_modified = $result[MW_DB_TABLE_UPLOADS_COLUMN_LAST_MODIFIED];
        $page->message = $result[MW_DB_TABLE_UPLOADS_COLUMN_MESSAGE];
        $page->user = $result[MW_DB_TABLE_UPLOADS_COLUMN_USER];
        if ($is_head) {
          $page->revision = MW_REVISION_HEAD;
        }
        $page->raw_content_length = $result['length'];
        array_push ($ret, $page);
        $is_head = false;
      }
      $this->db->close_query($query);
      return $ret;
    }

  }

  # [private] internal Wiki renderer state
  class MW_Renderer_State {
    # [read-only] attributes
    # MW_Renderer
    var $renderer;
    # raw text to render
    var $raw;
    # headings (array of arrays (
    #  'title' => heading title,
    #  'level' => heading level,
    #  'number' => heading number,
    #  'anchor' => heading anchor,
    # ))
    var $headings;
    # headings counter (current number)
    var $headings_counter;
    
    # constructor (do not call)
    # renderer: MW_Renderer
    # raw: raw text to render
    function MW_Renderer_State($renderer, $raw) {
      $this->renderer = $renderer;
      $this->raw = $raw;
      $this->headings = array();
      $this->headings_counter = '';
    }
    
    # [private] add heading into headings array
    # level: heading level
    # title: heading title
    # anchor: heading anchor
    function add_heading($level, $title, $anchor) {
      if ($level < 2) {
        # super-headings (level 1, single =) are ignored
        return;
      }
      $counter = (strlen($this->headings_counter) > 0) ? explode('.', $this->headings_counter) : array();
      $counter = array_pad($counter, $level - 1, 0);
      $counter[$level - 2]++;
      for ($i = $level - 1; $i < count($counter); $i++) {
        unset($counter[$i]);
      }
      $this->headings_counter = implode('.', $counter);
      $heading = array('title' => $title, 'level' => $level, 'anchor' => $anchor, 'number' => $this->headings_counter);
      array_push($this->headings, $heading);
    }

    # [private] makes anchor name from normal name
    # replaces spaces with '_'
    # name: name to make anchor name from
    function make_anchor_name($name) {
      return str_replace(' ', '_', $name);
    }

    # [private] returns HTML code for link to internal Wiki page
    # name: page name
    # title: link title
    function process_internal_link($name, $title) {
      debug('MW_Page.process_internal_link(name='.$name.', title='.$title.')');
      $fragment = null;
      if (!(strpos($name, '#') === false)) {
        list($name, $fragment) = explode('#', $name, 2);
      }
      $revision = MW_REVISION_HEAD;
      if (!(strpos($name, '$') === false)) {
        list($name, $revision) = explode('$', $name, 2);
      }
      $is_image = false;
      if ($name == '') {
        # we must override current actions we are rendered with
        global $page;
        $linked_page = $page;
        $link_exists = true;
      } else {
        if (!(strpos($name, MW_LINK_NAME_PREFIX_IMAGE) === false)) {
          $is_image = true;
          $upload_name = MW_PAGE_NAME_PREFIX_UPLOAD . substr($name, strlen(MW_LINK_NAME_PREFIX_IMAGE));
          $data_name = MW_PAGE_NAME_PREFIX_DATA . substr($name, strlen(MW_LINK_NAME_PREFIX_IMAGE));
          $data_page = new_page($this->renderer->db, $data_name, $revision);
          # small hack to change link on Upload page
          global $page;
          $name = (is_a($page, "MW_Special_Upload_Page") ? $data_name : $upload_name);
        }
        $linked_page = new_page($this->renderer->db, $name, $revision);
        $link_exists = $linked_page->exists();
      }
      $link_action = ($link_exists) ? MW_ACTION_VIEW : MW_ACTION_EDIT;
      $link_type = ($link_exists) ? ($is_image ? 'image' : 'view-link') : 'edit-link';
      return '<a href="'.htmlspecialchars($linked_page->url_for_action($link_action), ENT_QUOTES)
        .(($fragment != null) ? '#'.$fragment : '')
        .'" class="'.$link_type.'">'
        .($is_image && $link_exists
          ? '<img src="'.htmlspecialchars($data_page->url_for_action($link_action), ENT_QUOTES).'"'
            .' alt="'.$title.'"'
            .' longdesc="'.htmlspecialchars($linked_page->url_for_action($link_action), ENT_QUOTES).'"'
            .'/>'
          : $title)
        .'</a>';
    }

    # [private] callback for preg_replace_callback in process_inline()
    function process_inline_cb($matches) {
      $type = $matches[0];
      if (preg_match("/^'''/", $type)) {
        return $this->process_inline_cb_strong($matches);
      }
      if (preg_match("/^''/", $type)) {
        return $this->process_inline_cb_em($matches);
      }
      if (preg_match("/^\[\[/", $type)) {
        return $this->process_inline_cb_internal_link($matches);
      }
      if (preg_match("/^\[/", $type)) {
        return $this->process_inline_cb_external_link($matches);
      }
      if (preg_match("/^&lt;form-field/", $type)) {
        return $this->process_inline_cb_form_field($matches);
      }
      if (preg_match("/^&lt;form/", $type)) {
        return $this->process_inline_cb_form($matches);
      }
      if (preg_match(",^&lt;/form,", $type)) {
        return $this->process_inline_cb_form_end($matches);
      }
      if (preg_match("/^&lt;/", $type)) {
        return $this->process_inline_cb_br($matches);
      }
    }


    # [private] sub-callback for preg_replace_callback in process_inline()
    function process_inline_cb_strong($matches) {
      $text = $matches[1];
      return '<strong>'.$text.'</strong>';
    }

    # [private] sub-callback for preg_replace_callback in process_inline()
    function process_inline_cb_em($matches) {
      $text = $matches[1];
      return '<em>'.$text.'</em>';
    }

    # [private] sub-callback for preg_replace_callback in process_inline()
    function process_inline_cb_internal_link($matches) {
      $name = $matches[1];
      $title = ((count($matches) > 2) ? $matches[2] : $name);
      return $this->process_internal_link($name, $title);
    }

    # [private] sub-callback for preg_replace_callback in process_inline()
    function process_inline_cb_external_link($matches) {
      $url = $matches[1];
      $title = ((count($matches) > 2) ? $matches[2] : $url);
      return '<a href="'.$url.'">'.$title.'</a>';
    }

    # [private] sub-callback for preg_replace_callback in process_inline()
    function process_inline_cb_br($matches) {
      return '<br/>';
    }

    # [private] sub-callback for preg_replace_callback in process_inline()
    function process_inline_cb_form_end($matches) {
      return '</form>';
    }

    # [private] sub-callback for preg_replace_callback in process_inline()
    function process_inline_cb_form($matches) {
      $method = $matches[1];
      $action = $matches[2];
      return '<form method="'.$method.'" action="'.$action.'">';
    }

    # [private] sub-callback for preg_replace_callback in process_inline()
    function process_inline_cb_form_field($matches) {
      $name = $matches[1];
      $type = $matches[2];
      $value = ((count($matches) > 3) ? $matches[3] : '');
      if ($type == 'option') {
        $ret = '<select'.(($name != '#') ? ' name="'.$name.'"' : '').'>'."\n";
        $options = explode('|', $value);
        foreach ($options as $option) {
          $opt_value = '';
          $opt_text = '';
          $opt_selected = false;
          if (strpos($option, '~') === 0) {
            $opt_selected = true;
            $option = substr($option, 1);
          }
          if (strpos($option, ':') === false) {
            $opt_text = $option;
          } else {
            $opts = explode(':', $option, 2);
            $opt_value = $opts[0];
            $opt_text = $opts[1];
          }
          $ret .= '<option';
          if ($opt_value != '') {
            $ret .= ' value="'.$opt_value.'"';
          }
          if ($opt_selected) {
            $ret .= ' selected="selected"';
          }
          if ($opt_text != '') {
            $ret .= '>'.$opt_text.'</option>';
          } else {
            $ret .= '/>';
          }
          $ret .= "\n";
        }
        $ret .= '</select>';
        return $ret;
      } else {
        return '<input type="'.$type.'"'
          .(($name != '#') ? ' name="'.$name.'"' : '')
          .(($value != '') ? ' value="'.$value.'"' : '')
          .'/>';
      }
    }

    # [private] returns HTML code for inline Wiki markup:
    #   '''BOLD''', ''ITALIC'', [[PAGE_NAME:LINK_TITLE]], [[PAGE_NAME]], [URL LINK_TITLE], [URL], <br> and forms
    # text: text to process
    function process_inline($text) {
      debug('MW_Page.process_inline(text='.$text.')');
      $text = preg_replace_callback(
        array("/'''(.*?)'''/",
              "/''(.*?)''/",
              '/\[\[([^\]]*?)\|(.*?)\]\]/',
              '/\[\[([^\]]*?)\]\]/',
              '/\[([^\]]*?)\s+([^\]].*?)\]/',
              '/\[([^\]]*?)\]/',
              '/&lt;[Bb][Rr]&gt;/',
              '/&lt;form\s+(.+?)\s+(.+?)\s*&gt;/',
              '/&lt;form-field\s+(.+?)\s+(.+?)(?:\s+(.+?))?&gt;/',
              ',&lt;/form.*?&gt;,',
              ),
        array(&$this, 'process_inline_cb'),
        $text);
      return $text;
    }
    
    # [private] callback for preg_replace_callback in process_heading_block()
    function process_heading_block_cb($matches) {
      debug('MW_Page.process_heading_block_cb(matches='.join(', ', $matches).')');
      $h_level = strlen($matches[1]);
      $h_name = $matches[2];
      $h_anchor = $this->make_anchor_name($h_name);
      $this->add_heading($h_level, $h_name, $h_anchor);
      return '<h'.$h_level.'><a name="'.$h_anchor.'">'.$this->process_inline($h_name).'</a></h'.$h_level.'>';
    }
    
    # [private] returns HTML code for heading block (=H1= ... ======H6======)
    # heading title is inline processed
    # block: block to process
    function process_heading_block($block) {
      debug('MW_Page.process_heading_block(block='.$block.')');
      $block = preg_replace_callback(
        '/^(=+)\s*(.*?)\s*=+\s*$/',
        array(&$this, 'process_heading_block_cb'),
        $block);
      return $block."\n";
    }
    
    # [private] returns HTML code for list item (* ... **********...)
    # item content is inline processed
    # item: item to process
    # depth: depth of previous item in the same list block or 0
    function process_list_item($item, &$depth) {
      debug('MW_Page.process_list_item(item='.$item.', depth='.$depth.')');
      $ret = '';
      $i = 0;
      while (($i < strlen($item)) && ($item[$i] == '*')) {
        $i++;
      }
      debug('MW_Page.process_list_item: i='.$i);
      if ($i > $depth) {
        while ($i > $depth) {
          $ret .= "<ul>\n";
          $depth++;
          if ($i != $depth) {
            $ret .= '<li>';
          }
        }
      } elseif ($i < $depth) {
        while ($i < $depth) {
          $ret .= "</li>\n</ul>\n";
          $depth--;
          if ($i > 0) {
            $ret .= "</li>\n";
          }
        }
      } else {
        $ret .= "</li>\n";
      }
      if (strlen($item) > 0) {
        $ret .= "<li>".$this->process_inline(ltrim(substr($item, $i)));
      }
      return $ret;
    }
    
    # [private] returns HTML code for list block (block starting with *)
    # list block is composed of list items
    # block: block to process
    function process_list_block($block) {
      debug('MW_Page.process_list_block(block='.$block.')');
      $ret = '';
      $lines = explode("\n", $block);
      $cur_item = '';
      $cur_depth = 0;
      foreach ($lines as $line) {
        if ($line[0] == '*') {
          if (strlen($cur_item) > 0) {
            $ret .= $this->process_list_item($cur_item, $cur_depth);
          }
          $cur_item = $line;
        } else {
          $cur_item .= ' ' . $line;
        }
      }
      if (strlen($cur_item) > 0) {
        $ret .= $this->process_list_item($cur_item, $cur_depth);
      }
      $ret .= $this->process_list_item('', $cur_depth);
      return $ret;
    }
    
    # [private] returns HTML for normal block (paragraph)
    # block content is inline processed
    # block: block to process
    function process_normal_block($block) {
      return "<p>".$this->process_inline($block)."</p>\n";
    }
    
    # [private] returns HTML for given block
    # detects heading, list and normal blocks
    # block: block to process
    function process_block($block) {
      if ($block{0} == '=') {
        return $this->process_heading_block($block);
      } elseif ($block{0} == '*') {
        return $this->process_list_block($block);
      } elseif ($block == '---') {
        return "<hr/>\n";
      } else {
        return $this->process_normal_block($block);
      }
    }

    # [private] returns HTML for given block chain
    # chain is composed of blocks separated by empty lines
    # chain: chain to process
    function process_block_chain($chain) {
      debug('MW_Page.process_block_chain(chain='.$chain.')');
      $chain = htmlspecialchars($chain, ENT_NOQUOTES);
      $blocks = preg_split('/(^|\n+)[ \t]*(\n+|$)/', $chain, -1, PREG_SPLIT_NO_EMPTY);
      $ret = '';
      foreach ($blocks as $block) {
        $ret .= $this->process_block($block);
      }
      return $ret;
    }
    
    # [private] callback for preg_replace_callback in process_includes()
    function process_includes_cb($matches) {
      debug('MW_Page.process_includes_cb(matches='.join(', ', $matches).')');
      $inc_page_name = $matches[1];
      if ($inc_page_name[0] == '&') {
        $wiki_func_array = preg_split('/\s+/', substr($inc_page_name, 1), 2);
        $wiki_func = $wiki_func_array[0];
        $wiki_func_arg = null;
        if (count($wiki_func_array) > 1) {
          $wiki_func_arg = $wiki_func_array[1];
        }
        $wiki_func_ret = call_wiki_function($wiki_func, $wiki_func_arg);
        if (!($wiki_func_ret === null)) {
          return $wiki_func_ret;
        }
      } elseif ($inc_page_name[0] == '$') {
        $wiki_var = substr($inc_page_name, 1);
        $wiki_var_ret = get_wiki_variable($wiki_var);
        if (!($wiki_var_ret === null)) {
          return $wiki_var_ret;
        }
      } else {
        $inc_page = new_page($this->renderer->db, $inc_page_name, MW_REVISION_HEAD);
        if ($inc_page->load()) {
          return str_replace("\r", '', $inc_page->raw_content);
        }
      }
      return '[['.$inc_page_name.']]';
    }
    
    # [private] process includes {{...}}
    # this function only replaces all {{...}} with its contents (NOT processed/rendered)
    # line: line to process
    function process_includes($line) {
      debug('MW_Page.process_includes(line='.$line.')');
      $line = preg_replace_callback(
        '/{{(.*?)}}/',
        array(&$this, 'process_includes_cb'),
        $line);
      return $line;
    }
    
    # render Wiki markup to output
    # raw text is split into blocks (separated by empty lines) and then rendered,
    # text between <pre> and </pre> (must begin lines) is not Wiki-processed (regardless of blocks)
    function render() {
      if (strlen($this->raw) == 0) {
        global $mw_texts;
        echo $mw_texts[MWT_EMPTY_PAGE], "\n";
        return;
      }
      $src = str_replace("\r", '', $this->raw);
      $lines = explode("\n", $src);
      $in_pre = false;
      $current_chain = '';
      $notoc = false;
      $output = '';
      # the count() hack is because some our lines are empty which causes while(array_shift) to terminate prematurely
      while (count($lines) > 0) {
        $line = array_shift($lines);
        if (!$in_pre && preg_match('/^<pre>/i', $line)) {
          $output .= $this->process_block_chain($current_chain);
          $current_chain = '';
          $in_pre = true;
          $line = substr($line, 5);
          $output .= '<pre>';
        }
        if ($in_pre) {
          if (preg_match(',^</pre>,i', $line)) {
            $in_pre = 0;
            $output .= "</pre>\n";
          } else {
            $output .= htmlspecialchars($line, ENT_NOQUOTES) . "\n";
          }
        } elseif (strpos($line, '#NOTOC') === 0) {
          $notoc = true;
        } elseif (strpos($line, '#') === 0) {
          # omit directives
        } elseif (!(strpos($line, '{{') === false)) {
          $line = $this->process_includes($line);
          $lines = array_merge(explode("\n", $line), $lines);
        } else {
          $current_chain .= $line . "\n";
        }
      }
      $output .= $this->process_block_chain($current_chain);
      # TOC
      if (!$notoc && count($this->headings)) {
        echo '<div class="toc">', "\n";
        echo '<ul>', "\n";
        foreach ($this->headings as $heading) {
          # we must override current actions we are rendered with
          global $page;
          echo '<li class="toc-level-', $heading["level"] - 1, '"><a href="'
            .htmlspecialchars($page->url_for_action(MW_ACTION_VIEW), ENT_QUOTES)
            .'#', $heading['anchor'], '">', $heading['number'], ' ', $heading['title'], '</a></li>', "\n";
        }
        echo '</ul>', "\n";
        echo '</div>', "\n";
      }
      echo $output;
    }
    
  }

  # Wiki renderer
  class MW_Renderer {
    # [read-only] attributes
    # MW_Database
    var $db;
    
    # constructor (do not use directly, use new_renderer())
    # db: MW_Database
    function MW_Renderer($db) {
      $this->db = $db;
    }

    # render Wiki markup to output
    # raw text is split into blocks (separated by empty lines) and then rendered,
    # text between <pre> and </pre> (must begin lines) is not Wiki-processed (regardless of blocks)
    # raw: raw text (empty message is output if raw text is empty)
    function render($raw) {
      $state = new MW_Renderer_State($this, $raw);
      $state->render();
    }
    
  }

  # special user page (MW_PAGE_NAME_PREFIX_USER)
  # this page always exists even if empty (but then it is not stored in database)
  # user associated with this page may not exist
  class MW_Special_User_Page extends MW_DB_Page {
    # [read-only] attributes
    # user associated with this user page
    var $related_user;
    
    # constructor (do not use directly, use new_user_page() or new_page())
    # db: MW_Database
    # name: page name
    # revision: page revision
    function MW_Special_User_Page($db, $name, $revision) {
      parent::MW_DB_Page($db, $name, $revision);
      $this->related_user = substr($name, strlen(MW_PAGE_NAME_PREFIX_USER));
      $this->last_modified = now_as_last_modified();
    }

    # user page always exists
    function exists() {
      return true;
    }
    
    # user page is always loaded
    function load() {
      parent::load();
      $this->has_content = true;
      return true;
    }
    
    # user page exists even if deleted
    function delete() {
      parent::delete();
      $this->has_content = true;
    }
    
    function render() {
      global $auth;
      if ($auth->is_action_permitted(MW_ACTION_CHANGE_PASSWORD, $this)) {
        echo '<form method="post" action="', htmlspecialchars($this->url_for_action(MW_ACTION_CHANGE_PASSWORD), ENT_QUOTES), '">', "\n";
        echo '<input type="hidden" name="', MW_REQVAR_USER,'" value="', $this->related_user, '"/>', "\n";
        echo '<input type="password" size="40" name="', MW_REQVAR_PASS,'"/>', "\n";
        global $mw_texts;
        echo '<input type="submit" value="', htmlspecialchars($mw_texts[MWT_CHANGE_PASSWORD_BUTTON], ENT_QUOTES),'"/>', "\n";
        echo '</form>', "\n";
      }
      parent::render();
    }
    
    # create user associated with this page (user page is not created)
    # change_password() must be called too or else noone can login as this user
    function create_user() {
      $this->db->exec_statement('insert into '.MW_DB_TABLE_USERS.' ('.
        MW_DB_TABLE_USERS_COLUMN_NAME. ') values (?)',
        $this->related_user);
    }
    
    # delete user associated with this page (user page is not deleted)
    function delete_user() {
      $this->db->exec_statement('delete from '.MW_DB_TABLE_USERS.' where '.MW_DB_TABLE_USERS_COLUMN_NAME.'=?', $this->related_user);
    }
    
    # change password for associated user
    # pass: new password
    function change_password($pass) {
      $md5_pass = md5($pass);
      $this->db->exec_statement('update '.MW_DB_TABLE_USERS.' set '.
        MW_DB_TABLE_USERS_COLUMN_PASSWORD.'=? where '.MW_DB_TABLE_USERS_COLUMN_NAME.'=?',
        $md5_pass, $this->related_user);
    }
    
    # returns true if given password is valid for associated user
    # pass: password
    function is_password_valid($pass) {
      $md5_pass = md5($pass);
      $query = $this->db->open_query('select count(*) from '.MW_DB_TABLE_USERS.' where '.
        MW_DB_TABLE_USERS_COLUMN_NAME.'=? and '.
        MW_DB_TABLE_USERS_COLUMN_PASSWORD.'=?',
        $this->related_user, $md5_pass);
      $result = $this->db->fetch_query_result($query);
      $is_valid = ($result[0] == 1);
      $this->db->close_query($query);
      return $is_valid;
    }
    
  }

?>
