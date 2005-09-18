<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # page footer
  # auth: current MW_Auth
  # db: curent MW_Database
  # mw_texts: texts array
  # page: current MW_Page
  # req: current MW_Request

  $layout_footer = new_page($db, MW_PAGE_NAME_LAYOUT_FOOTER, MW_REVISION_HEAD);
  if ($layout_footer->load()) {
    global $renderer;
    $renderer->render($page, $layout_footer->raw_content);
  } else {

  echo '<div class="footer">', "\n";

  # shows action links (active if current user has permissions, completely missing if current page does not have such action)
  # actions: array of action names
  # head_rev: true if action should link to HEAD revision, links to current page's revision otherwise - defaults to false
  function show_actions($actions, $head_rev = false) {
    global $page, $auth, $mw_texts;
    foreach ($actions as $action) {
      if ($page->has_action($action)) {
        if ($auth->is_action_permitted($action, $page)) {
          echo ' | <a href="', htmlspecialchars($page->url_for_action($action, ($head_rev ? MW_REVISION_HEAD : $page->revision)), ENT_QUOTES), '">', $mw_texts[$action], '</a>';
        } else {
          echo ' | ', $mw_texts[$action];
        }
      }
    }
  }

  # MainPage link
  $main_page = new_page($db, MW_DEFAULT_PAGE_NAME, MW_REVISION_HEAD);
  echo '<a href="', htmlspecialchars($main_page->url_for_action(MW_ACTION_VIEW), ENT_QUOTES), '">', htmlspecialchars($main_page->name, ENT_NOQUOTES), '</a>';
  # login status and actions
  $login_param = '';
  if ($auth->is_logged) {
    $login_action = MW_ACTION_RELOGIN;
    $login_msg = $mw_texts[MWT_LOGGED_AS]." ".htmlspecialchars($auth->user, ENT_NOQUOTES);
    $login_param = '&'.MW_REQVAR_OLD_USER.'='.rawurlencode($auth->user);
  } else {
    $login_action = MW_ACTION_LOGIN;
    $login_msg = $mw_texts[MWT_ACTION_LOGIN];
  }
  echo ' | <a href="', htmlspecialchars($page->url_for_action($login_action, $page->revision), ENT_QUOTES), htmlspecialchars($login_param, ENT_QUOTES), '">', $login_msg, '</a>';
  # current page information and View Source action
  if ($page->has_content) {
    echo ' | ', $mw_texts[MWT_REVISION], ": ", $page->revision;
    show_actions(array(($req->action == MW_ACTION_VIEW_SOURCE) ? MW_ACTION_VIEW : MW_ACTION_VIEW_SOURCE));
    echo ' | ', format_last_modified($page->last_modified);
  }
  # current page HEAD actions
  show_actions(array(MW_ACTION_VIEW, MW_ACTION_EDIT, MW_ACTION_DELETE, MW_ACTION_HISTORY), true);
  
  echo "</div>\n";
  
  }
?>
</body>
</html>
