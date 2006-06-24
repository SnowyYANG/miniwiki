<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for showing messages to the user
  */

  require_once('registry.php');
  
  define("MW_COMPONENT_ROLE_INFO_TEXT", "_info_text");
  
  /**
  * add new information to be shown to the user
  * @param text text to show
  */
  function add_info_text($text) {
    global $registry;
    $registry->register($text, MW_COMPONENT_ROLE_INFO_TEXT);
  }
  /**
  * returns array with all information texts
  */
  function get_info_text() {
    global $registry;
    return $registry->lookup(MW_COMPONENT_ROLE_INFO_TEXT);
  }

?>
