<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for exporters
  */

  require_once('registry.php');

  define("MW_COMPONENT_ROLE_EXPORTER", "_exporter");

  function register_exporter($exporter) {
    global $registry;
    $registry->register($exporter, MW_COMPONENT_ROLE_EXPORTER, $exporter->get_format());
  }

  function get_exporters() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_EXPORTER);
  }

  function export($format, $file, $with_history = true, $dataspaces = array()) {
    global $registry;
    $exporter =& $registry->lookup(MW_COMPONENT_ROLE_EXPORTER, $format);
    if ($exporter !== null) {
      return $exporter->export($file, $with_history, $dataspaces);
    }
    return null;
  }

  class MW_Exporter {

    function export($file, $with_history = true, $dataspaces = array()) {
      die("abstract: export");
    }
  
    function get_format() {
      die("abstract: get_format");
    }
    
  }

?>
