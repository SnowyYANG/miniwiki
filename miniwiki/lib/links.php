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

    function to_url($in_attr = false) {
      $url = $this->_to_url();
      if ($in_attr) {
        $url = htmlspecialchars($url, ENT_QUOTES);
      }
      return $url;
    }

    /** @private */
    function _to_url() {
      $url = $_SERVER['SCRIPT_NAME'];
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

  }

?>
