<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * page footer
  */

  $page =& get_current_page();
  $renderer =& get_renderer();
  
  $layout_footer = new_page(MW_PAGE_NAME_LAYOUT_FOOTER, MW_REVISION_HEAD);
  if ($layout_footer->load()) {
    $renderer->render($page, $layout_footer->raw_content);
  } else {
    trigger_error("Required layout page ".$layout_footer->name." is missing", E_USER_ERROR);
  }
?>