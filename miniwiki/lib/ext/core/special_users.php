<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * extension Core Special:Users (bundled)
  */

  class MW_CoreSpecialUsersExtension extends MW_Extension {

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
      register_page_handler(new MW_SpecialUsersPageHandler());
      return true;
    }

  }

  register_extension(new MW_CoreSpecialUsersExtension());

  class MW_SpecialUsersPageHandler extends MW_PageHandler {
    function get_page($tag, $name, $revision) {
      if (($tag === null) && ($name == MW_PAGE_NAME_USERS)) {
        return new MW_SpecialUsersPage($name);
      }
      return $this->next->get_page($tag, $name, $revision);
    }
  }

  /**
  * special page with list of users (MW_PAGE_NAME_USERS)
  * allows user creation and deletion (if permitted)
  */
  class MW_SpecialUsersPage extends MW_SpecialPage {

    /** @protected constructor (do not use directly, use new_page()) */
    function MW_SpecialUsersPage($name) {
      parent::MW_SpecialPage($name);
    }

    function render() {
      echo '<div class="special-users">', "\n";
      $auth =& get_auth();
      $create_action = get_action(MW_ACTION_CREATE_USER);
      if ($auth->is_action_permitted($create_action, $this)) {
        $create_link = $create_action->link();
        echo '<form method="post" action="', $create_link->to_url(true), '">', "\n";
        echo '<input type="text" size="40" name="', $create_link->get_user_param_name(),'"/>', "\n";
        echo '<input type="submit" value="', htmlspecialchars(_('Create User'), ENT_QUOTES),'"/>', "\n";
        echo '</form>', "\n";
      }
      echo '<ul>', "\n";
      $users_mgr =& get_users_manager();
      $users = $users_mgr->get_all_usernames();
      foreach ($users as $name) {
        $page = new_user_page($name);
        echo '<li><a href="', url_for_page_action($page, MW_ACTION_VIEW, true), '">',
          htmlspecialchars($page->name, ENT_NOQUOTES), "</a>";
        $delete_action = get_action(MW_ACTION_DELETE_USER);
        if ($auth->is_action_permitted($delete_action, $this)) {
          $delete_link = $delete_action->link();
          $delete_link->set_page($page);
          echo '<form class="delete-user" method="post" action="', $delete_link->to_url(true), '">', "\n";
          echo '<input type="hidden" name="', $delete_link->get_user_param_name(),'" value="', $name, '"/>', "\n";
          echo '<input type="submit" value="', htmlspecialchars(_('Delete User'), ENT_QUOTES),'"/>', "\n";
          echo '</form>', "\n";
        }
        echo "</li>\n";
      }
      echo "</ul></div>\n";
    }

  }

?>
