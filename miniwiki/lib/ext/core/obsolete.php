<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Obsolete (bundled)
  */

  class MW_CoreObsoleteExtension extends MW_Extension {

    function get_name() {
      return "Core Obsolete";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Obsolete functionality.";
    }

    function initialize() {
      register_page_handler(new MW_ObsoletePageHandler());
      return true;
    }

  }

  register_extension(new MW_CoreObsoleteExtension());

  class MW_ObsoletePageHandler extends MW_PageHandler {
    function get_priority() {
      return -100;
    }
    function get_page($tag, $name, $revision) {
      if (($tag == null) && (strpos($name, MW_PAGE_NAME_PREFIX_DATA_0_2) === 0)) {
        $name = str_replace(MW_PAGE_NAME_PREFIX_DATA_0_2, MW_PAGE_NAME_PREFIX_DATA, $name);
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

?>
