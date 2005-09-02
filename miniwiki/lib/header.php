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
  <link rel="stylesheet" type="text/css" href="data/default.css"/>
  <script type="text/javascript" src="data/functions.js"></script>
</head>
<body>
<h1><?echo $title ?></h1>
<?php
  $info_text = get_info_text();
  if (count($info_text) > 0) {
    echo '<div class="info-text">', htmlspecialchars(implode(' ', $info_text), ENT_NOQUOTES), '</div>',"\n";
  }
?>
