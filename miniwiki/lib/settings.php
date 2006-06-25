<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY
  
  /** @file
  * support for user settings
  */

  define("MW_GLOBAL_SETTINGS_VARIABLE_PREFIX", "mw_");
  
  define("MW_COMPONENT_ROLE_SETTINGS", "MW_Settings");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_SETTINGS);
  $registry->register(new MW_Settings(), MW_COMPONENT_ROLE_SETTINGS);

  class MW_Settings {

    var $defaults = array();
    var $settings = array();

    function set_default($name, $value) {
      $defaults[$name] = $value;
    }

    function set($name, $value) {
      $settings[$name] = $value;
    }

    function get($name) {
      if (isset($settings[$name])) {
        debug("MW_Settings: $name set from inside");
        return $settings[$name];
      } elseif (isset($GLOBALS[MW_GLOBAL_SETTINGS_VARIABLE_PREFIX.$name])) {
        debug("MW_Settings: $name set from outside");
        return $GLOBALS[MW_GLOBAL_SETTINGS_VARIABLE_PREFIX.$name];
      } elseif (isset($defaults[$name])) {
        debug("MW_Settings: $name from defaults");
        return $defaults[$name];
      }
      debug("MW_Settings: $name not found");
      return null;
    }
  
  }

  function config($name) {
    global $registry;
    $settings = $registry->lookup(MW_COMPONENT_ROLE_SETTINGS);
    return $settings->get($name);
  }

  function set_default_config($name, $value) {
    global $registry;
    $settings = $registry->lookup(MW_COMPONENT_ROLE_SETTINGS);
    $settings->set_default($name, $value);
  }
  
  set_default_config('encoding', 'utf-8');
  
?>
