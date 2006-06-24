<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for importers
  */

  require_once('registry.php');

  define("MW_COMPONENT_ROLE_IMPORTER", "_importer");

  function register_importer(&$importer) {
    global $registry;
    $registry->register($importer, MW_COMPONENT_ROLE_IMPORTER);
  }

  function get_importers() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_IMPORTER);
  }

  function import($file, $with_history = true, $dataspaces = array(), $force_import = false) {
    global $registry;
    $importers =& $registry->lookup(MW_COMPONENT_ROLE_IMPORTER);
    foreach ($importers as $importer) {
      # null is "unknown format", true is OK and string is error message
      $ret = $importer->import($file, $with_history, $dataspaces, $force_import);
      if ($ret !== null) {
        return $ret;
      }
    }
    return null;
  }

  class MW_Importer {

    function import($file, $with_history = true, $dataspaces = array(), $force_import = false) {
      die("abstract: import");
    }

    function get_format() {
      die("abstract: get_format");
    }
  
  }
  
?>
