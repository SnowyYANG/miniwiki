<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for links
  */

  require_once('registry.php');
  
  class MW_Link {

    /** @private */
    var $params = array();
    /** @private */
    var $path_info = null;
    /** @private */
    var $link_base;
    /** @private */
    var $fragment = null;

    function MW_Link() {
      $this->unset_link_base();
    }

    function to_url($in_attr = false) {
      $url = $this->_to_url();
      if ($in_attr) {
        $url = htmlspecialchars($url, ENT_QUOTES);
      }
      return $url;
    }

    /** @private */
    function _to_url() {
      $url = $this->link_base;
      if ($this->path_info !== null) {
        $url .= '/' . str_replace(array('%2F', '%2f'), '/', rawurlencode($this->path_info));
      }
      if (sizeof($this->params) > 0) {
        $url .= '?';
        $in_query = false;
        foreach ($this->params as $name => $value) {
          if ($in_query) {
            $url .= '&';
          }
          $url .= $name . '=' . rawurlencode($value);
          $in_query = true;
        }
      }
      if ($this->fragment !== null) {
        $url .= '#' . rawurlencode($this->fragment);
      }
      return $url;
      
    }

    function set_param($name, $value) {
      $this->params[$name] = $value;
    }

    function unset_param($name) {
      unset($this->params[$name]);
    }

    function set_path_info($value) {
      $this->path_info = $value;
    }

    function unset_path_info() {
      $this->path_info = null;
    }

    function set_link_base($link_base) {
      $this->link_base = $link_base;
    }

    function unset_link_base() {
      $this->link_base = $_SERVER['SCRIPT_NAME'];
    }

    function set_fragment($fragment) {
      $this->fragment = $fragment;
    }

    function unset_fragment() {
      $this->fragment = null;
    }

  }

  function resolve_url($url) {
    # relative link
    if (strpos($url, ":") === false) {
      if ($url[0] != "/") {
        $url = $_SERVER['SCRIPT_NAME'].'/../'.$url;
      }
    }
    return $url;
  }

?>
