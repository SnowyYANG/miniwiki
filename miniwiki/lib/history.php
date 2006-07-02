<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * history page
  */

  $page =& get_current_page();
  
  render_ui(MW_LAYOUT_HEADER);
  echo '<div class="history"><ul>',"\n";
  $hist_pages = $page->get_all_revisions();
  foreach ($hist_pages as $hist_page) {
    if (isset($hist_page->user)) {
      $user_page = new_user_page($hist_page->user);
    }
    echo '<li><a href="', url_for_page_action($hist_page, MW_ACTION_VIEW, true), '">',
      format_datetime($hist_page->last_modified), '</a>',
      ((isset ($hist_page->user) && trim($hist_page->user)) ? ' <span class="history-user"> - <a href="'.
        url_for_page_action($user_page, MW_ACTION_VIEW, true).'">'.
        htmlspecialchars($hist_page->user, ENT_NOQUOTES).'</a></span>' : ''),
      ((isset ($hist_page->raw_content_length)) ? ' <span class="history-content-length">('.$hist_page->raw_content_length.' B)</span>' : ''),
      ((isset ($hist_page->message) && trim($hist_page->message)) ? ' <span class="history-message">('.htmlspecialchars($hist_page->message, ENT_NOQUOTES).')</span>' : ''),
      '</li>', "\n";
  }
  echo '</ul></div>',"\n";
  render_ui(MW_LAYOUT_FOOTER);
?>
