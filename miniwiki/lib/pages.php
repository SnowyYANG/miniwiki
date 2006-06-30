<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for Wiki pages
  */

  require_once('registry.php');
  require_once('actions.php');

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
    /** time of last modification (MW_DateTime) - valid after load() */
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
      $this->last_modified = null;
      $this->message = '';
      $this->user = '';
      $this->title = '';
      $this->raw_content_length = null;
    }
    
    /**
    * [override, returns false] returns true if this page supports given action
    * @param action MW_Action
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
      if (!is_default_action(get_action($action))) {
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
      $this->last_modified = now_as_datetime();
    }

    function has_action($action) {
      switch ($action->get_name()) {
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
  /** upload action */
  define("MW_ACTION_UPLOAD", "upload");

  class MW_PageAction extends MW_Action {
  
    function is_valid() {
      $page =& get_current_page();
      return $page->has_action($this);
    }
    
  }
  
  class MW_ViewAction extends MW_PageAction {

    /** @private */
    var $name;

    function MW_ViewAction($name) {
      $this->name = $name;
    }
    
    function get_name() {
      return $this->name;
    }
  
    function &handle() {
      $page =& get_current_page();
      if ($page->load()) {
        include(($this->get_name() == MW_ACTION_VIEW_SOURCE) ? 'viewsource.php' : 'viewpage.php');
        $p = null;
        return $p;
      } else if (is_a($page, 'MW_SpecialUploadPage') && $page->is_data_page) {
        # missing data page should raise 404 Not Found
        header ("HTTP/1.0 404 Not Found");
      }
      # fallback to edit if page does not exist
      return get_action(MW_ACTION_EDIT);
    }

  }

  register_action(new MW_ViewAction(MW_ACTION_VIEW));
  register_action(new MW_ViewAction(MW_ACTION_VIEW_SOURCE));
  register_default_action(new MW_ViewAction(MW_ACTION_VIEW));

  class MW_EditAction extends MW_PageAction {
  
    function get_name() {
      return MW_ACTION_EDIT;
    }
  
    function &handle() {
      $page =& get_current_page();
      # prevent double-load or preview overwriting
      if (!$page->has_content) {
        $page->load();
      }
      if (is_a($page, 'MW_SpecialUploadPage')) {
        include('editupload.php');
      } else {
        include('editpage.php');
      }
      return null_ref();
    }
    
  }

  register_action(new MW_EditAction());

  class MW_DeleteAction extends MW_PageAction {
  
    function get_name() {
      return MW_ACTION_DELETE;
    }
  
    function &handle() {
      $page =& get_current_page();
      $page->delete();
      add_info_text(_("Page deleted."));
      return get_default_action();
    }
    
  }

  register_action(new MW_DeleteAction());

  class MW_HistoryAction extends MW_PageAction {
  
    function get_name() {
      return MW_ACTION_HISTORY;
    }
  
    function &handle() {
      include('history.php');
      return null_ref();
    }
    
  }

  register_action(new MW_HistoryAction());

  class MW_UpdateAction extends MW_PageAction {
  
    function get_name() {
      return MW_ACTION_UPDATE;
    }
  
    function &handle() {
      $req =& get_request("MW_UpdateRequest");
      $page =& get_current_page();
      if ($req->is_preview()) {
        $page->update_for_preview($req->get_content());
        return get_action(MW_ACTION_EDIT);
      } else {
        $changed = $page->update($req->get_content(), $req->get_message());
        add_info_text($changed ? _("Page updated.") : _("No edits. Page was not updated."));
        return get_default_action();
      }
    }
    
  }

  register_action(new MW_UpdateAction());
  
  class MW_UploadAction extends MW_PageAction {
  
    function get_name() {
      return MW_ACTION_UPLOAD;
    }
  
    function &handle() {
      $req =& get_request("MW_UpdateRequest");
      $page =& get_current_page();
      if (is_a($page, 'MW_SpecialUploadsPage')) {
        $page = $page->upload($req->get_content(), $req->get_message(), $req->get_destname());
        set_current_page($page);
      } else {
        $page->update($req->get_content(), $req->get_message());
      }
      add_info_text(_("File uploaded."));
      return get_default_action();
    }
    
  }

  register_action(new MW_UploadAction());

  /** page name request variable */
  define("MW_REQVAR_PAGE_NAME", "page_name");
  /** page revision request variable */
  define("MW_REQVAR_REVISION", "revision");
  
  class MW_PageRequest extends MW_Request {
    /** @private */
    var $page;

    function MW_PageRequest($http_request) {
      $path_info = $http_request->get_path_info();
      $page_name = MW_DEFAULT_PAGE_NAME;
      if ($path_info !== null) {
        $page_name = $path_info;
      } elseif ($http_request->has_param(MW_REQVAR_PAGE_NAME)) {
        $page_name = $http_request->get_param(MW_REQVAR_PAGE_NAME);
      }
      $page_name = filter_page_name(decode_page_name($page_name));
      $revision = $http_request->get_param(MW_REQVAR_REVISION, MW_REVISION_HEAD);
      $this->page = new_page($page_name, $revision);
    }
  
    function get_page() {
      return $this->page;
    }
    
  }

  /** page content request variable (for update action) */
  define("MW_REQVAR_CONTENT", "content");
  /** update message (for update action) */
  define("MW_REQVAR_MESSAGE", "message");
  /** preview submit (for update action) */
  define("MW_REQVAR_PREVIEW", "preview");
  /** source file (for upload action) */
  define("MW_REQVAR_SOURCEFILE", "sourcefile");
  /** destination file (for upload action) */
  define("MW_REQVAR_DESTFILE", "destfile");
  
  class MW_UpdateRequest extends MW_Request {
    /** @private */
    var $content;
    /** @private */
    var $message;
    /** @private */
    var $preview;
    /** @private */
    var $sourcefile;
    /** @private */
    var $destname;

    function MW_UpdateRequest($http_request) {
      $this->content = $http_request->get_param(MW_REQVAR_CONTENT);
      $this->message = $http_request->get_param(MW_REQVAR_MESSAGE);
      $this->preview = $http_request->has_param(MW_REQVAR_PREVIEW);
      $this->sourcefile = $http_request->get_file(MW_REQVAR_SOURCEFILE);
      $this->destname = $http_request->get_param(MW_REQVAR_DESTFILE);
      if (($this->sourcefile !== null) && ($this->destname === null)) {
        $this->destname = $this->sourcefile['name'];
      }
    }

    function get_content() {
      if ($this->sourcefile !== null) {
        $file = $this->sourcefile['tmp_name'];
        $this->content = file_get_contents($file);
        unlink($file);
      }
      return $this->content;
    }

    function get_message() {
      return $this->message;
    }

    function is_preview() {
      return $this->preview;
    }

    function get_destname() {
      return $this->destname;
    }
  
  }

?>
