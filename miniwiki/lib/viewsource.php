<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * view Wiki page source
  */

  $page =& get_current_page();
  
  render_ui(MW_LAYOUT_HEADER, _("Viewing %0%", $page->name));
  if ($page->has_content) {
    echo '<div class="page-source"><textarea readonly="readonly" rows="20" cols="120">', "\n";
    echo htmlspecialchars($page->raw_content, ENT_NOQUOTES);
    echo '</textarea></div>', "\n";
  }
  render_ui(MW_LAYOUT_FOOTER);
?>
