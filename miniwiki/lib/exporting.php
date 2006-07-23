<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for exporters
  */

  require_once('registry.php');

  define("MW_COMPONENT_ROLE_EXPORTER", "MW_Exporter");

  function register_exporter(&$exporter) {
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

  define("MW_COMPONENT_ROLE_EXPORTING_HANDLER", "MW_ExportingHandler");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_EXPORTING_HANDLER);
  
  class MW_ExportingHandler {
    function show_exporting_message($msg) {
      die ("abstract: show_exporting_message");
    }
  }

  function show_exporting_message($msg) {
    global $registry;
    $install_handler =& $registry->lookup(MW_COMPONENT_ROLE_EXPORTING_HANDLER);
    $install_handler->show_exporting_message($msg);
  }

  function register_exporting_handler(&$handler) {
    global $registry;
    $registry->register($handler, MW_COMPONENT_ROLE_EXPORTING_HANDLER);
  }
  
?>
