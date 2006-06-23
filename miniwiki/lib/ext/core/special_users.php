<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Special:Users (bundled)
  */

  class EXT_CoreSpecialUsers extends MW_Extension {

    function get_name() {
      return "Core Special:Users";
    }

    function get_version() {
      return MW_VERSION;
    }

    function get_description() {
      return "Special:Users page.";
    }

    function initialize() {
      register_page_handler(new MW_Special_Users_Page_Handler());
      return true;
    }

  }

  register_extension(new EXT_CoreSpecialUsers());

  class MW_Special_Users_Page_Handler extends MW_Page_Handler {
    function get_page($tag, $name, $revision) {
      if (($tag == null) && ($name == MW_PAGE_NAME_USERS)) {
        return new MW_Special_Users_Page($name);
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

  /**
  * special page with list of users (MW_PAGE_NAME_USERS)
  * allows user creation and deletion (if permitted)
  */
  class MW_Special_Users_Page extends MW_Special_Page {

    /** @protected constructor (do not use directly, use new_page()) */
    function MW_Special_Users_Page($name) {
      parent::MW_Special_Page($name);
    }

    function render() {
      echo '<div class="special-users">', "\n";
      global $auth;
      if ($auth->is_action_permitted(MW_ACTION_CREATE_USER, $this)) {
        echo '<form method="post" action="', htmlspecialchars($this->url_for_action(MW_ACTION_CREATE_USER), ENT_QUOTES), '">', "\n";
        echo '<input type="text" size="40" name="', MW_REQVAR_USER,'"/>', "\n";
        global $mw_texts;
        echo '<input type="submit" value="', htmlspecialchars($mw_texts[MWT_CREATE_USER_BUTTON], ENT_QUOTES),'"/>', "\n";
        echo '</form>', "\n";
      }
      echo '<ul>', "\n";
      global $users_mgr;
      $users = $users_mgr->get_all_usernames();
      foreach ($users as $name) {
        $page = new_user_page($name);
        echo '<li><a href="', htmlspecialchars($page->url_for_action(MW_ACTION_VIEW), ENT_QUOTES), '">',
          htmlspecialchars($page->name, ENT_NOQUOTES), "</a>";
        if ($auth->is_action_permitted(MW_ACTION_DELETE_USER, $this)) {
          echo '<form class="delete-user" method="post" action="', htmlspecialchars($this->url_for_action(MW_ACTION_DELETE_USER), ENT_QUOTES), '">', "\n";
          echo '<input type="hidden" name="', MW_REQVAR_USER,'" value="', $name, '"/>', "\n";
          global $mw_texts;
          echo '<input type="submit" value="', htmlspecialchars($mw_texts[MWT_DELETE_USER_BUTTON], ENT_QUOTES),'"/>', "\n";
          echo '</form>', "\n";
        }
        echo "</li>\n";
      }
      echo "</ul></div>\n";
    }

  }

?>
