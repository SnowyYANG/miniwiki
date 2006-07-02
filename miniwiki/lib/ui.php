<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * support for user interface
  */

  require_once("pages.php");
  require_once("rendering.php");

  /** footer layout name */
  define("MW_LAYOUT_FOOTER", "Footer");
  /** header layout name */
  define("MW_LAYOUT_HEADER", "Header");
  
  define("MW_PAGE_TAG_LAYOUT", "layout");
  
  function new_layout_page($name) {
    return new_page_with_tag(MW_PAGE_TAG_LAYOUT, $name, MW_REVISION_HEAD);
  }

  function render_ui($ui_page, $title = null) {
    $page =& get_current_page();
    $renderer =& get_renderer();
    $layout_page = new_layout_page($ui_page);
    if ($layout_page->load()) {
      if (!isset ($title)) {
        $title = $page->title;
      }
      $vars = new_global_wiki_variables();
      $vars->set('title', $title);
      $info_text = get_info_text();
      if (count($info_text) > 0) {
        $vars->set('info_text', implode(' ', $info_text));
      }
      if (method_exists($layout_page, "render_ui")) {
        $layout_page->render_ui($vars);
      } else {
        $renderer->render($page, $layout_page->raw_content, $vars);
      }
    } else {
      trigger_error("Required layout page ".$layout_page->name." is missing", E_USER_ERROR);
    }
  }
  
?>
