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

  require_once('registry.php');
  require_once('extensions.php');
  require_once('exporting.php');
  require_once('importing.php');
  require_once('rendering.php');
  require_once('pages.php');
  require_once('storage.php');
  require_once('auth.php');
  require_once('request.php');
  require_once('installation.php');
  require_once('wiki_functions.php');

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
  * Initialize miniWiki infrastructure.
  * <p>
  * Will load and initialize extensions.
  */
  function miniwiki_boot() {
    load_extensions(realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."ext"), true);
    initialize_extensions();
  }

?>
