<?php
  # $Id$
  # (c)2005,2006 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * edit Wiki page
  * @param mw_texts texts array
  */

  $page =& get_current_page();
  $req =& get_request();
  
  $title = $mw_texts[MWT_EDITING] . " " . $page->name;
  include('header.php');
  if ($req->preview && $page->has_content) {
    echo '<div class="page-content">';
    $page->render();
    echo '</div>', "\n";
  }
  echo '<div class="page-edit">', "\n";
  echo '<form method="post" action="', htmlspecialchars($page->url_for_action(MW_ACTION_UPDATE), ENT_QUOTES), '">', "\n";
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
  echo '<textarea id="editarea" name="', MW_REQVAR_CONTENT, '" rows="20" cols="120">', "\n";
  if ($page->has_content) {
    echo htmlspecialchars($page->raw_content, ENT_NOQUOTES);
  }
  echo '</textarea><br/>', "\n";
  echo $mw_texts[MWT_EDIT_MESSAGE], ': ';
  echo '<input name="', MW_REQVAR_MESSAGE, '" type="text" size="90" value="';
  if ($req->message) {
    echo htmlspecialchars($req->message, ENT_QUOTES);
  }
  echo '"/>', "\n";
  echo '<input type="submit" name="', MW_REQVAR_PREVIEW, '" value="', $mw_texts[MWT_EDIT_PREVIEW], '"/>', "\n";
  echo '<input type="submit" value="', $mw_texts[MWT_EDIT_SUBMIT], '"/>', "\n";
  echo '</form>', "\n";
  echo '</div>', "\n";
  include('footer.php');
?>
