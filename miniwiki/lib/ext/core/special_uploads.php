<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # extension Core Special:Uploads (bundled)

  class EXT_CoreSpecialUploads extends MW_Extension {

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
      register_page_handler(new MW_Special_Uploads_Page_Handler());
      return true;
    }

  }

  register_extension(new EXT_CoreSpecialUploads());

  class MW_Special_Uploads_Page_Handler extends MW_Page_Handler {
    function get_page($tag, $name, $revision) {
      if (($tag == null) && ($name == MW_PAGE_NAME_UPLOADS)) {
        return new MW_Special_Uploads_Page($name);
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

  # special page with list of all uploads (MW_PAGE_NAME_UPLOADS)
  class MW_Special_Uploads_Page extends MW_Special_Page {

    # constructor (do not use directly, use new_page())
    function MW_Special_Uploads_Page($name) {
      parent::MW_Special_Page($name);
    }

    function has_action($action) {
      if ($action == MW_ACTION_UPLOAD) {
        return true;
      }
      return parent::has_action($action);
    }
    
    function render() {
      echo '<div class="special-uploads">', "\n";
      global $auth;
      if ($auth->is_action_permitted(MW_ACTION_UPLOAD, $this)) {
        echo '<form enctype="multipart/form-data" action="', htmlspecialchars($this->url_for_action(MW_ACTION_UPLOAD), ENT_QUOTES), '" method="post">'. "\n";
        global $mw_texts;
        echo $mw_texts[MWT_SOURCE_FILENAME], '<input type="file" size="40" name="', MW_REQVAR_SOURCEFILE, '"/><br/>', "\n";
        echo $mw_texts[MWT_DEST_FILENAME], '<input type="text" size="40" name="', MW_REQVAR_DESTFILE, '"/><br/>', "\n";
        echo $mw_texts[MWT_UPLOAD_MESSAGE], "<br/>\n";
        echo '<textarea name="', MW_REQVAR_MESSAGE, '" rows="10" cols="60"/></textarea><br/>', "\n";
        echo '<input type="submit" value="', htmlspecialchars($mw_texts[MWT_UPLOAD_BUTTON], ENT_QUOTES),'"/><br/>', "\n";
        echo '</form>', "\n";
      }
      echo "<ul>\n";
      global $storage;
      $names = $storage->get_resource_names(MW_DS_UPLOADS);
      foreach ($names as $name) {
        $page = new_upload_page($name, MW_REVISION_HEAD);
        echo '<li><a href="', htmlspecialchars($page->url_for_action(MW_ACTION_VIEW), ENT_QUOTES), '">',
          htmlspecialchars($page->name, ENT_NOQUOTES), "</a></li>\n";
      }
      echo "</ul></div>\n";
    }

    # upload new file
    # content: new content
    # message: change message
    # name: file name
    function upload($content, $message, $name) {
      $page = new_upload_page($name, MW_REVISION_HEAD);
      $page->update($content, $message);
      return $page;
    }

  }

?>