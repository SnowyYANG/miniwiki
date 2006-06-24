<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for Wiki functions
  */

  require_once('registry.php');

  define("MW_COMPONENT_ROLE_WIKI_FUNCTION", "_wiki_function");
  
  /**
  * register wiki function
  * @param name wiki function name
  * @param cb function callback
  */
  function register_wiki_function($name, $cb) {
    global $registry;
    $registry->register($cb, MW_COMPONENT_ROLE_WIKI_FUNCTION, $name);
  }
  /**
  * call wiki function
  * @param name wiki function name
  * @param args wiki function argument
  * @param renderer_state MW_RendererState
  */
  function call_wiki_function($name, $args, $renderer_state) {
    global $registry;
    $cb =& $registry->lookup(MW_COMPONENT_ROLE_WIKI_FUNCTION, $name);
    if ($cb !== null) {
      return call_user_func($cb, $args, $renderer_state);
    }
    return null;
  }

?>
