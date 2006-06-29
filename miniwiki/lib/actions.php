<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for actions
  */

  require_once('registry.php');
  
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

?>
