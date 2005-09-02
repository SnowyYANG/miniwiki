<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY
  
  # error handling module

  # error handler
  function error_handler($errno, $errstr, $errfile, $errline) {
    echo "<b>OOPS! Something is wrong: $errstr</b><br/>(error code $errno, file $errfile, line $errline)<br/><br/>\n";
  }
  set_error_handler("error_handler");
?>
