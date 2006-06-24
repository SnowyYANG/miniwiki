<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for components
  */

  class MW_ComponentRegistry {

    function register(&$component, $role, $selector = null) {
      die("abstract: register");
    }

    function &lookup($role, $selector = null) {
      die("abstract: lookup");
    }
    
  }

  class MW_SingletonComponentRegistry extends MW_ComponentRegistry {

    var $component = null;
  
    function register(&$component, $role, $selector = null) {
      if ($this->component !== null) {
        trigger_error("Component for role $role is already registered as ".$this->component.", ignoring $component ", E_USER_ERROR);
      } else {
        $this->component = $component;
      }
    }

    function &lookup($role, $selector = null) {
      return $this->component;
    }
    
  }

  class MW_UniqueComponentRegistry extends MW_ComponentRegistry {

    /** [selector -> component] */
    var $components = array();
  
    function register(&$component, $role, $selector = null) {
      if ($selector === null) {
        array_push($this->components, $component);
      } elseif (isset($this->components[$selector])) {
        trigger_error("Component for role $role is already registered as ".$this->components[$selector].", ignoring $component ", E_USER_ERROR);
      } else {
        $this->components[$selector] = $component;
      }
    }

    function &lookup($role, $selector = null) {
      if ($selector === null) {
        return $this->components;
      }
      if (isset($this->components[$selector])) {
        return $this->components[$selector];
      }
      return null;
    }
    
  }

  class MW_DelegatingComponentRegistry extends MW_ComponentRegistry {

    /** [role -> MW_ComponentRegistry] */
    var $role_registries = array(
    );

    /** @private */
    function &get_registry($role) {
      if (!isset($this->role_registries[$role])) {
        $this->role_registries[$role] = new MW_UniqueComponentRegistry();
      }
      return $this->role_registries[$role];
    }
  
    function register(&$component, $role, $selector = null) {
      $registry =& $this->get_registry($role);
      $registry->register($component, $role, $selector);
    }

    function &lookup($role, $selector = null) {
      $registry =& $this->get_registry($role);
      return $registry->lookup($role, $selector);
    }
  }

  $registry = new MW_DelegatingComponentRegistry();
  
?>
