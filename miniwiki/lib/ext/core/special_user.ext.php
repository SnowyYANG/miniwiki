<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Special User (bundled)
  */

  /** user page name prefix */
  define("MW_PAGE_NAME_PREFIX_USER", "User:");
  define("MW_SPECIAL_PAGE_USER", "User");
  
  class MW_CoreSpecialUserExtension extends MW_Extension {

    function get_name() {
      return "Core Special User";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "User page.";
    }

    function initialize() {
      register_page_handler(new MW_SpecialUserPageHandler());
      return true;
    }

  }

  register_extension(new MW_CoreSpecialUserExtension());

  class MW_SpecialUserPageHandler extends MW_PageHandler {
    function get_page($tag, $name, $revision) {
      $overlay = false;
      if (($tag === null) && (strpos($name, MW_PAGE_NAME_PREFIX_USER) === 0)) {
        $overlay = true;
      }
      if ($tag == MW_PAGE_TAG_USER) {
        $overlay = true;
        $name = MW_PAGE_NAME_PREFIX_USER.$name;
        $tag = null;
      }
      $page = $this->next->get_page($tag, $name, $revision);
      if ($overlay && ($page !== null)) {
        $page = new MW_SpecialUserPage($page);
      }
      return $page;
    }
  }

  /**
  * special user page (MW_PAGE_NAME_PREFIX_USER)
  * this page always exists even if empty (but then it is not stored in database)
  * user associated with this page may not exist
  */
  class MW_SpecialUserPage extends MW_Page {
    # [read-only] attributes
    /** user associated with this user page */
    var $related_user;
    /** overlayed page */
    var $page;
    
    /** @protected
    * constructor (do not use directly, use new_user_page() or new_page())
    * @param page "real" page
    */
    function MW_SpecialUserPage($page) {
      parent::MW_Page($page->name);
      $this->page = $page;
      $this->fill_vars();
      $this->related_user = substr($this->name, strlen(MW_PAGE_NAME_PREFIX_USER));
      $this->last_modified = now_as_datetime();
    }

    function fill_vars() {
      $this->name = $this->page->name;
      $this->revision = $this->page->revision;
      $this->has_content = $this->page->has_content;
      $this->raw_content = $this->page->raw_content;
      $this->last_modified = $this->page->last_modified;
      $this->message = $this->page->message;
      $this->user = $this->page->user;
      $this->title = $this->page->title;
      $this->raw_content_length = $this->page->raw_content_length;
    }

    function has_action($action) {
      return $this->page->has_action($action);
    }
    
    /** user page always exists */
    function exists() {
      return true;
    }
    
    /** user page is always loaded */
    function load() {
      $this->page->load();
      $this->fill_vars();
      $this->has_content = true;
      return true;
    }
    
    /** user page exists even if deleted */
    function delete() {
      $this->page->delete();
      $this->fill_vars();
      $this->has_content = true;
    }
    
    function update($content, $message) {
      $ret = $this->page->update($content, $message);
      $this->fill_vars();
      return $ret;
    }
    
    function update_for_preview($content) {
      $this->page->update_for_preview($content);
      $this->fill_vars();
    }
    
    function get_wiki_content() {
      $special_page = load_special_page(MW_SPECIAL_PAGE_USER);
      if ($special_page !== null) {
        return wiki_include($special_page, array(
          'related_user' => $this->related_user,
          'user_page_content' => $this->page->get_wiki_content()
        ), false, false);
      }
    }
    
    function get_all_revisions() {
      return $this->page->get_all_revisions();
    }
    
    /**
    * create user associated with this page (user page is not created)
    * change_password() must be called too or else noone can login as this user
    */
    function create_user() {
      $users_mgr =& get_users_manager();
      $users_mgr->create_user($this->related_user);
    }
    
    /** delete user associated with this page (user page is not deleted) */
    function delete_user() {
      $users_mgr =& get_users_manager();
      $users_mgr->delete_user($this->related_user);
    }
    
    /**
    * change password for associated user
    * @param pass new password
    */
    function change_password($pass) {
      $users_mgr =& get_users_manager();
      $users_mgr->change_password($this->related_user, $pass);
    }
    
    /**
    * returns true if given password is valid for associated user
    * @param pass password
    */
    function is_password_valid($pass) {
      $users_mgr =& get_users_manager();
      return $users_mgr->is_password_valid($this->related_user, $pass);
    }
    
  }

?>
