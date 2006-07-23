<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for HTTP requests
  */

  require_once('registry.php');
  
  define("MW_COMPONENT_ROLE_HTTP_REQUEST", "MW_HTTPRequest");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_HTTP_REQUEST);
  $registry->register(new MW_HTTPRequest(), MW_COMPONENT_ROLE_HTTP_REQUEST);
  define("MW_COMPONENT_ROLE_REQUEST", "MW_Request");

  class MW_Request {

    function MW_Request($http_request) {
      die("abstract: constructor");
    }
  
  }
  
  /**
  * returns instance of MW_Request of given type
  */
  function &get_request($type) {
    global $registry;
    $req =& $registry->lookup(MW_COMPONENT_ROLE_REQUEST, $type);
    if ($req === null) {
      $raw_request =& $registry->lookup(MW_COMPONENT_ROLE_HTTP_REQUEST);
      $req =& new $type($raw_request);
      $registry->register($req, MW_COMPONENT_ROLE_REQUEST, $type);
    }
    return $req;
  }

  class MW_BasicRequest extends MW_Request {
    /** @private */
    var $is_head;

    function MW_BasicRequest($http_request) {
      $this->is_head = (strcmp($http_request->get_method(), "HEAD") === 0);
    }
  
    function is_head() {
      return $this->is_head;
    }
    
  }

  class MW_RawRequest extends MW_Request {
    /** @private */
    var $http_request;

    function MW_RawRequest($http_request) {
      $this->http_request = $http_request;
    }
  
    function get_raw_param($name, $default = null) {
      return $this->http_request->get_param($name, $default);
    }
    
  }

  /**
  * HTTP request class
  */
  class MW_HTTPRequest {

    /** @private */
    var $method;
    /** @private */
    var $path_info;
    /** @private */
    var $params;
    /** @private */
    var $files;

    function get_method() {
      return $this->method;
    }
  
    function get_param($name, $default = null) {
      if ($this->has_param($name)) {
        return $this->params[$name];
      }
      return $default;
    }

    function has_param($name) {
      return isset($this->params[$name]);
    }

    function get_path_info() {
      return $this->path_info;
    }

    function get_file($name) {
      if (isset($this->files[$name])) {
        return $this->files[$name];
      }
      return null;
    }

    /** @protected constructor (do not use directly, use get_request()) */
    function MW_HTTPRequest() {

      if (isset($_SERVER["REQUEST_METHOD"])) {
        $this->method = $_SERVER["REQUEST_METHOD"];
      }
      
      $this->path_info = '';
      if (isset($_SERVER['FILEPATH_INFO'])) {
        $this->path_info = $_SERVER['FILEPATH_INFO'];
      } elseif (isset($_SERVER['PATH_INFO'])) {
        $this->path_info = $_SERVER['PATH_INFO'];
      }
      if (strlen(trim($this->path_info)) > 0) {
        $this->path_info = preg_replace('/^\/+/', '', $this->path_info);
      } else {
        $this->path_info = null;
      }
      
      $this->params = $_REQUEST;
      if (get_magic_quotes_gpc()) {
        $this->params = array_map("stripslashes", $this->params);
      }

      $this->files = array();
      foreach ($_FILES as $name => $file) {
        if (!is_uploaded_file($file['tmp_name'])) {
          trigger_error(_('Possible upload attack'), E_USER_ERROR);
        } else {
          $this->files[$name] = $file;
        }
      }
    }
  }

?>
