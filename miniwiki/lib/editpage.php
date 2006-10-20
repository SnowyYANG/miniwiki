<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * edit Wiki page
  */

  $page =& get_current_page();
  $req =& get_request("MW_EditRequest");
  
  render_ui(MW_LAYOUT_HEADER, _t("Editing %0%", $page->name));
  if ($req->is_preview() && $page->has_content) {
    echo '<div class="page-content">';
    $page->render();
    echo '</div>', "\n";
  }
  echo '<div class="page-edit">', "\n";
  $action = get_action(MW_ACTION_EDIT);
  $link = $action->link();
  echo '<form method="post" action="', $link->to_url(true), '">', "\n";
  /** generate edit button
  * label: button label
  * text: text inserted on current cursor position into textarea with id "editarea" if button is pressed
  *       % indicates where cursor should be positioned
  * accesskey: access key of this button (e.g. Alt-KEY in Mozilla Firefox) - defaults to nothing
  */
  function generate_button($label, $text, $accesskey = '') {
    echo '<button type="button"';
    if ($accesskey != '') {
      echo ' accesskey="', $accesskey, '"';
    }
    echo ' onclick="add_to_textarea(\'editarea\', \'', htmlspecialchars($text, ENT_QUOTES), '\')">', $label, '</button>', "\n";
  }
  # edit buttons
  generate_button('<b>B</b>', "\\'\\'\\'%\\'\\'\\'", 'b');
  generate_button('<i>I</i>', "\\'\\'%\\'\\'", 'i');
  generate_button('H1', "\\n\\n= % =\\n\\n", 'h');
  generate_button('H2', "\\n\\n== % ==\\n\\n");
  generate_button('H3', "\\n\\n=== % ===\\n\\n");
  generate_button('H4', "\\n\\n==== % ====\\n\\n");
  generate_button('H5', "\\n\\n===== % =====\\n\\n");
  generate_button('H6', "\\n\\n====== % ======\\n\\n");
  generate_button('<span class="link-like">Wiki</span>', "[[%]]", 'w');
  generate_button('<span class="link-like">http:</span>', "[http:%]", 'e');
  generate_button('<span class="list-like">abc</span>', "*", 'l');
  generate_button('<span class="pre-like">PRE</span>', "\\n\\n<pre>\\n%\\n</pre>\\n\\n", 'p');
  echo '<br/>', "\n";
  echo '<textarea id="editarea" name="', $link->get_content_param_name(), '" rows="20" cols="120">', "\n";
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
  echo '<input type="submit" name="', $link->get_preview_param_name(), '" value="', _t("Preview"), '"/>', "\n";
  echo '<input type="submit" value="', _t('Update Page'), '"/>', "\n";
  echo '</form>', "\n";
  echo '</div>', "\n";
  render_ui(MW_LAYOUT_FOOTER);
?>
