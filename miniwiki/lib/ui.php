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
  define("MW_PAGE_TAG_LAYOUT_DATA", "layout_data");

  define("MW_LAYOUT_DEFAULT", "Default");

  set_default_config('layout', MW_LAYOUT_DEFAULT);
  
  function new_layout_page($name) {
    $page = new_page_with_tag(MW_PAGE_TAG_LAYOUT, config('layout').'/'.$name, MW_REVISION_HEAD);
    if (!$page->exists()) {
      $page = new_page_with_tag(MW_PAGE_TAG_LAYOUT, MW_LAYOUT_DEFAULT.'/'.$name, MW_REVISION_HEAD);
    }
    return $page;
  }

  function new_layout_data_page($name) {
    $page = new_page_with_tag(MW_PAGE_TAG_LAYOUT_DATA, config('layout').'/'.$name, MW_REVISION_HEAD);
    if (!$page->exists()) {
      $page = new_page_with_tag(MW_PAGE_TAG_LAYOUT_DATA, MW_LAYOUT_DEFAULT.'/'.$name, MW_REVISION_HEAD);
    }
    return $page;
  }

  function load_layout_page($name) {
    $layout_page = new_layout_page($name);
    if (!$layout_page->load()) {
      trigger_error(_t("Required layout page %0% is missing", $layout_page->name), E_USER_ERROR);
      return null;
    }
    return $layout_page;
  }

  function render_ui($ui_page, $title = null) {
    $page =& get_current_page();
    $renderer =& get_renderer();
    $layout_page = load_layout_page($ui_page);
    if ($layout_page !== null) {
      if (empty($title)) {
        $title = $page->title;
      }
      if (empty($title)) {
        $title = $page->name;
      }
      $vars = new_global_wiki_variables();
      $vars->set('title', $title);
      $info_text = get_info_text();
      if (count($info_text) > 0) {
        $vars->set('info_text', implode(' ', $info_text));
      }
      $redirected_page =& get_redirected_page();
      if ($redirected_page !== null) {
        $vars->set('redir_page', $redirected_page->name);
      }
      $layout_page->render($vars);
    }
  }
  
?>
