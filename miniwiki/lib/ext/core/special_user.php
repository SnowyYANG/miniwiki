<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # extension Core Special User (bundled)

  class EXT_CoreSpecialUser extends MW_Extension {

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
      register_page_handler(new MW_Special_User_Page_Handler());
      return true;
    }

  }

  register_extension(new EXT_CoreSpecialUser());

  class MW_Special_User_Page_Handler extends MW_Page_Handler {
    function get_page($tag, $name, $revision) {
      $overlay = false;
      if (($tag == null) && (strpos($name, MW_PAGE_NAME_PREFIX_USER) === 0)) {
        $overlay = true;
      }
      if ($tag == MW_PAGE_TAG_USER) {
        $overlay = true;
        $name = MW_PAGE_NAME_PREFIX_USER.$name;
        $tag = null;
      }
      $page = $this->next->get_page($tag, $name, $revision);
      if ($overlay && ($page !== null)) {
        $page = new MW_Special_User_Page($page);
      }
      return $page;
    }
  }

  # special user page (MW_PAGE_NAME_PREFIX_USER)
  # this page always exists even if empty (but then it is not stored in database)
  # user associated with this page may not exist
  class MW_Special_User_Page extends MW_Page {
    # [read-only] attributes
    # user associated with this user page
    var $related_user;
    # overlayed page
    var $page;
    
    # constructor (do not use directly, use new_user_page() or new_page())
    # name: page name
    # revision: page revision
    function MW_Special_User_Page($page) {
      parent::MW_Page($page->name);
      $this->page = $page;
      $this->fill_vars();
      $this->related_user = substr($this->name, strlen(MW_PAGE_NAME_PREFIX_USER));
      $this->last_modified = now_as_last_modified();
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
    
    # user page always exists
    function exists() {
      return true;
    }
    
    # user page is always loaded
    function load() {
      $this->page->load();
      $this->fill_vars();
      $this->has_content = true;
      return true;
    }
    
    # user page exists even if deleted
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
      $this->page->update_for_preview($content, $message);
      $this->fill_vars();
    }
    
    function render() {
      global $auth;
      if ($auth->is_action_permitted(MW_ACTION_CHANGE_PASSWORD, $this)) {
        echo '<form method="post" action="', htmlspecialchars($this->url_for_action(MW_ACTION_CHANGE_PASSWORD), ENT_QUOTES), '">', "\n";
        echo '<input type="hidden" name="', MW_REQVAR_USER,'" value="', $this->related_user, '"/>', "\n";
        echo '<input type="password" size="40" name="', MW_REQVAR_PASS,'"/>', "\n";
        global $mw_texts;
        echo '<input type="submit" value="', htmlspecialchars($mw_texts[MWT_CHANGE_PASSWORD_BUTTON], ENT_QUOTES),'"/>', "\n";
        echo '</form>', "\n";
      }
      $this->page->render();
    }
    
    function url_for_action($action) {
      return $this->page->url_for_action($action);
    }
    
    function get_all_revisions() {
      return $this->page->get_all_revisions();
    }
    
    # create user associated with this page (user page is not created)
    # change_password() must be called too or else noone can login as this user
    function create_user() {
      global $users_mgr;
      $users_mgr->create_user($this->related_user);
    }
    
    # delete user associated with this page (user page is not deleted)
    function delete_user() {
      global $users_mgr;
      $users_mgr->delete_user($this->related_user);
    }
    
    # change password for associated user
    # pass: new password
    function change_password($pass) {
      global $users_mgr;
      $users_mgr->change_password($this->related_user, $pass);
    }
    
    # returns true if given password is valid for associated user
    # pass: password
    function is_password_valid($pass) {
      global $users_mgr;
      return $users_mgr->is_password_valid($this->related_user, $pass);
    }
    
  }

?>
