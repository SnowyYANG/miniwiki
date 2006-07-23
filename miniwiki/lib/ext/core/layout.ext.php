<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Layout (bundled)
  */

  /** layout page prefix */
  define("MW_PAGE_NAME_PREFIX_LAYOUT", MW_PAGE_NAME_PREFIX_MINIWIKI . "Layout/");
  
  class MW_CoreLayoutExtension extends MW_Extension {

    function get_name() {
      return "Core Layout";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Customizable layout.";
    }

    function initialize() {
      register_page_handler(new MW_LayoutPageHandler());
      return true;
    }

  }

  register_extension(new MW_CoreLayoutExtension());

  class MW_LayoutPageHandler extends MW_PageHandler {
    function get_page($tag, $name, $revision) {
      if ($tag == MW_PAGE_TAG_LAYOUT) {
        $name = MW_PAGE_NAME_PREFIX_LAYOUT.$name;
        $tag = null;
      } elseif ($tag == MW_PAGE_TAG_LAYOUT_DATA) {
        $name = MW_PAGE_NAME_PREFIX_DATA.MW_PAGE_NAME_PREFIX_LAYOUT.$name;
        $tag = null;
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

?>
