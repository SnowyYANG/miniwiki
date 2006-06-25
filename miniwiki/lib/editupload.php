<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * edit upload file (allows to upload new version)
  */

  $page =& get_current_page();
  
  $title = _("Editing", $page->name);
  include('header.php');
  echo '<div class="upload-edit">', "\n";
  echo '<form enctype="multipart/form-data" action="', htmlspecialchars($page->url_for_action(MW_ACTION_UPLOAD), ENT_QUOTES), '" method="post">'. "\n";
  echo _("Source filename"), ': <input type="file" size="40" name="', MW_REQVAR_SOURCEFILE, '"/><br/>', "\n";
  echo _("Upload message"), ": <br/>\n";
  echo '<textarea name="', MW_REQVAR_MESSAGE, '" rows="10" cols="60"></textarea><br/>', "\n";
  echo '<input type="submit" value="', htmlspecialchars(_("Upload"), ENT_QUOTES),'"/><br/>', "\n";
  echo '</form>', "\n";
  echo '</div>', "\n";
  include('footer.php');
?>
