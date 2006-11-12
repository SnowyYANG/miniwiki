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
    var $dataspace;
    var $data = array();
    
    function MW_Resource($dataspace) {
      $this->dataspace = $dataspace;
    }
    
    function get($key) {
      return (isset($this->data[$key]) ? $this->data[$key] : null);
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
    # ordered by name, namespace of form XXX/YYY, empty string means pages without namespace, null all pages
    function get_resource_names($dataspace, $namespace = null) {
      die("abstract: get_resource_names");
    }

    # ordered by name, will return only namespaces directly under given namespace (if any)
    function get_namespaces($dataspace, $namespace = null) {
      die("abstract: get_namespaces");
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

    function rename_resource($dataspace, $old_name, $new_name) {
      die("abstract: rename_resource");
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

  define("MW_COMPONENT_ROLE_DATETIME", "MW_DateTime");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_DATETIME);

  /** [abstract] */
  class MW_DateTime {

    function as_unix_timestamp() {
      die("abstract: as_unix_timestamp");
    }

    function format_strftime($fmt, $utc = false) {
      $ts = $this->as_unix_timestamp();
      return ($utc ? gmstrftime($fmt, $ts) : strftime($fmt, $ts));
    }

    function format_php($fmt, $utc = false) {
      $ts = $this->as_unix_timestamp();
      return ($utc ? gmdate($fmt, $ts) : date($fmt, $ts));
    }

    /** [static] */
    function &from_unix_timestamp($ts) {
      die("abstract: from_unix_timestamp");
    }
    
  }

  function register_datetime_class($class) {
    global $registry;
    $registry->register($class, MW_COMPONENT_ROLE_DATETIME);
  }
  
  function create_datetime_from_unix_timestamp($ts) {
    global $registry;
    $datetime_class = $registry->lookup(MW_COMPONENT_ROLE_DATETIME);
    return call_user_func(array($datetime_class, 'from_unix_timestamp'), $ts);
  }

  function now_as_datetime() {
    $ts = mktime();
    $dt = create_datetime_from_unix_timestamp($ts);
    return $dt;
  }

?>
