<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Special:Pages (bundled)
  */

  class MW_CoreSpecialPagesExtension extends MW_Extension {

    function get_name() {
      return "Core Special:Pages";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Special:Pages page.";
    }

    function initialize() {
      register_page_handler(new MW_SpecialPagesPageHandler());
      return true;
    }

  }

  register_extension(new MW_CoreSpecialPagesExtension());

  class MW_SpecialPagesPageHandler extends MW_PageHandler {
    function get_page($tag, $name, $revision) {
      if (($tag === null) && ($name == MW_PAGE_NAME_PAGES)) {
        return new MW_SpecialPagesPage($name);
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

  /** special page with list of all regular pages (MW_PAGE_NAME_PAGES) */
  class MW_SpecialPagesPage extends MW_SpecialPage {

    /** @protected constructor (do not use directly, use new_page()) */
    function MW_SpecialPagesPage($name) {
      parent::MW_SpecialPage($name);
    }

    function render() {
      echo '<div class="special-pages"><ul>', "\n";
      $storage =& get_storage();
      $names = $storage->get_resource_names(MW_DS_PAGES);
      foreach ($names as $name) {
        $page = new_page($name, MW_REVISION_HEAD);
        echo '<li><a href="', htmlspecialchars($page->url_for_action(MW_ACTION_VIEW), ENT_QUOTES), '">',
          htmlspecialchars($page->name, ENT_NOQUOTES), "</a></li>\n";
      }
      echo "</ul></div>\n";
    }

  }

?>
