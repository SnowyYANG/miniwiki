<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Obsolete (bundled)
  */

  /** data page prefix (raw uploaded file) from miniWiki 0.2 */
  define("MW_PAGE_NAME_PREFIX_DATA_0_2", "Data:");
  define("MW_PAGE_NAME_PREFIX_USER_0_2", "User:");
  define("MW_PAGE_NAME_UPLOADS_0_2", "Special:Uploads");
  define("MW_PAGE_NAME_PREFIX_SPECIAL_0_2", "Special:");
  
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
      if (($tag === null) && (strpos($name, MW_PAGE_NAME_PREFIX_DATA_0_2) === 0)) {
        $name = str_replace(MW_PAGE_NAME_PREFIX_DATA_0_2, MW_PAGE_NAME_PREFIX_DATA, $name);
      }
      if (($tag === null) && (strpos($name, MW_PAGE_NAME_PREFIX_USER_0_2) === 0)) {
        $name = str_replace(MW_PAGE_NAME_PREFIX_USER_0_2, MW_PAGE_NAME_PREFIX_USER, $name);
      }
      if (($tag === null) && (strpos($name, MW_PAGE_NAME_UPLOADS_0_2) === 0)) {
        $name = str_replace(MW_PAGE_NAME_UPLOADS_0_2, MW_PAGE_NAME_UPLOADS, $name);
      }
      if (($tag === null) && (strpos($name, MW_PAGE_NAME_PREFIX_SPECIAL_0_2) === 0)) {
        $name = str_replace(MW_PAGE_NAME_PREFIX_SPECIAL_0_2, MW_PAGE_NAME_PREFIX_SPECIAL, $name);
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

?>
