<?echo '<?xml version="1.0" encoding="'.$mw_encoding.'"?>',"\n" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # page header
  # mw_encoding: output encoding
  # page: current MW_Page
  # title: override page name

  if (!isset ($title)) {
    $title = $page->title;
  }
  $title = htmlspecialchars($title, ENT_NOQUOTES);
?>
<head>
  <?echo '<meta http-equiv="Content-Type" content="text/html; charset='.$mw_encoding.'"/>' ?>
  <?echo '<meta name="generator" content="', MW_NAME, '/', MW_VERSION, '"/>' ?>
  <title><?echo $title ?></title>
<?php
  $css_page = new_page($db, MW_PAGE_NAME_PREFIX_DATA.MW_DEFAULT_STYLESHEET_NAME, MW_REVISION_HEAD);
  if ($css_page->exists()) {
?>
  <link rel="stylesheet" type="text/css" href="<?echo $css_page->url_for_action(MW_ACTION_VIEW) ?>"/>
<?php
  } else {
?>
  <link rel="stylesheet" type="text/css" href="data/<?echo MW_DEFAULT_STYLESHEET_NAME ?>"/>
<?php
  }
?>
<?php
  $js_page = new_page($db, MW_PAGE_NAME_PREFIX_DATA.MW_DEFAULT_JAVASCRIPT_FUNCTIONS_NAME, MW_REVISION_HEAD);
  if ($js_page->exists()) {
?>
  <script type="text/javascript" src="<?echo $js_page->url_for_action(MW_ACTION_VIEW) ?>"></script>
<?php
  } else {
?>
  <script type="text/javascript" src="data/<?echo MW_DEFAULT_JAVASCRIPT_FUNCTIONS_NAME ?>"></script>
<?php
  }
?>
</head>
<body>
<h1><?echo $title ?></h1>
<?php
  $info_text = get_info_text();
  if (count($info_text) > 0) {
    echo '<div class="info-text">', htmlspecialchars(implode(' ', $info_text), ENT_NOQUOTES), '</div>',"\n";
  }
?>
