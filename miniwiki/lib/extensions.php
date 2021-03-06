<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for extensions
  */

  require_once('registry.php');

  class MW_Extension {
    function get_name() {
      die("abstract: get_name");
    }
    function get_version() {
      return null;
    }
    function get_author() {
      return null;
    }
    function get_link() {
      return null;
    }
    function get_description() {
      return null;
    }
    function initialize() {
      return true;
    }
  }

  define("MW_COMPONENT_ROLE_EXTENSION", "MW_Extension");
  
  function register_extension($extension) {
    global $registry;
    if (config('enabled_'.get_class($extension))) {
      debug("Enabling extension ".$extension->get_name());
      $registry->register($extension, MW_COMPONENT_ROLE_EXTENSION);
      return;
    }
    debug("Disabling extension ".$extension->get_name());
  }

  function get_extensions() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_EXTENSION);
  }

  function load_extensions($path, $recurse) {
    $d = dir($path);
    while (false !== ($entry = $d->read())) {
      if (($entry == '.') || ($entry == '..')) {
        continue;
      }
      $f = $path.DIRECTORY_SEPARATOR.$entry;
      if ($recurse && is_dir($f)) {
        load_extensions($f, false);
      }
       if ((is_file($f) || is_link($f)) && preg_match('/\.ext\.php$/i', $entry)) {
        include($f);
        debug("Loaded extension: $f");
      }
    }
    $d->close();
  }

  function initialize_extensions() {
    global $registry;
    $extensions =& $registry->lookup(MW_COMPONENT_ROLE_EXTENSION);
    foreach ($extensions as $ext) {
      if (!$ext->initialize()) {
        die("Extension " . $ext->get_name() . " failed to initalize");
      }
      debug("Initialized extension: ".$ext->get_name());
    }
  }

?>
