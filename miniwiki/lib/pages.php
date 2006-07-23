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
  define("MW_COMPONENT_ROLE_REDIRECTED_PAGE", "MW_Page:redirected_page");
  $registry->add_registry(new MW_SingletonComponentRegistry(true), MW_COMPONENT_ROLE_REDIRECTED_PAGE);
  define("MW_COMPONENT_ROLE_SPECIAL_PAGE", "_special_page");

  function set_current_page(&$page) {
    global $registry;
    $registry->register($page, MW_COMPONENT_ROLE_PAGE);
  }
  
  function &get_current_page() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_PAGE);
  }
  
  function set_redirected_page(&$page) {
    global $registry;
    $registry->register($page, MW_COMPONENT_ROLE_REDIRECTED_PAGE);
  }
  
  function &get_redirected_page() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_REDIRECTED_PAGE);
  }
  
  function register_special_page($name) {
    global $registry;
    $registry->register($name, MW_COMPONENT_ROLE_SPECIAL_PAGE);
  }

  function get_special_pages() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_SPECIAL_PAGE);
  }
  
  /** main page name */
  define("MW_PAGE_NAME_MAIN", "Main Page");
  /** default page name (if none requested) */
  define("MW_DEFAULT_PAGE_NAME", MW_PAGE_NAME_MAIN);
  
  define("MW_PAGE_TAG_USER", "user");
  define("MW_PAGE_TAG_UPLOAD", "upload");
  define("MW_PAGE_TAG_SPECIAL", "special");
  
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

  function new_special_page($name) {
    return new_page_with_tag(MW_PAGE_TAG_SPECIAL, $name, MW_REVISION_HEAD);
  }

  function load_special_page($name) {
    $special_page = new_special_page($name);
    if (!$special_page->load()) {
      trigger_error(_("Required special page %0% is missing", $special_page->name), E_USER_ERROR);
      return null;
    }
    return $special_page;
  }

  define("MW_PAGE_ATTR_TITLE", "title");
  
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
    /** page attributes */
    var $attrs;

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
      $this->attrs = array();
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
      $this->attrs[MW_PAGE_ATTR_TITLE] = $this->title;
      return false;
    }

    function get_attr($name) {
      return (isset($this->attrs[$name]) ? $this->attrs[$name] : null);
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

    function get_wiki_content() {
      return $this->raw_content;
    }

    /** render page (with revision) content (must be loaded first) to output */
    function render($vars = null) {
      $renderer =& get_renderer();
      $page =& get_current_page();
      if ($page == null) {
        $page = $this;
      }
      $renderer->render($page, $this->get_wiki_content(), $vars, $this);
    }
    
    /**
    * [override, returns empty array] returns array of MW_Page instances representing all revisions including current one
    * returned array is ordered by revision in descending order (HEAD first)
    */
    function get_all_revisions() {
      return array();
    }

    /** @returns null or page this page is redirected to */
    function get_redirected_page() {
      return null;
    }

    /** this page will still point to the old name */
    function rename($new_name, $with_redirect = true) {
      if (empty($new_name) || ($this->name === $new_name)) {
        return false;
      }
      $success = $this->_rename($new_name);
      if ($success && $with_redirect) {
        $this->update("#REDIRECT $new_name\n", _("Renamed to ".$new_name));
      }
      return $success;
    }

    /** @protected */
    function _rename($new_name) {
      return false;
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
        case MW_ACTION_RENAME;
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
    
    function get_wiki_content() {
      # simple and safe
      return "[[".$this->name."]]";
    }
    
  }

  /** view page action (renders Wiki page) */
  define("MW_ACTION_VIEW", "view");
  /** view page source action (shows Wiki markup) */
  define("MW_ACTION_VIEW_SOURCE", "view_source");
  /** edit page action (shows Wiki editor, optionally with preview or really changes a file or page or uploads new file) */
  define("MW_ACTION_EDIT", "edit");
  /** delete page action (really deletes page) */
  define("MW_ACTION_DELETE", "delete");
  /** show history action (shows history page) */
  define("MW_ACTION_HISTORY", "history");
  /** rename action */
  define("MW_ACTION_RENAME", "rename");

  class MW_PageAction extends MW_Action {
  
    function is_valid() {
      $page =& get_current_page();
      return $page->has_action($this);
    }
    
    /** @protected */
    function _link() {
      return new MW_PageLink();
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
        if ($this->get_name() == MW_ACTION_VIEW_SOURCE) {
          include('viewsource.php');
        } else {
          $req =& get_request("MW_ViewRequest");
          if ($req->get_redirect()) {
            $redirected = array($page->name);
            $redir_page = $page->get_redirected_page();
            while (($redir_page !== null) && $redir_page->load()) {
              # those "labels" instead of "references" are real pain...
              $_redir_page = $redir_page;
              set_redirected_page($page);
              set_current_page($_redir_page);
              $redir_page = $redir_page->get_redirected_page();
              if (($redir_page === null) || in_array($redir_page->name, $redirected)) {
                break;
              }
              array_unshift($redirected, $redir_page->name);
            }
          }
          include('viewpage.php');
        }
        $p = null;
        return $p;
      } else if (is_a($page, 'MW_SpecialUploadPage') && $page->is_data_page) {
        # missing data page should raise 404 Not Found
        header ("HTTP/1.0 404 Not Found");
      }
      # fallback to edit if page does not exist
      return get_action(MW_ACTION_EDIT);
    }

    /** @protected */
    function _link() {
      return new MW_ViewPageLink();
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
      $req =& get_request("MW_EditRequest");
      if ($req->is_update()) {
        # prevent infinite loop
        $req->clear_update();
        if (is_a($page, 'MW_SpecialUploadsPage')) {
          $uploaded = $page->upload($req->get_content(), $req->get_message(), $req->get_destname());
          set_current_page($uploaded);
          add_info_text(_("File uploaded."));
          return get_default_action();
        } elseif ($req->is_preview()) {
          $page->update_for_preview($req->get_content());
          # falls through to plain edit
        } else {
          $changed = $page->update($req->get_content(), $req->get_message());
          if (is_a($page, 'MW_SpecialUploadPage')) {
            add_info_text($changed ? _("File uploaded.") : _("No changes. File was not uploaded."));
          } else {
            add_info_text($changed ? _("Page updated.") : _("No edits. Page was not updated."));
          }
          return get_default_action();
        }
      }
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
    
    /** @protected */
    function _link() {
      return new MW_EditPageLink();
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

  define("MW_SPECIAL_PAGE_RENAME", "Rename");

  class MW_RenameAction extends MW_PageAction {
  
    function get_name() {
      return MW_ACTION_RENAME;
    }
  
    function &handle() {
      $req =& get_request("MW_RenameRequest");
      $page =& get_current_page();
      $new_name = $req->get_new_name();
      if ($new_name === null) {
        $special_page = load_special_page(MW_SPECIAL_PAGE_RENAME);
        if ($special_page !== null) {
          render_ui(MW_LAYOUT_HEADER, _("Renaming %0%", $page->name));
          $special_page->render();
          render_ui(MW_LAYOUT_FOOTER);
          return null_ref();
        }
      } else {
        $success = $page->rename($new_name);
        add_info_text($success ? _("Page renamed.") : _("Page not renamed."));
        # will not redirect itself
        if ($success && is_a($page, "MW_SpecialUploadPage")) {
          $new_page = new_upload_page($new_name, MW_REVISION_HEAD);
          set_current_page($new_page);
        }
      }
      return get_default_action();
    }
    
    function is_permitted() {
      $auth =& get_auth();
      $page =& get_current_page();
      $req =& get_request("MW_RenameRequest");
      $new_name = $req->get_new_name();
      if ($new_name !== null) {
        $new_page = new_page($new_name, MW_REVISION_HEAD);
        if (!$auth->is_action_permitted($this, $new_page)) {
          return false;
        }
      }
      return parent::is_permitted();
    }
    
  }

  register_action(new MW_RenameAction());

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

  /** page redirection variable */
  define("MW_REQVAR_REDIRECT", "redirect");
  define("MW_NO_REDIRECT_VALUE", "no");
  
  class MW_ViewRequest extends MW_Request {
    /** @private */
    var $redirect;

    function MW_ViewRequest($http_request) {
      $this->redirect = ($http_request->get_param(MW_REQVAR_REDIRECT) !== MW_NO_REDIRECT_VALUE);
    }
  
    function get_redirect() {
      return $this->redirect;
    }
    
  }

  /** page content request variable (for edit action) */
  define("MW_REQVAR_CONTENT", "content");
  /** update message (for edit action) */
  define("MW_REQVAR_MESSAGE", "message");
  /** preview submit (for edit action) */
  define("MW_REQVAR_PREVIEW", "preview");
  /** source file (for edit action) */
  define("MW_REQVAR_SOURCEFILE", "sourcefile");
  /** destination file (for edit action) */
  define("MW_REQVAR_DESTFILE", "destfile");
  
  class MW_EditRequest extends MW_Request {
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
    /** @private */
    var $is_update;

    function MW_EditRequest($http_request) {
      $this->content = $http_request->get_param(MW_REQVAR_CONTENT);
      $this->message = $http_request->get_param(MW_REQVAR_MESSAGE);
      $this->preview = $http_request->has_param(MW_REQVAR_PREVIEW);
      $this->sourcefile = $http_request->get_file(MW_REQVAR_SOURCEFILE);
      $this->destname = $http_request->get_param(MW_REQVAR_DESTFILE);
      if (($this->sourcefile !== null) && empty($this->destname)) {
        $this->destname = $this->sourcefile['name'];
      }
      $this->is_update = $http_request->has_param(MW_REQVAR_CONTENT) || ($this->sourcefile !== null);
    }

    function is_update() {
      return $this->is_update;
    }

    function clear_update() {
      $this->is_update = false;
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

  define("MW_REQVAR_NEW_NAME", "new_name");
  
  class MW_RenameRequest extends MW_Request {
    /** @private */
    var $new_name;

    function MW_RenameRequest($http_request) {
      $this->new_name = $http_request->get_param(MW_REQVAR_NEW_NAME);
      if ($this->new_name !== null) {
        $this->new_name = filter_page_name(decode_page_name($this->new_name));
      }
    }
  
    function get_new_name() {
      return $this->new_name;
    }
    
  }

  class MW_PageLink extends MW_ActionLink {

    function MW_PageLink() {
      parent::MW_ActionLink();
      $this->set_page(get_current_page());
    }

    function set_page($page) {
      $this->set_page_name($page->name);
      $this->set_revision($page->revision);
    }

    function get_page_name_param_name() {
      return MW_REQVAR_PAGE_NAME;
    }

    function set_page_name($page_name) {
      $this->set_path_info(encode_page_name($page_name));
    }

    function get_revision_param_name() {
      return MW_REQVAR_REVISION;
    }

    function set_revision($revision) {
      if ($revision !== MW_REVISION_HEAD) {
        $this->set_param(MW_REQVAR_REVISION, $revision);
      } else {
        $this->unset_param(MW_REQVAR_REVISION);
      }
    }
  
  }

  class MW_EditPageLink extends MW_PageLink {

    function get_content_param_name() {
      return MW_REQVAR_CONTENT;
    }

    function set_content($content) {
      $this->set_param(MW_REQVAR_CONTENT, $content);
    }
  
    function get_message_param_name() {
      return MW_REQVAR_MESSAGE;
    }
  
    function set_message($message) {
      $this->set_param(MW_REQVAR_MESSAGE, $message);
    }
  
    function get_preview_param_name() {
      return MW_REQVAR_PREVIEW;
    }
  
    function set_preview($preview) {
      $this->set_param(MW_REQVAR_PREVIEW, $preview);
    }
  
    function get_sourcefile_param_name() {
      return MW_REQVAR_SOURCEFILE;
    }
  
    function set_sourcefile($sourcefile) {
      $this->set_param(MW_REQVAR_SOURCEFILE, $sourcefile);
    }
  
    function get_destfile_param_name() {
      return MW_REQVAR_DESTFILE;
    }
  
    function set_destfile($destfile) {
      $this->set_param(MW_REQVAR_DESTFILE, $destfile);
    }
  
  }

  class MW_ViewPageLink extends MW_PageLink {

    function get_redirect_param_name() {
      return MW_REQVAR_REDIRECT;
    }

    function set_redirect($redirect) {
      if ($redirect) {
        $this->unset_param(MW_REQVAR_REDIRECT);
      } else {
        $this->set_param(MW_REQVAR_REDIRECT, MW_NO_REDIRECT_VALUE);
      }
    }
    
  }

  function link_for_page_action($page, $action_name) {
    $action = get_action($action_name);
    if ($action === null) {
      $action = get_default_action();
    }
    $link = $action->link();
    $link->set_page($page);
    return $link;
  }

  function url_for_page_action($page, $action_name, $in_attr = false, $fragment = null) {
    $link = link_for_page_action($page, $action_name);
    $link->set_page($page);
    if (!empty($fragment)) {
      $link->set_fragment($fragment);
    }
    return $link->to_url($in_attr);
  }

?>
