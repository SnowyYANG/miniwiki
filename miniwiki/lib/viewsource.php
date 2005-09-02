<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # view Wiki page source
  # mw_texts: texts array
  # page: current MW_Page
  
  $title = $mw_texts[MWT_VIEWING] . " " . $page->name;
  include('header.php');
  if ($page->has_content) {
    echo '<div class="page-source"><textarea readonly="readonly" rows="20" cols="120">', "\n";
    echo htmlspecialchars($page->raw_content, ENT_NOQUOTES);
    echo '</textarea></div>', "\n";
  }
  include('footer.php');
?>
