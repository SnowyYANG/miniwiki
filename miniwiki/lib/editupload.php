<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * edit upload file (allows to upload new version)
  */

  $page =& get_current_page();
  
  render_ui(MW_LAYOUT_HEADER, _t("Editing %0%", $page->name));
  echo '<div class="upload-edit">', "\n";
  $action = get_action(MW_ACTION_EDIT);
  $link = $action->link();
  echo '<form enctype="multipart/form-data" action="', $link->to_url(true), '" method="post">'. "\n";
  echo _t("Source filename"), ': <input type="file" size="40" name="', $link->get_sourcefile_param_name(), '"/><br/>', "\n";
  echo _t("Upload message"), ": <br/>\n";
  echo '<textarea name="', $link->get_message_param_name(), '" rows="10" cols="60"></textarea><br/>', "\n";
  echo '<input type="submit" value="', htmlspecialchars(_t("Upload"), ENT_QUOTES),'"/><br/>', "\n";
  echo '</form>', "\n";
  if ($page->is_text_content()) {
    echo '<form method="post" action="', $link->to_url(true), '">', "\n";
    echo '<textarea id="editarea" name="', $link->get_content_param_name(), '" rows="20" cols="120">', "\n";
    $page->load_with_raw_content();
    if ($page->has_content) {
      echo htmlspecialchars($page->raw_content, ENT_NOQUOTES);
    }
    echo '</textarea><br/>', "\n";
    echo _t('Edit message'), ': ';
    echo '<input name="', $link->get_message_param_name(), '" type="text" size="90" value="';
    if ($req->get_message() !== null) {
      echo htmlspecialchars($req->get_message(), ENT_QUOTES);
    }
    echo '"/>', "\n";
    echo '<br/>', "\n";
    echo '<input type="submit" value="', _t('Update'), '"/>', "\n";
    echo '</form>', "\n";
  }
  echo '</div>', "\n";
  render_ui(MW_LAYOUT_FOOTER);
?>
