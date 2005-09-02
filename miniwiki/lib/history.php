<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # history page
  # page: current MW_Page

  $title = $page->name;
  include('header.php');
  echo '<div class="history"><ul>',"\n";
  $hist_pages = $page->get_all_revisions();
  foreach ($hist_pages as $hist_page) {
    if (isset($hist_page->user)) {
      $user_page = new_user_page($hist_page->db, $hist_page->user);
    }
    echo '<li><a href="', $hist_page->url_for_action(MW_ACTION_VIEW), '">',
      format_last_modified($hist_page->last_modified), '</a>',
      ((isset ($hist_page->user) && trim($hist_page->user)) ? ' <span class="history-user"> - <a href="'.
        $user_page->url_for_action(MW_ACTION_VIEW).'">'.
        htmlspecialchars($hist_page->user, ENT_NOQUOTES).'</a></span>' : ''),
      ((isset ($hist_page->raw_content_length)) ? ' <span class="history-content-length">('.$hist_page->raw_content_length.' B)</span>' : ''),
      ((isset ($hist_page->message) && trim($hist_page->message)) ? ' <span class="history-message">('.htmlspecialchars($hist_page->message, ENT_NOQUOTES).')</span>' : ''),
      '</li>', "\n";
  }
  echo '</ul></div>',"\n";
  include('footer.php');
?>
