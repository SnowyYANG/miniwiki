<?php
  # $Id$
  # (c)2005 Stepan Roh <src@srnet.cz>
  # Free to copy, free to modify, NO WARRANTY

  # functions accessible from Wiki pages by using {{&...}} syntax

  register_wiki_function('echo', 'wiki_fn_echo');
  register_wiki_function('include', 'wiki_fn_include');
  register_wiki_function('user_info', 'wiki_fn_user_info');

  # joins arguments and returns them
  function wiki_fn_echo($args, $renderer_state) {
    return join('', $args);
  }

  # returns raw content of page specified by first argument
  function wiki_fn_include($args, $renderer_state) {
    $inc_page_name = $args[0];
    $inc_page = new_page($renderer_state->renderer->db, $inc_page_name, MW_REVISION_HEAD);
    if ($inc_page->load()) {
      return str_replace("\r", '', $inc_page->raw_content);
    }
    return '[['.$inc_page_name .']]';
  }

  # returns user information
  function wiki_fn_user_info() {
    global $auth;
    if ($auth->is_logged) {
      $f = get_user_info_file($auth->user);
      if (!file_exists($f)) {
        return null;
      }
      $user_info = file_get_contents($f);
      if ($user_info === false) {
        return null;
      }
      return $user_info;
    }
    return null;
  }

?>
