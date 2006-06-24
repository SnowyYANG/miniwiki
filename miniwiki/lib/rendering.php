<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for Wiki rendering
  */

  require_once('registry.php');
  
  $renderer_class_name = null;
  
  function register_renderer_class($class_name) {
    global $renderer_class_name;
    if ($renderer_class_name !== null) {
      trigger_error("Renderer class $renderer_class_name already registered, ignoring $class_name ", E_USER_ERROR);
    } else {
      $renderer_class_name = $class_name;
    }
  }
  
  /**
  * returns instance of MW_Renderer
  */
  function new_renderer() {
    global $renderer_class_name;
    return new $renderer_class_name();
  }

  /**
  * returns new MW_Variables
  * @param supervars super MW_Variables to use
  */
  function new_wiki_variables($supervars) {
    return new MW_Variables($supervars);
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

?>
