<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # debug module
  
  define("MW_DEBUG", false);
  
  # echo debug message (if MW_DEBUG is true)
  # msg: message to show
  function debug($msg) {
    if (MW_DEBUG) {
      echo '<div class="debug">'.htmlspecialchars('DEBUG: '.$msg, ENT_NOQUOTES),"</div>\n";
    }
  }
  
?>
