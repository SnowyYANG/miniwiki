<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for installation mode
  */

  class MW_InstallHandler {
    function show_install_message($msg) {
      die ("abstract: show_install_message");
    }
  }

  function show_install_message($msg) {
    global $install_handler;
    $install_handler->show_install_message($msg);
  }

?>
