<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for Wiki rendering
  */

  require_once('registry.php');
  require_once('settings.php');
  
  define("MW_COMPONENT_ROLE_RENDERER", "MW_Renderer");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_RENDERER);
  
  function register_renderer($renderer) {
    global $registry;
    $registry->register($renderer, MW_COMPONENT_ROLE_RENDERER);
  }
  
  /**
  * returns instance of MW_Renderer
  */
  function &get_renderer() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_RENDERER);
  }

  /**
  * returns new MW_Variables
  * @param supervars super MW_Variables to use
  */
  function new_wiki_variables($supervars) {
    return new MW_Variables($supervars);
  }

  /**
  * returns new MW_Variables with prefilled global values
  */
  function new_global_wiki_variables() {
    $req =& get_request("MW_ActionRequest");
    $auth =& get_auth();
    $vars = new MW_Variables(null);
    $vars->set('wiki_name', config('wiki_name'));
    $vars->set('user', ($auth->is_logged ? $auth->user : ''));
    $vars->set('main_page', MW_PAGE_NAME_MAIN);
    $action = $req->get_action();
    $vars->set('req_action', $action->get_name());
    return $vars;
  }

  set_default_config('datetime_format', "%Y/%m/%d %H:%M:%S");

  /**
  * returns MW_DateTime value formatted for UI
  * @param val MW_DateTime
  */
  function format_datetime($val) {
    return $val->format_strftime(config('datetime_format'));
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
    * @param curpage MW_Page or null for the same value as page
    */
    function MW_RendererState($renderer, $page, $raw, $super_wiki_variables, $curpage = null) {
      $this->renderer = $renderer;
      $this->raw = $raw;
      $this->wiki_variables = new_wiki_variables($super_wiki_variables);
      if ($page !== null) {
        if ($curpage === null) {
          $curpage = $page;
        }
        $this->wiki_variables->set('page', $page->name);
        $this->wiki_variables->set('curpage', $curpage->name);
        $this->wiki_variables->set('revision', $page->revision);
        if ($page->storage_revision !== null) {
          $this->wiki_variables->set('storage_revision', $page->storage_revision);
        }
        if ($page->last_modified !== null) {
          $this->wiki_variables->set('last_modified', format_datetime($page->last_modified));
        }
        $this->wiki_variables->set('has_content', ($page->has_content ? 'true' : ''));
        if ($page->raw_content_length !== null) {
          $this->wiki_variables->set('content_length', $page->raw_content_length);
        }
        if ($page->message !== null) {
          $this->wiki_variables->set('revision_message', $page->message);
        }
        if ($page->user !== null) {
          $this->wiki_variables->set('revision_author', $page->user);
        }
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
    * @param curpage (optional): MW_Page (may be null)
    */
    function render($page, $raw, $vars = null, $curpage = null) {
      die ("abstract: render");
    }
    
  }

  function wiki_include($page, $args = null, $flat_args = false, $as_current = true) {
    $auth =& get_auth();
    if (!$auth->is_action_permitted(get_action(MW_ACTION_VIEW), $page)) {
      return '[['.$page->name.']]';
    }
    $ret = '{{&push_vars}}';
    if ($as_current) {
      $ret .= '{{&set|curpage|'.$page->name.'}}';
    }
    if (($args !== null) && (count($args) > 0)) {
      $args_str = '';
      if ($flat_args) {
        $args_str = '|'.join('|', $args);
      } else {
        foreach ($args as $name => $value) {
          $args_str .= '|'.$name.'|'.$value;
        }
      }
      $ret .= '{{&set'.$args_str .'}}';
    }
    $ret .= str_replace("\r", '', $page->get_wiki_content());
    $ret .= '{{&pop_vars}}';
    return $ret;
  }

?>
