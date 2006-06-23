<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  /** @file
  * page header
  * @param mw_encoding output encoding
  * @param page current MW_Page
  * @param title override page name
  * @param db curent MW_Database
  * @param renderer current MW_Renderer
  */

  if (!isset ($title)) {
    $title = $page->title;
  }

  $layout_header = new_page(MW_PAGE_NAME_LAYOUT_HEADER, MW_REVISION_HEAD);
  if ($layout_header->load()) {
    $vars = new_global_wiki_variables();
    $vars->set('title', $title);
    $info_text = get_info_text();
    if (count($info_text) > 0) {
      $vars->set('info_text', implode(' ', $info_text));
    }
    $renderer->render($page, $layout_header->raw_content, $vars);
  } else {

  $title = htmlspecialchars($title, ENT_NOQUOTES);

?>
<?echo '<?xml version="1.0" encoding="'.$mw_encoding.'"?>',"\n" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
  <?echo '<meta http-equiv="Content-Type" content="text/html; charset='.$mw_encoding.'"/>' ?>
  <?echo '<meta name="generator" content="', MW_NAME, '/', MW_VERSION, '"/>' ?>
  <title><?echo $title ?></title>
<?php
  $css_page = new_page(MW_PAGE_NAME_PREFIX_DATA.MW_DEFAULT_STYLESHEET_NAME, MW_REVISION_HEAD);
  if ($css_page->exists()) {
?>
  <link rel="stylesheet" type="text/css" href="<?echo $css_page->url_for_action(MW_ACTION_VIEW) ?>"/>
<?php
  } else {
?>
  <link rel="stylesheet" type="text/css" href="<?echo $_SERVER['SCRIPT_NAME'].'/../data/'.MW_DEFAULT_STYLESHEET_NAME ?>"/>
<?php
  }
?>
<?php
  $js_page = new_page(MW_PAGE_NAME_PREFIX_DATA.MW_DEFAULT_JAVASCRIPT_FUNCTIONS_NAME, MW_REVISION_HEAD);
  if ($js_page->exists()) {
?>
  <script type="text/javascript" src="<?echo $js_page->url_for_action(MW_ACTION_VIEW) ?>"></script>
<?php
  } else {
?>
  <script type="text/javascript" src="<?echo $_SERVER['SCRIPT_NAME'].'/../data/'.MW_DEFAULT_JAVASCRIPT_FUNCTIONS_NAME ?>"></script>
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
  
  }
?>
