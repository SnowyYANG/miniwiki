<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * miniWiki library
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
  require_once('messages.php');
  require_once('settings.php');

  /** miniWiki product name */
  define("MW_NAME", "miniWiki");
  /** miniWiki version as X.Y string */
  define("MW_VERSION", "0.3-svn");

  if (!function_exists('stripos')) {
    function stripos($haystack,$needle,$offset = 0) {
      return(strpos(strtolower($haystack),strtolower($needle),$offset));
    }
  }

  /** users list special page name */
  define("MW_PAGE_NAME_USERS", "Special:Users");
  /** pages list special page name */
  define("MW_PAGE_NAME_PAGES", "Special:Pages");
  /** user page name prefix */
  define("MW_PAGE_NAME_PREFIX_USER", "User:");
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

  /**
  * Initialize miniWiki infrastructure.
  * <p>
  * Will load and initialize extensions.
  */
  function miniwiki_boot($install_mode = false) {
    set_default_config('install_mode', $install_mode);
    register_shutdown_function('miniwiki_shutdown');
    load_extensions(realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."ext"), true);
    initialize_extensions();
  }

  define("MW_COMPONENT_ROLE_SHUTDOWN", "_shutdown");

  function register_shutdown_object(&$object) {
    global $registry;
    $registry->register($object, MW_COMPONENT_ROLE_SHUTDOWN);
  }
  
  function unregister_shutdown_object(&$object) {
    global $registry;
    $registry->unregister($object, MW_COMPONENT_ROLE_SHUTDOWN);
  }

  function shutdown_cb(&$component) {
    if (is_object($component) && method_exists($component, 'shutdown')) {
      debug('Calling shutdown on '.get_class($component));
      $component->shutdown();
    }
  }
  
  /**
  * Shutdown miniWiki infrastructure.
  * <p>
  * Will call method shutdown() (if exists) on every object in registry (including shutdown
  * objects).
  */
  function miniwiki_shutdown() {
    global $registry;
    $registry->apply('shutdown_cb');
  }

?>
