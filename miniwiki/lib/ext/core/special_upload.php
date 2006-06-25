<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Special Upload (bundled)
  */

  define("MW_DS_UPLOADS", "uploads");
  
  class MW_CoreSpecialUploadExtension extends MW_Extension {

    function get_name() {
      return "Core Special Upload";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Upload page.";
    }

    function initialize() {
      register_page_handler(new MW_SpecialUploadPageHandler());
      $dataspace_def = new MW_DataSpaceDefinition(MW_DS_UPLOADS, true, MW_RESOURCE_CONTENT_TYPE_BINARY);
      register_dataspace($dataspace_def);
      return true;
    }

  }

  register_extension(new MW_CoreSpecialUploadExtension());

  class MW_SpecialUploadPageHandler extends MW_PageHandler {
    function get_page($tag, $name, $revision) {
      if ($tag == null) {
        if (strpos($name, MW_PAGE_NAME_PREFIX_UPLOAD) === 0) {
          return new MW_SpecialUploadPage($name, $revision);
        } elseif (strpos($name, MW_PAGE_NAME_PREFIX_DATA) === 0) {
          return new MW_SpecialUploadPage($name, $revision);
        }
      }
      if ($tag == MW_PAGE_TAG_UPLOAD) {
        return new MW_SpecialUploadPage(MW_PAGE_NAME_PREFIX_UPLOAD.$name, $revision);
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

  /** special upload page (MW_PAGE_NAME_PREFIX_UPLOAD or MW_PAGE_NAME_PREFIX_DATA) */
  class MW_SpecialUploadPage extends MW_Page {
    # [read-only] attributes
    /** upload name */
    var $upload_name;
    /** is this an upload (MW_PAGE_NAME_PREFIX_UPLOAD) or data (MW_PAGE_NAME_PREFIX_DATA) page? */
    var $is_data_page;
    /** MIME type */
    var $mime_type;
    
    /** @protected
    * constructor (do not use directly, use new_page())
    * @param name page name
    * @param revision page revision
    */
    function MW_SpecialUploadPage($name, $revision) {
      parent::MW_Page($name);
      if (strpos($name, MW_PAGE_NAME_PREFIX_UPLOAD) === 0) {
        $this->upload_name = substr($name, strlen(MW_PAGE_NAME_PREFIX_UPLOAD));
        $this->is_data_page = false;
      } else {
        $this->upload_name = substr($name, strlen(MW_PAGE_NAME_PREFIX_DATA));
        $this->is_data_page = true;
      }
      $this->revision = $revision;
      $this->mime_type = $this->guess_type($this->upload_name);
    }

    /** @private
    * guess MIME type from file name (form extension)
    * @param name file name
    */
    function guess_type($name) {
      if (preg_match('/\.txt$/i', $name)) {
        return "text/plain";
      }
      if (preg_match('/\.html?$/i', $name)) {
        return "text/html";
      }
      if (preg_match('/\.xml?$/i', $name)) {
        return "text/xml";
      }
      if (preg_match('/\.css$/i', $name)) {
        return "text/css";
      }
      if (preg_match('/\.js$/i', $name)) {
        return "application/x-javascript";
      }
      if (preg_match('/\.(jpeg|jpg|jpe)$/i', $name)) {
        return "image/jpeg";
      }
      if (preg_match('/\.png$/i', $name)) {
        return "image/png";
      }
      if (preg_match('/\.gif$/i', $name)) {
        return "image/gif";
      }
      return MW_DEFAULT_MIME_TYPE;
    }

    function has_action($action) {
      switch ($action) {
        case MW_ACTION_VIEW_SOURCE:
          return false;
      }
      return true;
    }
    
    function exists() {
      $storage =& get_storage();
      return $storage->exists(MW_DS_UPLOADS, $this->upload_name);
    }

    /**
    * unlike general contract this function does not load raw content - use load_with_raw_content()
    * note that render() does not render raw content, but dynamic content
    */
    function load() {
      return $this->load_internal(false);
    }
    
    /** same as load(), but also loads raw content (as described by general contract for load()) */
    function load_with_raw_content() {
      return $this->load_internal(true);
    }
    
    /** @private
    * internal load() function
    * @param with_raw whether to load raw_content or not
    */
    function load_internal($with_raw) {
      $rev = $this->revision;
      $storage =& get_storage();
      $res = $storage->get_resource(MW_DS_UPLOADS, $this->upload_name, $rev, $with_raw);
      $this->has_content = false;
      if ($res !== null) {
        $this->has_content = true;
        if ($with_raw) {
          $this->raw_content = $res->get(MW_RESOURCE_KEY_CONTENT);
        }
        $this->raw_content_length = $res->get(MW_RESOURCE_KEY_CONTENT_LENGTH);
        $this->last_modified = $res->get(MW_RESOURCE_KEY_LAST_MODIFIED);
        $this->message = $res->get(MW_RESOURCE_KEY_MESSAGE);
        $this->user = $res->get(MW_RESOURCE_KEY_AUTHOR);
      }
      $this->title = $this->name;
      return $this->has_content;
    }
    
    function delete() {
      $storage =& get_storage();
      $storage->delete_resource(MW_DS_UPLOADS, $this->upload_name);
      $this->has_content = false;
    }
    
    function update($content, $message) {
      $storage =& get_storage();
      $auth =& get_auth();
      $this->user = $auth->user;
      $this->revision = MW_REVISION_HEAD;
      $res = new MW_Resource();
      $res->set(MW_RESOURCE_KEY_NAME, $this->upload_name);
      $res->set(MW_RESOURCE_KEY_CONTENT, $content);
      $res->set(MW_RESOURCE_KEY_MESSAGE, $message);
      $res->set(MW_RESOURCE_KEY_AUTHOR, $this->user);
      $storage->update_resource(MW_DS_UPLOADS, $res);
      $this->load();
      return true;
    }

    function render() {
      $renderer =& get_renderer();
      if ($this->is_data_page) {
        trigger_error("INTERNAL: MW_SpecialUploadPage.render(): is_data_page is true", E_USER_ERROR);
      } else {
        $link_prefix = (strpos($this->mime_type, "image/") === 0) ? MW_LINK_NAME_PREFIX_IMAGE : MW_PAGE_NAME_PREFIX_DATA;
        $renderer->render($this, _("''This page represents uploaded file named '''%FILENAME%''' (of type %MIMETYPE% and size %LENGTH% B).''
    
[[%LINK%|Download file %FILENAME%]]

%MESSAGE%
    
---

For uploading new version, please, use '''Edit''' link at the bottom.", array (
          'LINK' => $link_prefix.$this->upload_name.($this->revision != MW_REVISION_HEAD ? '$'.$this->revision : ''),
          'MESSAGE' => $this->message,
          'FILENAME' => $this->upload_name,
          'MIMETYPE' => $this->mime_type,
          'LENGTH' => $this->raw_content_length
        )));
      }
    }

    function get_all_revisions() {
      $storage =& get_storage();
      $resources = $storage->get_resource_history(MW_DS_UPLOADS, $this->upload_name, false);
      $ret = array();
      $is_head = true;
      foreach ($resources as $res) {
        $page = new_upload_page($res->get(MW_RESOURCE_KEY_NAME), $res->get(MW_RESOURCE_KEY_REVISION));
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
