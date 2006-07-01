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
  $action = get_action(MW_ACTION_UPLOAD);
  $link = $action->link();
  echo '<form enctype="multipart/form-data" action="', $link->to_url(true), '" method="post">'. "\n";
  echo _("Source filename"), ': <input type="file" size="40" name="', $link->get_sourcefile_param_name(), '"/><br/>', "\n";
  echo _("Upload message"), ": <br/>\n";
  echo '<textarea name="', $link->get_message_param_name(), '" rows="10" cols="60"></textarea><br/>', "\n";
  echo '<input type="submit" value="', htmlspecialchars(_("Upload"), ENT_QUOTES),'"/><br/>', "\n";
  echo '</form>', "\n";
  echo '</div>', "\n";
  include('footer.php');
?>
