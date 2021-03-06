<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for actions
  */

  require_once('registry.php');
  require_once('request.php');
  require_once('links.php');
  
  define("MW_COMPONENT_ROLE_ACTION", "MW_Action");
  define("MW_COMPONENT_ROLE_DEFAULT_ACTION", "_default_action");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_DEFAULT_ACTION);

  class MW_Action {

    function get_name() {
      die("abstract: get_name");
    }

    function &handle() {
      die("abstract: handle");
    }

    function is_valid() {
      die("abstract: is_valid");
    }

    function is_permitted() {
      $auth =& get_auth();
      $page =& get_current_page();
      return $auth->is_action_permitted($this, $page);
    }

    function link() {
      $link = $this->_link();
      $link->set_action($this);
      return $link;
    }

    /** @protected */
    function _link() {
      return new MW_ActionLink();
    }
    
  }

  function register_action(&$action) {
    global $registry;
    $registry->register($action, MW_COMPONENT_ROLE_ACTION, $action->get_name());
  }

  function &get_action($name) {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_ACTION, $name);
  }

  function register_default_action(&$action) {
    global $registry;
    $registry->register($action, MW_COMPONENT_ROLE_DEFAULT_ACTION);
  }

  function &get_default_action() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_DEFAULT_ACTION);
  }

  function is_default_action($action) {
    $def_action =& get_default_action();
    return (strcmp($def_action->get_name(), $action->get_name()) === 0);
  }

  /** action request variable */
  define("MW_REQVAR_ACTION", "action");
  
  class MW_ActionRequest extends MW_Request {
    /** @private */
    var $action;

    function MW_ActionRequest($http_request) {
      $name = $http_request->get_param(MW_REQVAR_ACTION);
      $this->action = ($name !== null) ? get_action($name) : get_default_action();
    }
  
    function get_action() {
      return $this->action;
    }
    
  }
  
  class MW_ActionLink extends MW_Link {

    function get_action_param_name() {
      return MW_REQVAR_ACTION;
    }

    function set_action($action) {
      if (!is_default_action($action)) {
        $this->set_param(MW_REQVAR_ACTION, $action->get_name());
      } else {
        $this->unset_param(MW_REQVAR_ACTION);
      }
    }
  
  }

  function url_for_action($action_name, $in_attr = false, $fragment = null) {
    $action = get_action($action_name);
    if ($action === null) {
      $action = get_default_action();
    }
    $link = $action->link();
    if ($fragment !== null) {
      $link->set_fragment($fragment);
    }
    return $link->to_url($in_attr);
  }

?>
