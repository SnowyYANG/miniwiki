<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Special:Uploads (bundled)
  */

  /** uploads list special page name */
  define("MW_PAGE_NAME_UPLOADS", "Special:Uploads");
  
  class MW_CoreSpecialUploadsExtension extends MW_Extension {

    function get_name() {
      return "Core Special:Uploads";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Special:Uploads page.";
    }

    function initialize() {
      register_page_handler(new MW_SpecialUploadsPageHandler());
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

    /** @protected constructor (do not use directly, use new_page()) */
    function MW_SpecialUploadsPage($name) {
      parent::MW_SpecialPage($name);
    }

    function has_action($action) {
      if ($action->get_name() == MW_ACTION_UPLOAD) {
        return true;
      }
      return parent::has_action($action);
    }
    
    function render($vars = null) {
      echo '<div class="special-uploads">', "\n";
      $auth =& get_auth();
      $action = get_action(MW_ACTION_UPLOAD);
      if ($auth->is_action_permitted($action, $this)) {
        $link = $action->link();
        echo '<form enctype="multipart/form-data" action="', $link->to_url(true), '" method="post">'. "\n";
        echo _("Source filename"), ': <input type="file" size="40" name="', $link->get_sourcefile_param_name(), '"/><br/>', "\n";
        echo _("Destination filename (may be empty)"), ': <input type="text" size="40" name="', $link->get_destfile_param_name(), '"/><br/>', "\n";
        echo _("Upload message"), ": <br/>\n";
        echo '<textarea name="', $link->get_message_param_name(), '" rows="10" cols="60"/></textarea><br/>', "\n";
        echo '<input type="submit" value="', htmlspecialchars(_("Upload"), ENT_QUOTES),'"/><br/>', "\n";
        echo '</form>', "\n";
      }
      echo "<ul>\n";
      $storage =& get_storage();
      $names = $storage->get_resource_names(MW_DS_UPLOADS);
      foreach ($names as $name) {
        $page = new_upload_page($name, MW_REVISION_HEAD);
        echo '<li><a href="', url_for_page_action($page, MW_ACTION_VIEW, true), '">',
          htmlspecialchars($page->name, ENT_NOQUOTES), "</a></li>\n";
      }
      echo "</ul></div>\n";
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