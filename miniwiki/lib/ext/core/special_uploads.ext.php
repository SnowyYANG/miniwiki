<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Special:Uploads (bundled)
  */

  /** uploads list special page name */
  define("MW_PAGE_NAME_UPLOADS", "Special/Uploads");
  
  class MW_CoreSpecialUploadsExtension extends MW_Extension {

    function get_name() {
      return "Core Special/Uploads";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Special/Uploads page.";
    }

    function initialize() {
      register_page_handler(new MW_SpecialUploadsPageHandler());
      register_special_page(MW_PAGE_NAME_UPLOADS);
      return true;
    }

  }

  register_extension(new MW_CoreSpecialUploadsExtension());

  class MW_SpecialUploadsPageHandler extends MW_PageHandler {
    function get_page($tag, $name, $revision) {
      if (($tag === null) && ($name == MW_PAGE_NAME_UPLOADS)) {
        return new MW_SpecialUploadsPage($name);
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

  /** special page with list of all uploads (MW_PAGE_NAME_UPLOADS) */
  class MW_SpecialUploadsPage extends MW_SpecialPage {

    /** @private */
    var $wrapped;

    /** @protected constructor (do not use directly, use new_page()) */
    function MW_SpecialUploadsPage($name) {
      parent::MW_SpecialPage($name);
      # can not use load_special_page() because of infinite loop
      $this->wrapped = new_page(MW_PAGE_NAME_PREFIX_MINIWIKI.$this->name, MW_REVISION_HEAD);
    }

    function has_action($action) {
      if ($action->get_name() == MW_ACTION_EDIT) {
        return true;
      }
      return parent::has_action($action);
    }

    function load() {
      $ret = $this->wrapped->load();
      $this->title = $this->wrapped->title;
      return $ret;
    }

    function get_wiki_content() {
      return $this->wrapped->get_wiki_content();
    }

    function get_attr($name) {
      return $this->wrapped->get_attr($name);
    }
    
    /**
    * upload new file
    * @param content new content
    * @param message change message
    * @param name file name
    */
    function upload($content, $message, $name) {
      $page = new_upload_page($name, MW_REVISION_HEAD);
      $page->update($content, $message);
      return $page;
    }

  }

?>
