<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # edit upload file (allows to upload new version)
  # mw_texts: texts array
  # page: current MW_Page

  $title = $mw_texts[MWT_EDITING] . " " . $page->name;
  include('header.php');
  echo '<div class="upload-edit">', "\n";
  echo '<form enctype="multipart/form-data" action="', htmlspecialchars($page->url_for_action(MW_ACTION_UPLOAD), ENT_QUOTES), '" method="post">'. "\n";
  echo $mw_texts[MWT_SOURCE_FILENAME], '<input type="file" size="40" name="', MW_REQVAR_SOURCEFILE, '"/><br/>', "\n";
  echo $mw_texts[MWT_UPLOAD_MESSAGE], "<br/>\n";
  echo '<textarea name="', MW_REQVAR_MESSAGE, '" rows="10" cols="60"></textarea><br/>', "\n";
  echo '<input type="submit" value="', htmlspecialchars($mw_texts[MWT_UPLOAD_BUTTON], ENT_QUOTES),'"/><br/>', "\n";
  echo '</form>', "\n";
  echo '</div>', "\n";
  include('footer.php');
?>
