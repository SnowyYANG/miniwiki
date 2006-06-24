<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * page header
  * @param title override page name
  */

  $page =& get_current_page();
  $renderer =& get_renderer();
  
  if (!isset ($title)) {
    $title = $page->title;
  }

  $layout_header = new_page(MW_PAGE_NAME_LAYOUT_HEADER, MW_REVISION_HEAD);
  if ($layout_header->load()) {
    $vars = new_global_wiki_variables();
    $vars->set('title', $title);
    $info_text = get_info_text();
    if (count($info_text) > 0) {
      $vars->set('info_text', implode(' ', $info_text));
    }
    $renderer->render($page, $layout_header->raw_content, $vars);
  } else {
    trigger_error("Required layout page ".$layout_header->name." is missing", E_USER_ERROR);
  }
?>
