<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Page (bundled)
  */

  define("MW_DS_PAGES", "pages");
  
  class MW_CorePageExtension extends MW_Extension {

    function get_name() {
      return "Core Page";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Wiki page.";
    }

    function initialize() {
      register_page_handler(new MW_CorePagePageHandler());
      $dataspace_def = new MW_DataSpaceDefinition(MW_DS_PAGES, true, MW_RESOURCE_CONTENT_TYPE_TEXT);
      register_dataspace($dataspace_def);
      return true;
    }

  }

  register_extension(new MW_CorePageExtension());

  class MW_CorePagePageHandler extends MW_PageHandler {
    function get_priority() {
      return 100;
    }
    function get_page($tag, $name, $revision) {
      if ($tag == null) {
        return new MW_DBPage($name, $revision);
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

  /** regular Wiki page */
  class MW_DBPage extends MW_Page {
    
    /** @protected
    * constructor (do not use directly, use new_page())
    * @param name page name
    * @param revision page revision
    */
    function MW_DBPage($name, $revision) {
      parent::MW_Page($name);
      $this->revision = $revision;
    }

    function has_action($action) {
      if ($action == MW_ACTION_UPLOAD) {
        return false;
      }
      return true;
    }
    
    function exists() {
      global $storage;
      return $storage->exists(MW_DS_PAGES, $this->name);
    }

    /** @private process content (mainly directives) */
    function process_content() {
      if ($this->has_content) {
        /** @todo will happily process directives inside &lt;pre&gt; blocks */
        if (preg_match_all("/(?:^|\n)#TITLE\s+(.*?)(?:$|\n)/", $this->raw_content, $matches)) {
          $this->title = $matches[1][count($matches[1]) - 1];
          $this->title = str_replace("\r", '', $this->title);
        }
      }
    }

    function load() {
      $rev = $this->revision;
      global $storage;
      $res = $storage->get_resource(MW_DS_PAGES, $this->name, $rev, true);
      $this->has_content = false;
      if ($res !== null) {
        $this->has_content = true;
        $this->raw_content = $res->get(MW_RESOURCE_KEY_CONTENT);
        $this->raw_content_length = $res->get(MW_RESOURCE_KEY_CONTENT_LENGTH);
        $this->last_modified = $res->get(MW_RESOURCE_KEY_LAST_MODIFIED);
        $this->message = $res->get(MW_RESOURCE_KEY_MESSAGE);
        $this->user = $res->get(MW_RESOURCE_KEY_AUTHOR);
      }
      $this->title = $this->name;
      $this->process_content();
      return $this->has_content;
    }
    
    function delete() {
      global $storage;
      $storage->delete_resource(MW_DS_PAGES, $this->name);
      $this->has_content = false;
    }
    
    function update($content, $message) {
      $this->load();
      if ($this->has_content && ($this->raw_content == $content)) {
        return false;
      }
      global $auth, $storage;
      $this->user = $auth->user;
      $this->revision = MW_REVISION_HEAD;
      $res = new MW_Resource();
      $res->set(MW_RESOURCE_KEY_NAME, $this->name);
      $res->set(MW_RESOURCE_KEY_CONTENT, $content);
      $res->set(MW_RESOURCE_KEY_MESSAGE, $message);
      $res->set(MW_RESOURCE_KEY_AUTHOR, $this->user);
      $storage->update_resource(MW_DS_PAGES, $res);
      $this->load();
      return true;
    }

    function update_for_preview($content) {
      if ($this->exists()) {
        $this->load();
      }
      $this->raw_content = $content;
      $this->has_content = true;
      $this->process_content();
    }
    
    function render() {
      global $renderer;
      $renderer->render($this, $this->raw_content);
    }
    
    function get_all_revisions() {
      global $storage;
      $resources = $storage->get_resource_history(MW_DS_PAGES, $this->name, false);
      $ret = array();
      $is_head = true;
      foreach ($resources as $res) {
        $page = new_page($res->get(MW_RESOURCE_KEY_NAME), $res->get(MW_RESOURCE_KEY_REVISION));
        $page->last_modified = $res->get(MW_RESOURCE_KEY_LAST_MODIFIED);
        $page->message = $res->get(MW_RESOURCE_KEY_MESSAGE);
        $page->user = $res->get(MW_RESOURCE_KEY_AUTHOR);
        if ($is_head) {
          $page->revision = MW_REVISION_HEAD;
        }
        $page->raw_content_length = $res->get(MW_RESOURCE_KEY_CONTENT_LENGTH);
        array_push ($ret, $page);
        $is_head = false;
      }
      return $ret;
    }
    
  }

?>
