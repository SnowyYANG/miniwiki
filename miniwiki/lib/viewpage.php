<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # view Wiki page
  # page: current MW_Page

  if (is_a($page, 'MW_Special_Upload_Page') && $page->is_data_page) {
    header('Content-Type: '.$page->mime_type);
    if ($page->raw_content_length) {
      header('Content-Length: '.$page->raw_content_length);
    }
    if (!$req->is_head) {
      $page->load_with_raw_content();
      echo $page->raw_content;
    }
  } else {
    include('header.php');
    if ($page->has_content) {
      echo '<div class="page-content">';
      $page->render();
      echo '</div>', "\n";
    }
    include('footer.php');
  }
?>
