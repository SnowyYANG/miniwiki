<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Special Page Redirector (bundled)
  */

  define("MW_PAGE_NAME_PREFIX_SPECIAL", "Special:");

  class MW_CoreSpecialPageRedirectorExtension extends MW_Extension {

    function get_name() {
      return "Core Special Page Redirector";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Redirects Special:* to MW:Special:* if no specific page is found.";
    }

    function initialize() {
      register_page_handler(new MW_SpecialPageRedirectorPageHandler());
      register_page_handler(new MW_SpecialPageTagPageHandler());
      return true;
    }

  }

  register_extension(new MW_CoreSpecialPageRedirectorExtension());

  class MW_SpecialPageRedirectorPageHandler extends MW_PageHandler {
    function get_priority() {
      return 50;
    }
    function get_page($tag, $name, $revision) {
      if (($tag === null) && (strpos($name, MW_PAGE_NAME_PREFIX_SPECIAL) === 0)) {
        $name = MW_PAGE_NAME_PREFIX_MINIWIKI . $name;
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

  class MW_SpecialPageTagPageHandler extends MW_PageHandler {
    function get_priority() {
      return -50;
    }
    function get_page($tag, $name, $revision) {
      if ($tag === MW_PAGE_TAG_SPECIAL) {
        $name = MW_PAGE_NAME_PREFIX_SPECIAL . $name;
        $tag = null;
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

?>
