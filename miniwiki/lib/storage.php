<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for resources and storages
  */

  require_once('registry.php');

  /** HEAD pseudo-revision name (latest revision will be used when talking to database) */
  define("MW_REVISION_HEAD", "HEAD");
  define("MW_RESOURCE_KEY_NAME", "name");
  define("MW_RESOURCE_KEY_CONTENT", "content");
  define("MW_RESOURCE_KEY_CONTENT_LENGTH", "length");
  define("MW_RESOURCE_KEY_LAST_MODIFIED", "last_modified");
  define("MW_RESOURCE_KEY_MESSAGE", "message");
  define("MW_RESOURCE_KEY_AUTHOR", "author");
  define("MW_RESOURCE_KEY_REVISION", "revision");
  define("MW_RESOURCE_CONTENT_TYPE_NONE", "none");
  define("MW_RESOURCE_CONTENT_TYPE_TEXT", "text");
  define("MW_RESOURCE_CONTENT_TYPE_BINARY", "binary");
  define("MW_RESOURCE_CUSTOM_KEY_TYPE_TEXT", "text:");
  
  define("MW_COMPONENT_ROLE_STORAGE", "MW_Storage");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_STORAGE);
  define("MW_COMPONENT_ROLE_DELAYED_DATASPACE_REGISTRATION", "_delayed_dataspace_registration");
  
  function register_storage(&$storage) {
    global $registry;
    $registry->register($storage, MW_COMPONENT_ROLE_STORAGE);
    $delayed_dataspace_registration = $registry->lookup(MW_COMPONENT_ROLE_DELAYED_DATASPACE_REGISTRATION);
    foreach ($delayed_dataspace_registration as $dataspace_def) {
      $storage->register_dataspace($dataspace_def);
    }
  }
  
  function &get_storage() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_STORAGE);
  }

  function register_dataspace($dataspace_def) {
    $storage =& get_storage();
    if ($storage !== null) {
      $storage->register_dataspace($dataspace_def);
    } else {
      global $registry;
      $registry->register($dataspace_def, MW_COMPONENT_ROLE_DELAYED_DATASPACE_REGISTRATION);
    }
  }

  class MW_Resource {
    var $data = array();
    function get($key) {
      return $this->data[$key];
    }
    function set($key, $value) {
      $this->data[$key] = $value;
    }
  }

  class MW_DataSpaceDefinition {
    var $name;
    var $versioned;
    var $content_type;
    var $custom_keys;

    function MW_DataSpaceDefinition($name, $versioned, $content_type) {
      $this->name = $name;
      $this->versioned = $versioned;
      $this->content_type = $content_type;
      $this->custom_keys = array();
    }

    function get_name() {
      return $this->name;
    }

    function is_versioned() {
      return $this->versioned;
    }

    function get_content_type() {
      return $this->content_type;
    }

    function get_custom_keys() {
      return $this->custom_keys;
    }

    function add_custom_key($name, $type) {
      $this->custom_keys[$name] = $type;
    }
  }
  
  class MW_Storage {
    # ordered by name
    function get_resource_names($dataspace) {
      die("abstract: get_resource_names");
    }
    
    function exists($dataspace, $name) {
      die("abstract: exists");
    }
    
    function get_resource($dataspace, $name, $revision, $with_data) {
      die("abstract: get_resource");
    }
    
    function delete_resource($dataspace, $name) {
      die("abstract: delete_resource");
    }
    
    function update_resource($dataspace, $resource) {
      die("abstract: update_resource");
    }
    
    # for versioned resources one can use update_resource() too
    function create_resource($dataspace, $resource) {
      die("abstract: create_resource");
    }
    
    /** ordered by revision from last to first */
    function get_resource_history($dataspace, $name, $with_data) {
      die("abstract: get_resource_history");
    }

    function register_dataspace($dataspace_def) {
      die("abstract: register_dataspace");
    }

    function get_dataspace_names() {
      die("abstract: get_dataspace_names");
    }

    function get_dataspace_definition($dataspace) {
      die("abstract: get_dataspace_definition");
    }
  }

  /**
  * returns last modified value as UNIX timestamp (see mktime())
  * @param val last modified value (as loaded from database)
  */
  function last_modified_as_timestamp($val) {
    # detect whether we have MySQL's "INTERNAL" or "ISO" (or similar) timestamp format - default changed in MySQL 4.1.x
    if (strlen($val) == 14) {
      $year = substr($val, 0, 4);
      $month = substr($val, 4, 2);
      $day = substr($val, 6, 2);
      $hour = substr($val, 8, 2);
      $min = substr($val, 10, 2);
      $sec = substr($val, 12, 2);
    } else {
      $year = substr($val, 0, 4);
      $month = substr($val, 5, 2);
      $day = substr($val, 8, 2);
      $hour = substr($val, 11, 2);
      $min = substr($val, 14, 2);
      $sec = substr($val, 17, 2);
    }
    return mktime($hour, $min, $sec, $month, $day, $year);
  }

  /**
  * returns last modified value returned as YEAR/MONTH/DAY HOUR:MIN:SEC
  * @param val last modified value (as loaded from database)
  */
  function format_last_modified($val) {
    $ts = last_modified_as_timestamp($val);
    /** @todo configurable */
    return strftime("%Y/%m/%d %H:%M:%S", $ts);
  }
  
  /**
  * returns current date and time as last modified value
  */
  function now_as_last_modified() {
    return strftime("%Y%m%d%H%M%S");
  }
  
?>
