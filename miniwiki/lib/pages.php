<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for Wiki pages
  */

  require_once('registry.php');

  define("MW_COMPONENT_ROLE_PAGE", "MW_Page");
  $registry->add_registry(new MW_SingletonComponentRegistry(true), MW_COMPONENT_ROLE_PAGE);
  define("MW_COMPONENT_ROLE_PAGE_HANDLER", "MW_PageHandler");
  $registry->add_registry(new MW_PageHandlerComponentRegistry(), MW_COMPONENT_ROLE_PAGE_HANDLER);

  function set_current_page(&$page) {
    global $registry;
    $registry->register($page, MW_COMPONENT_ROLE_PAGE);
  }
  
  function &get_current_page() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_PAGE);
  }
  
  /** main page name */
  define("MW_PAGE_NAME_MAIN", "Main Page");
  /** default page name (if none requested) */
  define("MW_DEFAULT_PAGE_NAME", MW_PAGE_NAME_MAIN);
  
  define("MW_PAGE_TAG_USER", "user");
  define("MW_PAGE_TAG_UPLOAD", "upload");
  
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
  
  function page_handler_cmp($a, $b) {
    return ($a->get_priority()) - ($b->get_priority());
  }
  
  class MW_PageHandlerComponentRegistry extends MW_UniqueComponentRegistry {

    function MW_PageHandlerComponentRegistry() {
      array_push($this->components, new MW_LastPageHandler());
    }
  
    function register(&$component, $role, $selector = null) {
      parent::register($component, $role, $selector);
      $this->initialize_page_handlers();
    }
    
    function unregister(&$component, $role) {
      # page handlers are linked together, breaking the link is too much work,
      # because noone needs it
      die("Unsupported: unregister");
    }

    /** @private */
    function initialize_page_handlers() {
      usort($this->components, "page_handler_cmp");
      $last = null;
      for ($i = 0; $i < sizeof($this->components); $i++) {
        $handler =& $this->components[$i];
        if ($last !== null) {
          $last->next =& $handler;
        }
        $last =& $handler;
      }
      if ($last !== null) {
        $last->next = null;
      }
    }
  
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
  
  function register_page_handler($handler) {
    global $registry;
    $registry->register($handler, MW_COMPONENT_ROLE_PAGE_HANDLER);
  }
  
  function new_page_with_tag($tag, $name, $revision) {
    debug("new_page_with_tag: tag=".$tag. ", name=".$name. ", revision=".$revision);
    $name = filter_page_name($name);
    global $registry;
    $page_handler = $registry->lookup(MW_COMPONENT_ROLE_PAGE_HANDLER, 0);
    if ($page_handler !== null) {
      return $page_handler->get_page($tag, $name, $revision);
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

?>
