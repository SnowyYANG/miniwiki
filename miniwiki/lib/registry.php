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

    function unregister(&$component, $role) {
      die("abstract: unregister");
    }

    function &lookup($role, $selector = null) {
      die("abstract: lookup");
    }
    
    function apply($callback, $role = null, $selector = null) {
      die("abstract: lookup");
    }
    
  }

  class MW_SingletonComponentRegistry extends MW_ComponentRegistry {

    var $allow_overwrites = false;
    var $component = null;

    function MW_SingletonComponentRegistry($allow_overwrites = false) {
      $this->allow_overwrites = $allow_overwrites;
    }
  
    function register(&$component, $role, $selector = null) {
      if (!$this->allow_overwrites && ($this->component !== null)) {
        trigger_error("Component for role $role is already registered as ".to_string($this->component).", ignoring ".to_string($component), E_USER_ERROR);
      } else {
        $this->component =& $component;
      }
    }

    function unregister(&$component, $role) {
      if ($this->component === $component) {
        $this->component = null;
      }
    }
    
    function &lookup($role, $selector = null) {
      return $this->component;
    }
    
    function apply($callback, $role = null, $selector = null) {
      if ($this->component !== null) {
        call_user_func($callback, $this->component);
      }
    }
    
  }

  class MW_UniqueComponentRegistry extends MW_ComponentRegistry {

    /** [selector -> component] */
    var $components = array();
  
    function register(&$component, $role, $selector = null) {
      if ($selector === null) {
        array_push($this->components, $component);
      } elseif (isset($this->components[$selector])) {
        trigger_error("Component for role $role is already registered as ".to_string($this->components[$selector]).", ignoring ".to_string($component), E_USER_ERROR);
      } else {
        $this->components[$selector] =& $component;
      }
    }

    function unregister(&$component, $role) {
      $keys = array_keys($this->components, $component);
      foreach ($keys as $key) {
        unset($this->components[$key]);
      }
    }
    
    function &lookup($role, $selector = null) {
      if ($selector === null) {
        return $this->components;
      }
      if (isset($this->components[$selector])) {
        return $this->components[$selector];
      }
      return null_ref();
    }
    
    function apply($callback, $role = null, $selector = null) {
      if ($selector === null) {
        foreach ($this->components as $component) {
          call_user_func($callback, $component);
        }
      } elseif (isset($this->components[$selector])) {
        call_user_func($callback, $this->components[$selector]);
      }
    }
    
  }

  class MW_DelegatingComponentRegistry extends MW_ComponentRegistry {

    /** [role -> MW_ComponentRegistry] */
    var $role_registries = array();

    /** @private */
    function &get_registry($role) {
      if (!isset($this->role_registries[$role])) {
        $this->role_registries[$role] = new MW_UniqueComponentRegistry();
      }
      return $this->role_registries[$role];
    }

    function add_registry(&$registry, $role) {
      $this->role_registries[$role] =& $registry;
    }
  
    function register(&$component, $role, $selector = null) {
      debug("MW_DelegatingComponentRegistry: register component ".to_string($component)." with role $role and selector '$selector'");
      $registry =& $this->get_registry($role);
      $registry->register($component, $role, $selector);
    }

    function unregister(&$component, $role) {
      $registry =& $this->get_registry($role);
      $registry->unregister($component, $role);
    }

    function &lookup($role, $selector = null) {
      debug("MW_DelegatingComponentRegistry: lookup role $role and selector '$selector'");
      $registry =& $this->get_registry($role);
      return $registry->lookup($role, $selector);
    }

    function apply($callback, $role = null, $selector = null) {
      if ($role === null) {
        foreach ($this->role_registries as $registry) {
          $registry->apply($callback, $role, $selector);
        }
      } elseif (isset($this->role_registries[$role])) {
        $registry = $this->role_registries[$role];
        $registry->apply($callback, $role, $selector);
      }
    }
    
  }

  $registry = new MW_DelegatingComponentRegistry();
  
?>
