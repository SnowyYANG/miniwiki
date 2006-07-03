<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for installation mode
  */

  require_once('registry.php');

  define("MW_COMPONENT_ROLE_INSTALL_HANDLER", "MW_InstallHandler");
  $registry->add_registry(new MW_SingletonComponentRegistry(), MW_COMPONENT_ROLE_INSTALL_HANDLER);
  
  class MW_InstallHandler {
    function show_install_message($msg) {
      die ("abstract: show_install_message");
    }
  }

  function show_install_message($msg) {
    global $registry;
    $install_handler =& $registry->lookup(MW_COMPONENT_ROLE_INSTALL_HANDLER);
    $install_handler->show_install_message($msg);
  }

  function register_install_handler(&$handler) {
    global $registry;
    $registry->register($handler, MW_COMPONENT_ROLE_INSTALL_HANDLER);
  }
?>
