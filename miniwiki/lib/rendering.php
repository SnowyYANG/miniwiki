<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for Wiki rendering
  */

  require_once('registry.php');
  
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
    $req =& get_request();
    $auth =& get_auth();
    $vars = new MW_Variables(null);
    $vars->set('version', MW_VERSION);
    $vars->set('user', ($auth->is_logged ? $auth->user : ''));
    $vars->set('main_page', MW_PAGE_NAME_MAIN);
    $vars->set('req_action', $req->action);
    $vars->set('self_link_dir', $_SERVER['SCRIPT_NAME'].'/../');
    return $vars;
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
        if ($page->last_modified !== null) {
          $this->wiki_variables->set('last_modified', format_last_modified($page->last_modified));
        }
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

?>
